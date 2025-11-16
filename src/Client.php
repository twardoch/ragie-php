<?php

// this_file: src/Client.php

declare(strict_types=1);

namespace Ragie;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use JsonException;
use Psr\Log\LoggerInterface;
use Ragie\Api\Api\DocumentsApi;
use Ragie\Api\Api\RetrievalsApi;
use Ragie\Api\Api\ResponsesApi;
use Ragie\Api\Configuration;
use Ragie\Cache\QueryCacheInterface;
use Ragie\Exception\InvalidQueryException;
use Ragie\Logging\StructuredLogger;
use Ragie\Metrics\CostTracker;
use Ragie\Metrics\MetricsCollector;
use Ragie\Validation\InputSanitizer;

/**
 * Ragie PHP Client
 *
 * High-level convenience client for common RAG (Retrieval-Augmented Generation) operations.
 * Wraps the auto-generated Ragie API client and provides simplified methods for retrieval tasks.
 *
 * Note: This class is not designed to be extended. Use composition instead of inheritance.
 *
 * @example
 * ```php
 * use Ragie\Client;
 *
 * // Initialize client
 * $ragie = new Client('your-api-key');
 *
 * // Simple retrieval
 * $result = $ragie->retrieve('What is RAG?');
 * foreach ($result->getChunks() as $chunk) {
 *     echo $chunk->getText() . "\n";
 * }
 *
 * // Fluent configuration with defaults
 * $ragie = (new Client('your-api-key'))
 *     ->withDefaultTopK(20)
 *     ->withDefaultRerank();
 *
 * // Enable debug mode
 * $ragie = (new Client('your-api-key'))
 *     ->enableDebug();
 *
 * // Batch retrieval
 * $results = $ragie->retrieveBatch([
 *     'What is machine learning?',
 *     'Explain neural networks'
 * ]);
 *
 * // Access underlying API for document management
 * $documents = $ragie->getDocumentsApi()->listDocuments();
 * ```
 *
 * @package Ragie
 */
class Client
{
    private const MAX_QUERY_LENGTH = 10000;
    private const DEFAULT_CACHE_TTL = 300;

    private Configuration $configuration;
    private ClientInterface $httpClient;

    private ?RetrievalsApi $retrievalsApi = null;
    private ?ResponsesApi $responsesApi = null;
    private ?DocumentsApi $documentsApi = null;

    // Fluent configuration defaults
    private ?int $defaultTopK = null;
    private ?bool $defaultRerank = null;
    private ?\Ragie\Retry\RetryPolicy $retryPolicy = null;
    private ?QueryCacheInterface $cache = null;
    private int $cacheTtlSeconds = self::DEFAULT_CACHE_TTL;
    private bool $debug = false;
    private ?MetricsCollector $metricsCollector = null;
    private ?CostTracker $costTracker = null;
    private ?StructuredLogger $structuredLogger = null;

    /**
     * Create a new Ragie client instance
     *
     * @param string $apiKey The Ragie API key for authentication
     * @param string|null $baseUrl Optional base URL override (defaults to https://api.ragie.ai)
     * @param ClientInterface|null $httpClient Optional custom HTTP client (defaults to Guzzle)
     *
     * @throws InvalidQueryException If API key is empty
     */
    public function __construct(
        string $apiKey,
        ?string $baseUrl = null,
        ?ClientInterface $httpClient = null
    ) {
        $trimmed = trim($apiKey);
        if ($trimmed === '') {
            if ($apiKey === '') {
                throw InvalidQueryException::empty();
            }
            throw InvalidQueryException::whitespace();
        }

        $this->configuration = new Configuration();
        $this->configuration->setAccessToken($apiKey);

        if ($baseUrl !== null) {
            $this->configuration->setHost($baseUrl);
        }

        $this->httpClient = $httpClient ?? new GuzzleClient([
            'timeout' => 30,
            'connect_timeout' => 5,
            'headers' => [
                'Connection' => 'keep-alive',
                'Keep-Alive' => '300',
            ],
            'curl' => [
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 120,
            ],
        ]);
    }

    /**
     * Retrieve relevant document chunks for a query
     *
     * This is the primary method for RAG retrieval operations. It finds and returns
     * the most relevant document chunks based on the provided query.
     *
     * @param string $query The search query
     * @param RetrievalOptions|null $options Optional retrieval parameters (filters, top_k, etc.)
     *
     *
     * @throws \Ragie\Api\ApiException If the API request fails
     * @throws InvalidQueryException If query is empty or whitespace
     * @return RetrievalResult The retrieval results with convenient accessors
     *
     * @example
     * ```php
     * // Basic retrieval
     * $result = $ragie->retrieve('What is machine learning?');
     *
     * // With options
     * $options = RetrievalOptions::create()
     *     ->withTopK(20)
     *     ->withRerank()
     *     ->withFilter(['category' => 'docs']);
     * $result = $ragie->retrieve('query', $options);
     * ```
     */
    public function retrieve(string $query, ?RetrievalOptions $options = null): RetrievalResult
    {
        $query = InputSanitizer::sanitizeAndValidate($query, self::MAX_QUERY_LENGTH);

        // Use default options if none provided, applying fluent defaults
        $options = $options ?? $this->createDefaultOptions();

        // Debug: Log query start
        $startTime = microtime(true);
        $debugStartTime = $this->debug ? $startTime : null;
        if ($this->debug && $debugStartTime !== null) {
            error_log("[Ragie Debug] Query: {$query}");
        }

        // Debug: Log options
        if ($this->debug) {
            error_log('[Ragie Debug] Options: ' . json_encode($options->toRetrieveParams($query)));
        }

        $this->structuredLogger?->logRetrievalStart('ragie', $query);

        // Convert to API params
        $retrieveParams = $options->toRetrieveParams($query);

        $cacheKey = null;
        $chunkCount = 0;
        $success = false;
        $costIncurred = false;
        $result = null;
        $cacheHit = false;
        $durationMs = 0;

        try {
            if ($this->cache !== null) {
                $cacheKey = $this->buildCacheKey($retrieveParams);
                if ($this->cache->has($cacheKey)) {
                    $cached = $this->cache->get($cacheKey);
                    if ($cached !== null) {
                        $result = $cached;
                        $cacheHit = true;
                    }
                }
            }

            // Execute with retry policy if configured
            $executeRetrieval = function () use ($retrieveParams): \Ragie\Api\Model\Retrieval {
                $retrieval = $this->getRetrievalsApi()->retrieve($retrieveParams);

                // In practice, errors throw ApiException, so this should always be Retrieval
                if (!$retrieval instanceof \Ragie\Api\Model\Retrieval) {
                    throw new \RuntimeException('Unexpected response type from retrieve API');
                }

                return $retrieval;
            };

            if ($result === null) {
                $retrieval = $this->retryPolicy !== null
                    ? $this->retryPolicy->execute($executeRetrieval)
                    : $executeRetrieval();

                // Wrap in convenience result object
                $result = new RetrievalResult($retrieval);
                $costIncurred = true;

                if ($cacheKey !== null && $this->cache !== null) {
                    $this->cache->set($cacheKey, $result, $this->cacheTtlSeconds);
                }
            }

            $chunkCount = count($result->getChunks());
            $success = true;

            if ($this->debug && $debugStartTime !== null) {
                $durationMs = $this->elapsedMs($debugStartTime);
                error_log(sprintf('[Ragie Debug] Duration: %dms', $durationMs));
                error_log('[Ragie Debug] Chunks returned: ' . $chunkCount);
            }

            $durationMs = $durationMs > 0 ? $durationMs : $this->elapsedMs($startTime);
            $this->structuredLogger?->logRetrievalSuccess('ragie', $query, $durationMs, $chunkCount, $cacheHit);

            return $result;
        } catch (\Throwable $e) {
            $durationMs = $durationMs > 0 ? $durationMs : $this->elapsedMs($startTime);
            $this->structuredLogger?->logRetrievalFailure('ragie', $query, $e, $durationMs);
            throw $e;
        } finally {
            $finalDuration = $durationMs > 0 ? $durationMs : $this->elapsedMs($startTime);

            if ($this->metricsCollector !== null) {
                $this->metricsCollector->recordRetrieval('ragie', $finalDuration, $chunkCount, $success);
            }

            if ($costIncurred && $this->costTracker !== null) {
                $this->costTracker->recordRagieQuery();
            }
        }
    }

    /**
     * Retrieve relevant document chunks asynchronously.
     *
     * Provides a non-blocking variant of {@see retrieve()} that uses Guzzle promises.
     *
     * @param string $query             The search query
     * @param RetrievalOptions|null $options Optional retrieval parameters
     *
     * @throws InvalidQueryException If the query is empty or whitespace
     *
     * @return PromiseInterface Promise resolving to RetrievalResult
     */
    public function retrieveAsync(string $query, ?RetrievalOptions $options = null): PromiseInterface
    {
        $query = InputSanitizer::sanitizeAndValidate($query, self::MAX_QUERY_LENGTH);

        $options = $options ?? $this->createDefaultOptions();

        $startTime = microtime(true);
        $debugStartTime = $this->debug ? $startTime : null;

        if ($this->debug && $debugStartTime !== null) {
            error_log("[Ragie Debug] Query: {$query}");
        }

        if ($this->debug) {
            error_log('[Ragie Debug] Options: ' . json_encode($options->toRetrieveParams($query)));
        }

        $this->structuredLogger?->logRetrievalStart('ragie', $query);

        $retrieveParams = $options->toRetrieveParams($query);

        $cacheKey = null;
        if ($this->cache !== null) {
            $cacheKey = $this->buildCacheKey($retrieveParams);
            if ($this->cache->has($cacheKey)) {
                $cached = $this->cache->get($cacheKey);
                if ($cached !== null) {
                    $chunkCount = count($cached->getChunks());
                    $durationMs = $this->elapsedMs($startTime);

                    if ($this->debug && $debugStartTime !== null) {
                        error_log(sprintf('[Ragie Debug] Duration: %dms', $durationMs));
                        error_log('[Ragie Debug] Chunks returned: ' . $chunkCount);
                    }

                    $this->structuredLogger?->logRetrievalSuccess('ragie', $query, $durationMs, $chunkCount, true);

                    if ($this->metricsCollector !== null) {
                        $this->metricsCollector->recordRetrieval('ragie', $durationMs, $chunkCount, true);
                    }

                    return Create::promiseFor($cached);
                }
            }
        }

        $promise = $this->getRetrievalsApi()->retrieveAsync($retrieveParams);

        return $promise->then(
            function (\Ragie\Api\Model\Retrieval $retrieval) use ($cacheKey, $query, $startTime, $debugStartTime): RetrievalResult {
                $result = new RetrievalResult($retrieval);

                if ($cacheKey !== null && $this->cache !== null) {
                    $this->cache->set($cacheKey, $result, $this->cacheTtlSeconds);
                }

                $chunkCount = count($result->getChunks());
                $durationMs = $this->elapsedMs($startTime);

                if ($this->debug && $debugStartTime !== null) {
                    error_log(sprintf('[Ragie Debug] Duration: %dms', $durationMs));
                    error_log('[Ragie Debug] Chunks returned: ' . $chunkCount);
                }

                $this->structuredLogger?->logRetrievalSuccess('ragie', $query, $durationMs, $chunkCount, false);

                if ($this->metricsCollector !== null) {
                    $this->metricsCollector->recordRetrieval('ragie', $durationMs, $chunkCount, true);
                }

                if ($this->costTracker !== null) {
                    $this->costTracker->recordRagieQuery();
                }

                return $result;
            },
            function ($reason) use ($query, $startTime) {
                $durationMs = $this->elapsedMs($startTime);
                $throwable = $reason instanceof \Throwable
                    ? $reason
                    : new \RuntimeException('Asynchronous retrieval failed');

                $this->structuredLogger?->logRetrievalFailure('ragie', $query, $throwable, $durationMs);

                if ($this->metricsCollector !== null) {
                    $this->metricsCollector->recordRetrieval('ragie', $durationMs, 0, false);
                }

                if ($this->costTracker !== null) {
                    $this->costTracker->recordRagieQuery();
                }

                throw $throwable;
            }
        );
    }

    /**
     * Get the underlying Retrievals API instance
     *
     * Provides direct access to the generated RetrievalsApi for advanced use cases.
     *
     * @return RetrievalsApi
     */
    public function getRetrievalsApi(): RetrievalsApi
    {
        if ($this->retrievalsApi === null) {
            $this->retrievalsApi = new RetrievalsApi(
                $this->httpClient,
                $this->configuration
            );
        }

        return $this->retrievalsApi;
    }

    /**
     * Get the underlying Responses API instance
     *
     * Provides direct access to the generated ResponsesApi for AI response generation.
     *
     * @return ResponsesApi
     */
    public function getResponsesApi(): ResponsesApi
    {
        if ($this->responsesApi === null) {
            $this->responsesApi = new ResponsesApi(
                $this->httpClient,
                $this->configuration
            );
        }

        return $this->responsesApi;
    }

    /**
     * Get the underlying Documents API instance
     *
     * Provides direct access to the generated DocumentsApi for document management operations.
     *
     * @return DocumentsApi
     */
    public function getDocumentsApi(): DocumentsApi
    {
        if ($this->documentsApi === null) {
            $this->documentsApi = new DocumentsApi(
                $this->httpClient,
                $this->configuration
            );
        }

        return $this->documentsApi;
    }

    /**
     * Get the API configuration instance
     *
     * Provides access to the underlying configuration for advanced customization.
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * Set default top_k value for all subsequent retrievals
     *
     * Configures a default number of results to return for all retrieve() calls
     * that don't explicitly specify a top_k value.
     *
     * @param int $topK Default number of chunks to retrieve (must be positive)
     *
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $ragie = (new Client($apiKey))
     *     ->withDefaultTopK(20)
     *     ->withDefaultRerank();
     *
     * // All subsequent calls use top_k=20 and rerank=true by default
     * $result = $ragie->retrieve('query');
     * ```
     */
    public function withDefaultTopK(int $topK): self
    {
        $this->defaultTopK = $topK;
        return $this;
    }

    /**
     * Set default rerank value for all subsequent retrievals
     *
     * Configures whether to rerank results by default for all retrieve() calls
     * that don't explicitly specify a rerank value.
     *
     * @param bool $rerank Whether to rerank results by default (default: true)
     *
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * $ragie = (new Client($apiKey))
     *     ->withDefaultTopK(15)
     *     ->withDefaultRerank(false);
     *
     * // Fast retrieval without reranking
     * $result = $ragie->retrieve('query');
     * ```
     */
    public function withDefaultRerank(bool $rerank = true): self
    {
        $this->defaultRerank = $rerank;
        return $this;
    }

    /**
     * Enable caching for identical retrieval queries.
     *
     * @param QueryCacheInterface $cache Cache implementation handling persistence
     * @param int $ttlSeconds Default cache lifetime in seconds
     *
     * @return self
     */
    public function withCache(QueryCacheInterface $cache, int $ttlSeconds = self::DEFAULT_CACHE_TTL): self
    {
        $this->cache = $cache;
        $this->cacheTtlSeconds = $ttlSeconds > 0 ? $ttlSeconds : self::DEFAULT_CACHE_TTL;

        return $this;
    }

    /**
     * Attach a metrics collector for retrieval telemetry.
     *
     * @return self
     */
    public function withMetricsCollector(MetricsCollector $metricsCollector): self
    {
        $this->metricsCollector = $metricsCollector;
        return $this;
    }

    /**
     * Attach a cost tracker to measure API spend.
     *
     * @return self
     */
    public function withCostTracker(CostTracker $costTracker): self
    {
        $this->costTracker = $costTracker;
        return $this;
    }

    /**
     * Attach a StructuredLogger instance for PSR-3 logging hooks.
     */
    public function withStructuredLogger(StructuredLogger $logger): self
    {
        $this->structuredLogger = $logger;
        return $this;
    }

    /**
     * Convenience helper for passing a raw PSR-3 logger.
     *
     * @psalm-suppress PossiblyUnusedReturnValue Fluent API consumed by downstream scripts
     */
    public function withLogger(LoggerInterface $logger): self
    {
        return $this->withStructuredLogger(new StructuredLogger($logger));
    }

    /**
     * Set retry policy for handling transient failures
     *
     * Configures exponential backoff retry logic for network errors,
     * server errors (5xx), and rate limits (429).
     *
     * @param \Ragie\Retry\RetryPolicy $retryPolicy Retry policy instance
     * @return self Returns $this for method chaining
     *
     * @example
     * ```php
     * use Ragie\Retry\RetryPolicy;
     *
     * $client = (new Client($apiKey))
     *     ->withRetryPolicy(new RetryPolicy(
     *         maxAttempts: 3,
     *         initialDelayMs: 100,
     *         multiplier: 2.0,
     *         maxDelayMs: 5000
     *     ));
     * ```
     */
    public function withRetryPolicy(\Ragie\Retry\RetryPolicy $retryPolicy): self
    {
        $this->retryPolicy = $retryPolicy;
        return $this;
    }

    /**
     * Enable debug mode with detailed logging
     *
     * When enabled, the client will log detailed information about retrievals
     * including queries, options, timing, and results to error_log().
     *
     * @return self Returns $this for method chaining
     * @psalm-suppress PossiblyUnusedMethod Public API consumed by downstream scripts outside this repo
     *
     * @example
     * ```php
     * $client = (new Client($apiKey))
     *     ->enableDebug();
     *
     * // Debug output will be written to error_log
     * $result = $client->retrieve('test query');
     * ```
     */
    public function enableDebug(): self
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Retrieve multiple queries in batch
     *
     * Processes multiple queries and returns an array of results.
     * Currently processes queries sequentially - may be optimized for parallel processing in the future.
     *
     * @param array<string> $queries Array of search queries
     * @param RetrievalOptions|null $options Optional retrieval parameters applied to all queries
     *
     * @throws \Ragie\Api\ApiException If any API request fails
     * @throws InvalidQueryException If any query is empty or whitespace
     *
     * @return array<RetrievalResult> Array of retrieval results in the same order as queries
     *
     * @example
     * ```php
     * $queries = [
     *     'What is machine learning?',
     *     'Explain neural networks',
     *     'How does gradient descent work?'
     * ];
     *
     * $results = $ragie->retrieveBatch($queries);
     *
     * foreach ($results as $i => $result) {
     *     echo "Query {$i}: " . $queries[$i] . "\n";
     *     echo "Top chunk: " . $result->getChunks()[0]->getText() . "\n\n";
     * }
     * ```
     */
    public function retrieveBatch(array $queries, ?RetrievalOptions $options = null): array
    {
        return array_map(
            fn ($query) => $this->retrieve($query, $options),
            $queries
        );
    }

    /**
     * Retrieve multiple queries concurrently using promises.
     *
     * @param array<string> $queries
     * @param RetrievalOptions|null $options Options applied to every query
     *
     * @return array<RetrievalResult>
     */
    public function retrieveConcurrent(array $queries, ?RetrievalOptions $options = null): array
    {
        $promises = array_map(
            fn ($query): PromiseInterface => $this->retrieveAsync((string) $query, $options),
            $queries
        );

        if ($promises === []) {
            return [];
        }

        return Utils::unwrap($promises);
    }

    /**
     * Create default options from fluent configuration
     *
     * Builds a RetrievalOptions object applying any defaults set via withDefaultTopK() or withDefaultRerank().
     *
     * @return RetrievalOptions Options object with defaults applied
     */
    private function createDefaultOptions(): RetrievalOptions
    {
        $options = RetrievalOptions::create();

        if ($this->defaultTopK !== null) {
            $options = $options->withTopK($this->defaultTopK);
        }

        if ($this->defaultRerank !== null) {
            $options = $options->withRerank($this->defaultRerank);
        }

        return $options;
    }

    private function elapsedMs(float $startTime): int
    {
        return (int) max(0, round((microtime(true) - $startTime) * 1000.0));
    }

    private function buildCacheKey(\Ragie\Api\Model\RetrieveParams $params): string
    {
        $host = (string) $this->configuration->getHost();

        try {
            $payload = (string) json_encode($params, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $payload = serialize($params);
        }

        return hash('sha256', $host . '|' . $payload);
    }

    /**
     * @api
     */
    public function getMetricsCollector(): ?MetricsCollector
    {
        return $this->metricsCollector;
    }

    /**
     * @api
     */
    public function getCostTracker(): ?CostTracker
    {
        return $this->costTracker;
    }

    /**
     * @api
     */
    public function getStructuredLogger(): ?StructuredLogger
    {
        return $this->structuredLogger;
    }
}
