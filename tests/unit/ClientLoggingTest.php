<?php

// this_file: tests/unit/ClientLoggingTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Api\Api\RetrievalsApi;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Client;
use Ragie\Tests\Logging\SpyLogger;

final class ClientLoggingTest extends TestCase
{
    public function testRetrieveEmitsStructuredLogs(): void
    {
        $retrieval = new Retrieval([
            'scored_chunks' => [new ScoredChunk(['text' => 'chunk', 'score' => 0.9])],
        ]);

        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willReturn($retrieval);

        $spy = new SpyLogger();
        $client = $this->clientWithMockApi($mockApi);
        $client->withLogger($spy);

        $client->retrieve('Explain Ragie');

        $this->assertGreaterThanOrEqual(2, count($spy->records));
        $this->assertSame('ragie.retrieve.start', $spy->records[0]['message']);
        $this->assertSame('ragie.retrieve.success', $spy->records[1]['message']);
        $this->assertArrayHasKey('chunk_count', $spy->records[1]['context']);
    }

    public function testRetrieveFailureIsLogged(): void
    {
        $mockApi = $this->createMock(RetrievalsApi::class);
        $mockApi->expects($this->once())
            ->method('retrieve')
            ->willThrowException(new \RuntimeException('boom'));

        $spy = new SpyLogger();
        $client = $this->clientWithMockApi($mockApi);
        $client->withLogger($spy);

        $this->expectException(\RuntimeException::class);
        try {
            $client->retrieve('Trigger failure');
        } finally {
            $this->assertGreaterThanOrEqual(2, count($spy->records));
            $this->assertSame('ragie.retrieve.failure', $spy->records[1]['message']);
            $this->assertSame('error', $spy->records[1]['level']);
        }
    }

    private function clientWithMockApi(RetrievalsApi $api): Client
    {
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

        $client->setMockRetrievalsApi($api);

        return $client;
    }
}
