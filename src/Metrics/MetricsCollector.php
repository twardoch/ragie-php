<?php

declare(strict_types=1);

/**
 * this_file: src/Metrics/MetricsCollector.php
 */

namespace Ragie\Metrics;

/**
 * Lightweight in-memory collector for retrieval telemetry.
 */
final class MetricsCollector
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $events = [];

    public function recordRetrieval(string $source, int|float $durationMs, int $chunkCount, bool $success): void
    {
        $this->events[] = [
            'type' => 'retrieval',
            'source' => $source,
            'duration_ms' => max(0, (int) round($durationMs)),
            'chunk_count' => max(0, $chunkCount),
            'success' => $success,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @return array{
     *     total_requests: int,
     *     successful_requests: int,
     *     avg_duration_ms: int
     * }
     */
    public function getSummary(): array
    {
        $retrievals = array_filter(
            $this->events,
            fn (array $event): bool => ($event['type'] ?? '') === 'retrieval'
        );

        $totalRequests = count($retrievals);
        $successes = count(
            array_filter($retrievals, fn (array $event): bool => ($event['success'] ?? false) === true)
        );
        $avgDuration = $totalRequests > 0
            ? (int) round(array_sum(array_map(
                fn (array $event): int => (int) ($event['duration_ms'] ?? 0),
                $retrievals
            )) / $totalRequests)
            : 0;

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successes,
            'avg_duration_ms' => $avgDuration,
        ];
    }
}
