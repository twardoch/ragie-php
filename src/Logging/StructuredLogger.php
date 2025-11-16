<?php

declare(strict_types=1);

/**
 * this_file: src/Logging/StructuredLogger.php
 */

namespace Ragie\Logging;

use Psr\Log\LoggerInterface;

final class StructuredLogger
{
    public function __construct(private ?LoggerInterface $logger = null)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logRetrievalStart(string $provider, string $query, array $context = []): void
    {
        $this->logger?->info('ragie.retrieve.start', array_merge($context, [
            'provider' => $provider,
            'query_preview' => $this->preview($query),
            'timestamp_ms' => $this->timestamp(),
        ]));
    }

    public function logRetrievalSuccess(string $provider, string $query, int $durationMs, int $chunkCount, bool $cacheHit): void
    {
        $this->logger?->info('ragie.retrieve.success', [
            'provider' => $provider,
            'query_preview' => $this->preview($query),
            'chunk_count' => $chunkCount,
            'duration_ms' => $durationMs,
            'cache_hit' => $cacheHit,
            'timestamp_ms' => $this->timestamp(),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logRetrievalFailure(string $provider, string $query, \Throwable $exception, int $durationMs, array $context = []): void
    {
        $this->logger?->error('ragie.retrieve.failure', array_merge($context, [
            'provider' => $provider,
            'query_preview' => $this->preview($query),
            'duration_ms' => $durationMs,
            'exception' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'timestamp_ms' => $this->timestamp(),
        ]));
    }

    private function preview(string $value): string
    {
        $flattened = trim((string) preg_replace('/\s+/u', ' ', $value));
        return mb_substr($flattened, 0, 120) ?: '';
    }

    private function timestamp(): int
    {
        return (int) round((float) microtime(true) * 1000.0);
    }
}
