<?php

// this_file: src/Client.php

declare(strict_types=1);

namespace Ragie;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Ragie\Api\Api\DocumentsApi;
use Ragie\Api\Api\RetrievalsApi;
use Ragie\Api\Api\ResponsesApi;
use Ragie\Api\Configuration;

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
 * // Access underlying API for document management
 * $documents = $ragie->getDocumentsApi()->listDocuments();
 * ```
 *
 * @package Ragie
 */
class Client
{
    private Configuration $configuration;
    private ClientInterface $httpClient;

    private ?RetrievalsApi $retrievalsApi = null;
    private ?ResponsesApi $responsesApi = null;
    private ?DocumentsApi $documentsApi = null;

    /**
     * Create a new Ragie client instance
     *
     * @param string $apiKey The Ragie API key for authentication
     * @param string|null $baseUrl Optional base URL override (defaults to https://api.ragie.ai)
     * @param ClientInterface|null $httpClient Optional custom HTTP client (defaults to Guzzle)
     *
     * @throws \InvalidArgumentException If API key is empty
     */
    public function __construct(
        string $apiKey,
        ?string $baseUrl = null,
        ?ClientInterface $httpClient = null
    ) {
        if (empty(trim($apiKey))) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        $this->configuration = new Configuration();
        $this->configuration->setAccessToken($apiKey);

        if ($baseUrl !== null) {
            $this->configuration->setHost($baseUrl);
        }

        $this->httpClient = $httpClient ?? new GuzzleClient();
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
     * @throws \InvalidArgumentException If query is empty
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
        if (empty(trim($query))) {
            throw new \InvalidArgumentException('Query cannot be empty');
        }

        // Use default options if none provided
        $options = $options ?? RetrievalOptions::create();

        // Convert to API params
        $retrieveParams = $options->toRetrieveParams($query);

        // Call underlying API
        $retrieval = $this->getRetrievalsApi()->retrieve($retrieveParams);

        // Wrap in convenience result object
        return new RetrievalResult($retrieval);
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
}
