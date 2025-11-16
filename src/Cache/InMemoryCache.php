<?php

// this_file: src/Cache/InMemoryCache.php

declare(strict_types=1);

namespace Ragie\Cache;

use Closure;
use Ragie\RetrievalResult;

/**
 * Lightweight per-request cache for retrieval responses.
 */
final class InMemoryCache implements QueryCacheInterface
{
    /** @var array<string, RetrievalResult> */
    private array $cache = [];

    /** @var array<string, int> */
    private array $expiry = [];

    private Closure $clock;

    /**
     * @param callable():int|null $clock Allows deterministic time control during testing
     */
    public function __construct(?callable $clock = null)
    {
        $this->clock = $clock !== null
            ? Closure::fromCallable($clock)
            : static fn (): int => time();
    }

    #[\Override]
    public function get(string $key): ?RetrievalResult
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->cache[$key];
    }

    #[\Override]
    public function set(string $key, RetrievalResult $result, int $ttl): void
    {
        if ($ttl <= 0) {
            unset($this->cache[$key], $this->expiry[$key]);
            return;
        }

        $this->cache[$key] = $result;
        $this->expiry[$key] = $this->now() + $ttl;
    }

    #[\Override]
    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $expiry = $this->expiry[$key] ?? 0;

        if ($expiry <= $this->now()) {
            unset($this->cache[$key], $this->expiry[$key]);
            return false;
        }

        return true;
    }

    #[\Override]
    public function clear(): void
    {
        $this->cache = [];
        $this->expiry = [];
    }

    private function now(): int
    {
        return (int) ($this->clock)();
    }
}
