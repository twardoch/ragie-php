<?php

// this_file: src/Cache/SimpleCacheQueryCache.php

declare(strict_types=1);

namespace Ragie\Cache;

use Psr\SimpleCache\CacheInterface;
use Ragie\RetrievalResult;

/**
 * Adapter that allows any PSR-16 cache to back query caching.
 */
final class SimpleCacheQueryCache implements QueryCacheInterface
{
    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    #[\Override]
    public function get(string $key): ?RetrievalResult
    {
        $value = $this->cache->get($key);

        return $value instanceof RetrievalResult ? $value : null;
    }

    #[\Override]
    public function set(string $key, RetrievalResult $result, int $ttl): void
    {
        $this->cache->set($key, $result, $ttl);
    }

    #[\Override]
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    #[\Override]
    public function clear(): void
    {
        $this->cache->clear();
    }
}
