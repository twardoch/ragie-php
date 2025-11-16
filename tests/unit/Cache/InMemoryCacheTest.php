<?php

// this_file: tests/unit/Cache/InMemoryCacheTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Cache\InMemoryCache;
use Ragie\RetrievalResult;

final class InMemoryCacheTest extends TestCase
{
    public function testSetAndGetStoresResultUntilExpiry(): void
    {
        $clock = new MutableClock(1_000);
        $cache = new InMemoryCache($clock);
        $result = $this->createResult('cached chunk');

        $cache->set('key', $result, 60);

        $this->assertTrue($cache->has('key'));
        $this->assertSame($result, $cache->get('key'));
    }

    public function testHasReturnsFalseWhenExpired(): void
    {
        $clock = new MutableClock(1_000);
        $cache = new InMemoryCache($clock);
        $result = $this->createResult('expired chunk');

        $cache->set('key', $result, 10);

        $this->assertTrue($cache->has('key'), 'Entry should exist before expiry');

        $clock->advance(11);

        $this->assertFalse($cache->has('key'), 'Entry should expire once TTL passed');
        $this->assertNull($cache->get('key'));
    }

    public function testClearRemovesEntries(): void
    {
        $cache = new InMemoryCache();
        $result = $this->createResult('clear me');

        $cache->set('key', $result, 60);
        $cache->clear();

        $this->assertFalse($cache->has('key'));
        $this->assertNull($cache->get('key'));
    }

    private function createResult(string $text): RetrievalResult
    {
        $chunk = new ScoredChunk();
        $chunk->setText($text);
        $chunk->setScore(0.99);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        return new RetrievalResult($retrieval);
    }
}

final class MutableClock
{
    public function __construct(private int $value)
    {
    }

    public function __invoke(): int
    {
        return $this->value;
    }

    public function advance(int $seconds): void
    {
        $this->value += $seconds;
    }
}
