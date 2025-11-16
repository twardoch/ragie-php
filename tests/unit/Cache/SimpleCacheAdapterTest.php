<?php

// this_file: tests/unit/Cache/SimpleCacheAdapterTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Cache\SimpleCacheQueryCache;
use Ragie\RetrievalResult;

final class SimpleCacheAdapterTest extends TestCase
{
    public function testGetReturnsValueFromWrappedCache(): void
    {
        $result = $this->createResult('from simple cache');
        $simpleCache = $this->createMock(CacheInterface::class);
        $simpleCache->expects($this->once())
            ->method('get')
            ->with('answer_key')
            ->willReturn($result);

        $adapter = new SimpleCacheQueryCache($simpleCache);

        $this->assertSame($result, $adapter->get('answer_key'));
    }

    public function testSetPersistsValueWithTtl(): void
    {
        $result = $this->createResult('stored');
        $simpleCache = $this->createMock(CacheInterface::class);
        $simpleCache->expects($this->once())
            ->method('set')
            ->with('cache_key', $result, 120);

        $adapter = new SimpleCacheQueryCache($simpleCache);
        $adapter->set('cache_key', $result, 120);
    }

    public function testHasDelegatesToCache(): void
    {
        $simpleCache = $this->createMock(CacheInterface::class);
        $simpleCache->expects($this->once())
            ->method('has')
            ->with('cache_key')
            ->willReturn(true);

        $adapter = new SimpleCacheQueryCache($simpleCache);

        $this->assertTrue($adapter->has('cache_key'));
    }

    public function testClearDelegatesToCache(): void
    {
        $simpleCache = $this->createMock(CacheInterface::class);
        $simpleCache->expects($this->once())
            ->method('clear');

        $adapter = new SimpleCacheQueryCache($simpleCache);
        $adapter->clear();
    }

    private function createResult(string $text): RetrievalResult
    {
        $chunk = new ScoredChunk();
        $chunk->setText($text);
        $chunk->setScore(0.87);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        return new RetrievalResult($retrieval);
    }
}
