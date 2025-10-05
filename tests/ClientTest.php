<?php
// this_file: tests/ClientTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use GuzzleHttp\Client as GuzzleClient;
use PHPUnit\Framework\TestCase;
use Ragie\Api\Api\DocumentsApi;
use Ragie\Api\Api\RetrievalsApi;
use Ragie\Api\Api\ResponsesApi;
use Ragie\Api\Configuration;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Client;
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key cannot be empty');

        new Client('');
    }

    public function testConstructorThrowsExceptionForWhitespaceApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Query cannot be empty');

        $client = new Client('test-key');
        $client->retrieve('');
    }

    public function testRetrieveThrowsExceptionForWhitespaceQuery(): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
        $this->assertSame('Test chunk', $result->getTopChunk()->getText());
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
}
