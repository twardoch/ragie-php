<?php

// this_file: src/Benchmark/TimingSummary.php

declare(strict_types=1);

namespace Ragie\Benchmark;

use InvalidArgumentException;

/**
 * Aggregates retrieval benchmark timings into helpful summary statistics.
 *
 * The summary is deterministic because timings are sorted on construction and
 * percentile calculations use linear interpolation between ranks.
 */
final class TimingSummary
{
    /**
     * @param list<float> $timings Sorted list of timings (milliseconds)
     */
    private function __construct(
        private readonly array $timings,
        private readonly float $averageMs,
        private readonly float $minMs,
        private readonly float $maxMs,
    ) {
    }

    /**
     * @param array<float|int> $timings Timings in milliseconds
     */
    public static function fromTimings(array $timings): self
    {
        if ($timings === []) {
            throw new InvalidArgumentException('At least one timing is required');
        }

        $normalized = [];

        foreach ($timings as $timing) {
            if (!is_numeric($timing)) {
                throw new InvalidArgumentException('Timings must be numeric values in milliseconds');
            }

            $value = (float) $timing;

            if ($value < 0) {
                throw new InvalidArgumentException('Timings cannot be negative');
            }

            $normalized[] = $value;
        }

        sort($normalized);

        $count = count($normalized);
        $sum = array_sum($normalized);

        return new self(
            $normalized,
            $sum / (float) $count,
            $normalized[0],
            $normalized[$count - 1],
        );
    }

    public function getAverageMs(): float
    {
        return $this->averageMs;
    }

    public function getMinMs(): float
    {
        return $this->minMs;
    }

    public function getMaxMs(): float
    {
        return $this->maxMs;
    }

    public function getCount(): int
    {
        return count($this->timings);
    }

    /**
     * Returns the interpolated percentile in milliseconds.
     */
    public function getPercentileMs(float $percentile): float
    {
        if ($percentile <= 0 || $percentile > 100) {
            throw new InvalidArgumentException('Percentile must be between 0 and 100');
        }

        $count = count($this->timings);

        if ($count === 1) {
            return $this->timings[0];
        }

        $position = ($percentile / 100.0) * ((float) ($count - 1));
        $lowerIndex = (int) floor($position);
        $upperIndex = (int) ceil($position);

        if ($lowerIndex === $upperIndex) {
            return $this->timings[$lowerIndex];
        }

        $lowerValue = $this->timings[$lowerIndex];
        $upperValue = $this->timings[$upperIndex];
        $fraction = $position - (float) $lowerIndex;

        return $lowerValue + (($upperValue - $lowerValue) * $fraction);
    }

    /**
     * Export summary data for downstream reporting.
     *
     * @return array{
     *     count: int,
     *     average_ms: float,
     *     min_ms: float,
     *     max_ms: float,
     *     p50_ms: float,
     *     p95_ms: float,
     *     p99_ms: float
     * }
     */
    public function toArray(): array
    {
        return [
            'count' => $this->getCount(),
            'average_ms' => $this->getAverageMs(),
            'min_ms' => $this->getMinMs(),
            'max_ms' => $this->getMaxMs(),
            'p50_ms' => $this->getPercentileMs(50),
            'p95_ms' => $this->getPercentileMs(95),
            'p99_ms' => $this->getPercentileMs(99),
        ];
    }
}
