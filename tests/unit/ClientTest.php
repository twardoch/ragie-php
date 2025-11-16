<?php

// this_file: tests/unit/ClientTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use Ragie\Api\Api\DocumentsApi;
use Ragie\Api\Api\RetrievalsApi;
use Ragie\Api\Api\ResponsesApi;
use Ragie\Api\Configuration;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Cache\InMemoryCache;
use Ragie\Cache\QueryCacheInterface;
use Ragie\Client;
use Ragie\Exception\InvalidQueryException;
use Ragie\Metrics\CostTracker;
use Ragie\Metrics\MetricsCollector;
use Ragie\RetrievalOptions;
use Ragie\RetrievalResult;

final class ClientTest extends TestCase
{
    public function testConstructorAcceptsApiKey(): void
    {
        $client = new Client('test-api-key');

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorThrowsExceptionForEmptyApiKey(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot be empty');

        new Client('');
    }

    public function testConstructorThrowsExceptionForWhitespaceApiKey(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot consist only of whitespace');

        new Client('   ');
    }

    public function testConstructorSetsBaseUrl(): void
    {
        $client = new Client('test-key', 'https://custom.api.url');
        $config = $client->getConfiguration();

        $this->assertSame('https://custom.api.url', $config->getHost());
    }

    public function testConstructorUsesDefaultBaseUrl(): void
    {
        $client = new Client('test-key');
        $config = $client->getConfiguration();

        $this->assertSame('https://api.ragie.ai', $config->getHost());
    }

    public function testGetConfigurationReturnsConfiguration(): void
    {
        $client = new Client('test-key');
        $config = $client->getConfiguration();

        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertSame('test-key', $config->getAccessToken());
    }

    public function testGetRetrievalsApiReturnsApiInstance(): void
    {
        $client = new Client('test-key');
        $api = $client->getRetrievalsApi();

        $this->assertInstanceOf(RetrievalsApi::class, $api);
    }

    public function testGetRetrievalsApiReturnsSameInstance(): void
    {
        $client = new Client('test-key');
        $api1 = $client->getRetrievalsApi();
        $api2 = $client->getRetrievalsApi();

        $this->assertSame($api1, $api2, 'Should return same instance (lazy initialization)');
    }

    public function testGetResponsesApiReturnsApiInstance(): void
    {
        $client = new Client('test-key');
        $api = $client->getResponsesApi();

        $this->assertInstanceOf(ResponsesApi::class, $api);
    }

    public function testGetResponsesApiReturnsSameInstance(): void
    {
        $client = new Client('test-key');
        $api1 = $client->getResponsesApi();
        $api2 = $client->getResponsesApi();

        $this->assertSame($api1, $api2);
    }

    public function testGetDocumentsApiReturnsApiInstance(): void
    {
        $client = new Client('test-key');
        $api = $client->getDocumentsApi();

        $this->assertInstanceOf(DocumentsApi::class, $api);
    }

    public function testGetDocumentsApiReturnsSameInstance(): void
    {
        $client = new Client('test-key');
        $api1 = $client->getDocumentsApi();
        $api2 = $client->getDocumentsApi();

        $this->assertSame($api1, $api2);
    }

    public function testRetrieveThrowsExceptionForEmptyQuery(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot be empty');

        $client = new Client('test-key');
        $client->retrieve('');
    }

    public function testRetrieveThrowsExceptionForWhitespaceQuery(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot consist only of whitespace');

        $client = new Client('test-key');
        $client->retrieve('   ');
    }

    public function testRetrieveReturnsRetrievalResult(): void
    {
        // Create mock retrieval response
        $chunk = new ScoredChunk();
        $chunk->setText('Test chunk');
        $chunk->setScore(0.95);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        // Create a mock RetrievalsApi
        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        // Create a test double of Client that returns our mock API
        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        // Test the retrieve method
        $result = $client->retrieve('test query');

        $this->assertInstanceOf(RetrievalResult::class, $result);
        $this->assertCount(1, $result);
        $topChunk = $result->getTopChunk();
        $this->assertNotNull($topChunk);
        $this->assertSame('Test chunk', $topChunk->getText());
    }

    public function testRetrieveUsesCacheForRepeatedQuery(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('Cached chunk');
        $chunk->setScore(0.9);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->withCache(new InMemoryCache());

        $first = $client->retrieve('cache me');
        $second = $client->retrieve('cache me');

        $this->assertSame($first, $second);
    }

    public function testRetrieveUsesDefaultOptions(): void
    {
        // Create empty retrieval
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                // Verify it's a valid RetrieveParams with the query
                return $params->getQuery() === 'test query';
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $result = $client->retrieve('test query');

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testRetrieveUsesDifferentCacheKeyWhenOptionsChange(): void
    {
        $firstChunk = new ScoredChunk();
        $firstChunk->setText('first');
        $firstChunk->setScore(0.8);

        $secondChunk = new ScoredChunk();
        $secondChunk->setText('second');
        $secondChunk->setScore(0.81);

        $firstRetrieval = new Retrieval();
        $firstRetrieval->setScoredChunks([$firstChunk]);

        $secondRetrieval = new Retrieval();
        $secondRetrieval->setScoredChunks([$secondChunk]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->exactly(2))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls($firstRetrieval, $secondRetrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $cache = new class () implements QueryCacheInterface {
            /** @var array<string, RetrievalResult> */
            private array $store = [];

            #[\Override]
            public function get(string $key): ?RetrievalResult
            {
                return $this->store[$key] ?? null;
            }

            #[\Override]
            public function set(string $key, RetrievalResult $result, int $ttl): void
            {
                $this->store[$key] = $result;
            }

            #[\Override]
            public function has(string $key): bool
            {
                return array_key_exists($key, $this->store);
            }

            #[\Override]
            public function clear(): void
            {
                $this->store = [];
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->withCache($cache);

        $firstOptions = RetrievalOptions::create()->withTopK(3);
        $secondOptions = RetrievalOptions::create()->withTopK(5);

        $first = $client->retrieve('query', $firstOptions);
        $second = $client->retrieve('query', $secondOptions);

        $this->assertNotSame($first, $second);
        $this->assertSame('first', $first->getTopChunk()?->getText());
        $this->assertSame('second', $second->getTopChunk()?->getText());
    }

    public function testWithCacheFallsBackToDefaultTtlWhenInvalidValueProvided(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $cache = new class () implements QueryCacheInterface {
            /** @var array<string, RetrievalResult> */
            private array $store = [];

            public ?int $lastTtl = null;

            #[\Override]
            public function get(string $key): ?RetrievalResult
            {
                return $this->store[$key] ?? null;
            }

            #[\Override]
            public function set(string $key, RetrievalResult $result, int $ttl): void
            {
                $this->store[$key] = $result;
                $this->lastTtl = $ttl;
            }

            #[\Override]
            public function has(string $key): bool
            {
                return array_key_exists($key, $this->store);
            }

            #[\Override]
            public function clear(): void
            {
                $this->store = [];
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        $client
            ->withCache($cache, -10)
            ->retrieve('force default ttl');

        $reflection = new \ReflectionClass(Client::class);
        $expectedTtl = $reflection->getConstant('DEFAULT_CACHE_TTL');

        $this->assertSame($expectedTtl, $cache->lastTtl);
    }

    public function testRetrieveUsesProvidedOptions(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                // Verify options were applied
                return $params->getQuery() === 'test query'
                    && $params->getTopK() === 20
                    && $params->getRerank() === true;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        $options = RetrievalOptions::create()
            ->withTopK(20)
            ->withRerank();

        $result = $client->retrieve('test query', $options);

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testConstructorAcceptsCustomHttpClient(): void
    {
        $customClient = new GuzzleClient();
        $client = new Client('test-key', null, $customClient);

        $this->assertInstanceOf(Client::class, $client);
    }

    // Fluent configuration tests

    public function testWithDefaultTopKReturnsClient(): void
    {
        $client = new Client('test-key');
        $result = $client->withDefaultTopK(20);

        $this->assertSame($client, $result, 'Should return self for method chaining');
    }

    public function testWithDefaultRerankReturnsClient(): void
    {
        $client = new Client('test-key');
        $result = $client->withDefaultRerank();

        $this->assertSame($client, $result, 'Should return self for method chaining');
    }

    public function testWithDefaultRerankWithFalseReturnsClient(): void
    {
        $client = new Client('test-key');
        $result = $client->withDefaultRerank(false);

        $this->assertSame($client, $result, 'Should return self for method chaining');
    }

    public function testFluentConfigurationChaining(): void
    {
        $client = new Client('test-key');
        $result = $client
            ->withDefaultTopK(15)
            ->withDefaultRerank(false);

        $this->assertSame($client, $result, 'Should support method chaining');
    }

    public function testRetrieveAppliesDefaultTopK(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                return $params->getQuery() === 'test query'
                    && $params->getTopK() === 25;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->withDefaultTopK(25);
        $result = $client->retrieve('test query');

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testRetrieveAppliesDefaultRerank(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                return $params->getQuery() === 'test query'
                    && $params->getRerank() === true;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->withDefaultRerank(true);
        $result = $client->retrieve('test query');

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testRetrieveAppliesBothDefaults(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                return $params->getQuery() === 'test query'
                    && $params->getTopK() === 30
                    && $params->getRerank() === false;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client
            ->withDefaultTopK(30)
            ->withDefaultRerank(false);
        $result = $client->retrieve('test query');

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testRetrieveExplicitOptionsOverrideDefaults(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                // Explicit options should override defaults
                return $params->getQuery() === 'test query'
                    && $params->getTopK() === 50  // Explicit, not default 30
                    && $params->getRerank() === true; // Explicit, not default false
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client
            ->withDefaultTopK(30)
            ->withDefaultRerank(false);

        // Explicit options should override defaults
        $options = RetrievalOptions::create()
            ->withTopK(50)
            ->withRerank(true);

        $result = $client->retrieve('test query', $options);

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    // Batch retrieval tests

    public function testRetrieveBatchWithEmptyArray(): void
    {
        $client = new Client('test-key');
        $results = $client->retrieveBatch([]);

        $this->assertCount(0, $results);
    }

    public function testRetrieveBatchWithSingleQuery(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $results = $client->retrieveBatch(['test query']);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(RetrievalResult::class, $results[0]);
    }

    public function testRetrieveBatchWithMultipleQueries(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->exactly(3))
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $results = $client->retrieveBatch([
            'What is machine learning?',
            'Explain neural networks',
            'How does gradient descent work?'
        ]);

        $this->assertCount(3, $results);
        $this->assertInstanceOf(RetrievalResult::class, $results[0]);
        $this->assertInstanceOf(RetrievalResult::class, $results[1]);
        $this->assertInstanceOf(RetrievalResult::class, $results[2]);
    }

    public function testRetrieveBatchAppliesOptionsToAllQueries(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->exactly(2))
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                // All queries should have the same options applied
                return $params->getTopK() === 15
                    && $params->getRerank() === true;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        $options = RetrievalOptions::create()
            ->withTopK(15)
            ->withRerank(true);

        $results = $client->retrieveBatch([
            'Query 1',
            'Query 2'
        ], $options);

        $this->assertCount(2, $results);
    }

    public function testRetrieveBatchAppliesDefaultOptions(): void
    {
        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->exactly(2))
            ->method('retrieve')
            ->with($this->callback(function ($params) {
                // Defaults should be applied to batch queries
                return $params->getTopK() === 40
                    && $params->getRerank() === true;
            }))
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client
            ->withDefaultTopK(40)
            ->withDefaultRerank(true);

        $results = $client->retrieveBatch([
            'Query 1',
            'Query 2'
        ]);

        $this->assertCount(2, $results);
    }

    public function testRetrieveBatchThrowsOnInvalidQuery(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot be empty');

        $retrieval = new Retrieval();

        $mockApi = $this->createMock(RetrievalsApi::class);
        // First query succeeds, second query should fail validation
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->retrieveBatch([
            'Valid query',
            '',  // Empty query should throw during validation
        ]);
    }

    public function testRetrieveBatchPreservesQueryOrder(): void
    {
        // Create distinct retrievals for each query to verify order
        $retrieval1 = new Retrieval();
        $chunk1 = new ScoredChunk();
        $chunk1->setText('Result 1');
        $retrieval1->setScoredChunks([$chunk1]);

        $retrieval2 = new Retrieval();
        $chunk2 = new ScoredChunk();
        $chunk2->setText('Result 2');
        $retrieval2->setScoredChunks([$chunk2]);

        $retrieval3 = new Retrieval();
        $chunk3 = new ScoredChunk();
        $chunk3->setText('Result 3');
        $retrieval3->setScoredChunks([$chunk3]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->exactly(3))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls($retrieval1, $retrieval2, $retrieval3);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $results = $client->retrieveBatch([
            'Query 1',
            'Query 2',
            'Query 3'
        ]);

        $this->assertCount(3, $results);
        $this->assertSame('Result 1', $results[0]->getTopChunk()?->getText());
        $this->assertSame('Result 2', $results[1]->getTopChunk()?->getText());
        $this->assertSame('Result 3', $results[2]->getTopChunk()?->getText());
    }

    public function testRetrieveEmitsMetricsAndCostOnSuccess(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('telemetry chunk');
        $chunk->setScore(0.8);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $metrics = new MetricsCollector();
        $costTracker = new CostTracker();

        $client->setMockRetrievalsApi($mockApi);
        $client->withMetricsCollector($metrics)->withCostTracker($costTracker);

        $client->retrieve('telemetry?');

        $events = array_values($metrics->getEvents());
        $this->assertNotEmpty($events);
        $this->assertSame('ragie', $events[0]['source']);
        $this->assertTrue($events[0]['success']);
        $this->assertSame(1, $events[0]['chunk_count']);
        $this->assertSame(1, $costTracker->getRagieQueries());
        $this->assertGreaterThan(0.0, $costTracker->getTotalCost());
    }

    public function testRetrieveEmitsFailureMetricWhenExceptionThrown(): void
    {
        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willThrowException(new \RuntimeException('boom'));

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $metrics = new MetricsCollector();
        $costTracker = new CostTracker();

        $client->setMockRetrievalsApi($mockApi);
        $client->withMetricsCollector($metrics)->withCostTracker($costTracker);

        try {
            $client->retrieve('should fail');
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $summary = $metrics->getSummary();
        $this->assertSame(1, $summary['total_requests']);
        $this->assertSame(0, $summary['successful_requests']);
        $this->assertSame(0, $costTracker->getRagieQueries());
    }

    public function testRetrieveAsyncResolvesToRetrievalResult(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('Async result');
        $chunk->setScore(0.91);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieveAsync')
            ->willReturn(Create::promiseFor($retrieval));

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        $result = $client->retrieveAsync('async query')->wait();

        $this->assertInstanceOf(RetrievalResult::class, $result);
        $this->assertSame('Async result', $result->getTopChunk()?->getText());
    }

    public function testRetrieveAsyncUsesCacheForSubsequentCalls(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('Cached async result');
        $chunk->setScore(0.5);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieveAsync')
            ->willReturn(Create::promiseFor($retrieval));

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);
        $client->withCache(new InMemoryCache());

        $first = $client->retrieveAsync('async cached query')->wait();
        $second = $client->retrieveAsync('async cached query')->wait();

        $this->assertSame('Cached async result', $first->getTopChunk()?->getText());
        $this->assertSame('Cached async result', $second->getTopChunk()?->getText());
    }

    public function testRetrieveAsyncPropagatesExceptions(): void
    {
        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieveAsync')
            ->willReturn(Create::rejectionFor(new \RuntimeException('async boom')));

        $client = new class ('test-key') extends Client {
            private ?RetrievalsApi $mockApi = null;

            public function setMockRetrievalsApi(RetrievalsApi $api): void
            {
                $this->mockApi = $api;
            }

            #[\Override]
            public function getRetrievalsApi(): RetrievalsApi
            {
                return $this->mockApi ?? parent::getRetrievalsApi();
            }
        };

        $client->setMockRetrievalsApi($mockApi);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('async boom');

        $client->retrieveAsync('fails now')->wait();
    }

    public function testRetrieveConcurrentReturnsResultsInOriginalOrder(): void
    {
        $client = new class ('test-key') extends Client {
            #[\Override]
            public function retrieveAsync(string $query, ?RetrievalOptions $options = null): \GuzzleHttp\Promise\PromiseInterface
            {
                $chunk = new ScoredChunk();
                $chunk->setText($query);
                $retrieval = new Retrieval();
                $retrieval->setScoredChunks([$chunk]);

                return Create::promiseFor(new RetrievalResult($retrieval));
            }
        };

        $queries = ['first question', 'second question', 'third question'];

        $results = $client->retrieveConcurrent($queries);

        $this->assertCount(3, $results);
        $this->assertSame('first question', $results[0]->getTopChunk()?->getText());
        $this->assertSame('second question', $results[1]->getTopChunk()?->getText());
        $this->assertSame('third question', $results[2]->getTopChunk()?->getText());
    }
}
