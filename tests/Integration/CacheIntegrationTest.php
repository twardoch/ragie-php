<?php

// this_file: tests/Integration/CacheIntegrationTest.php

declare(strict_types=1);

namespace Ragie\Tests\Integration;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Ragie\Cache\InMemoryCache;
use Ragie\Client;
use Ragie\RetrievalOptions;

final class CacheIntegrationTest extends TestCase
{
    public function test_retrieve_uses_cache_for_identical_queries(): void
    {
        $mock = new MockHandler([
            $this->successfulResponse('Cacheable chunk'),
            $this->errorResponse(),
        ]);

        $client = $this->makeClient($mock)->withCache(new InMemoryCache(), 60);

        $first = $client->retrieve('Cache me');
        self::assertCount(1, $first->getChunks());

        $second = $client->retrieve('Cache me');

        self::assertSame($first, $second, 'Cached result should be returned for identical queries.');
        self::assertSame(1, count($mock), 'Second HTTP response must remain unused when cache hits.');
    }

    public function test_retrieve_with_different_options_bypasses_cache(): void
    {
        $mock = new MockHandler([
            $this->successfulResponse('Default options'),
            $this->successfulResponse('Custom options'),
        ]);

        $client = $this->makeClient($mock)->withCache(new InMemoryCache(), 120);

        $default = $client->retrieve('Cache divergence');
        self::assertCount(1, $default->getChunks());

        $customOptions = RetrievalOptions::create()->withTopK(2);
        $custom = $client->retrieve('Cache divergence', $customOptions);

        self::assertNotSame($default, $custom, 'Different retrieval options should result in a cache miss.');
        self::assertSame(0, count($mock), 'Both mock responses should be consumed when cache misses.');
    }

    private function makeClient(MockHandler $mock): Client
    {
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new GuzzleHttpClient(['handler' => $handlerStack]);

        return new Client('test-key', null, $httpClient);
    }

    private function successfulResponse(string $chunkText): Response
    {
        $payload = [
            'id' => 'ret_' . md5($chunkText),
            'query' => 'cache-test',
            'scored_chunks' => [
                [
                    'id' => 'chunk_' . md5($chunkText),
                    'document_id' => 'doc',
                    'text' => $chunkText,
                    'score' => 0.91,
                ],
            ],
        ];

        return new Response(200, [], (string) json_encode($payload));
    }

    private function errorResponse(): Response
    {
        return new Response(500, [], 'Should remain unused when cache hits');
    }
}
