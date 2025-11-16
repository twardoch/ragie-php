<?php

// this_file: tests/Integration/RetryIntegrationTest.php

declare(strict_types=1);

namespace Ragie\Tests\Integration;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Ragie\Api\ApiException;
use Ragie\Client;
use Ragie\Retry\RetryPolicy;

class RetryIntegrationTest extends TestCase
{
    public function testClientRetriesOnNetworkError(): void
    {
        // Create mock handler that fails twice then succeeds
        $mock = new MockHandler([
            new ConnectException('Connection timeout', new Request('POST', 'test')),
            new ConnectException('Connection timeout', new Request('POST', 'test')),
            new Response(200, [], json_encode([
                'id' => 'ret_123',
                'query' => 'test query',
                'scored_chunks' => [],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $result = $client->retrieve('test query');

        // Should succeed after retries
        $this->assertCount(0, $result->getChunks());
    }

    public function testClientRetriesOn500Error(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal server error'),
            new Response(500, [], 'Internal server error'),
            new Response(200, [], json_encode([
                'id' => 'ret_123',
                'query' => 'test query',
                'scored_chunks' => [],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $result = $client->retrieve('test query');

        $this->assertCount(0, $result->getChunks());
    }

    public function testClientRetriesOnGatewayTimeout(): void
    {
        $mock = new MockHandler([
            new Response(504, [], 'Gateway timeout'),
            new Response(504, [], 'Gateway timeout'),
            new Response(200, [], json_encode([
                'id' => 'ret_123',
                'query' => 'test query',
                'scored_chunks' => [],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $result = $client->retrieve('test query');

        $this->assertCount(0, $result->getChunks());
    }

    public function testClientSurfaces429AfterRetryBudgetExhausted(): void
    {
        $mock = new MockHandler([
            new Response(429, [], json_encode(['error' => 'Rate limit exceeded'])),
            new Response(429, [], json_encode(['error' => 'Rate limit exceeded'])),
            new Response(429, [], json_encode(['error' => 'Rate limit exceeded'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(429);

        $client->retrieve('test query');
    }

    public function testClientRetriesOn429AndEventuallySucceeds(): void
    {
        $mock = new MockHandler([
            new Response(429, [], json_encode(['error' => 'Rate limit exceeded'])),
            new Response(200, [], json_encode([
                'id' => 'ret_success',
                'query' => 'test query',
                'scored_chunks' => [],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 2));

        $result = $client->retrieve('test query');

        $this->assertCount(0, $result->getChunks(), 'Successful retry should still return a valid result instance.');
    }

    public function testClientDoesNotRetryOn400Error(): void
    {
        $mock = new MockHandler([
            new Response(400, [], json_encode(['error' => 'Bad request'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $this->expectException(ApiException::class);

        $client->retrieve('test query');

        // Should only call API once (no retry)
        $this->assertCount(0, $mock);
    }

    public function testClientWithoutRetryPolicyDoesNotRetry(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal server error'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = new Client('test-key', null, $httpClient);
        // No retry policy configured

        $this->expectException(ApiException::class);

        $client->retrieve('test query');
    }

    public function testClientThrowsAfterMaxRetries(): void
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal server error'),
            new Response(500, [], 'Internal server error'),
            new Response(500, [], 'Internal server error'),
            new Response(500, [], 'Internal server error'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handlerStack]);

        $client = (new Client('test-key', null, $httpClient))
            ->withRetryPolicy(new RetryPolicy(maxAttempts: 3));

        $this->expectException(ApiException::class);

        $client->retrieve('test query');
    }
}
