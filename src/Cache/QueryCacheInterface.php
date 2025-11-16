<?php

// this_file: src/Cache/QueryCacheInterface.php

declare(strict_types=1);

namespace Ragie\Cache;

use Ragie\RetrievalResult;

/**
 * Interface for opt-in retrieval result caching.
 */
interface QueryCacheInterface
{
    /**
     * Fetch a cached retrieval result if present and not expired.
     */
    public function get(string $key): ?RetrievalResult;

    /**
     * Persist a retrieval result for the specified TTL (seconds).
     */
    public function set(string $key, RetrievalResult $result, int $ttl): void;

    /**
     * Determine whether a cache entry exists and is still valid.
     */
    public function has(string $key): bool;

    /**
     * Remove all cached entries.
     */
    public function clear(): void;
}
