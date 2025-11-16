<?php

declare(strict_types=1);

/**
 * this_file: src/Metrics/CostTracker.php
 */

namespace Ragie\Metrics;

/**
 * Tracks approximate spend for Ragie retrieval calls.
 */
final class CostTracker
{
    private const RAGIE_COST_PER_QUERY = 0.001;

    private float $totalCost = 0.0;
    private int $ragieQueries = 0;

    public function recordRagieQuery(?float $costOverride = null): void
    {
        $this->ragieQueries++;
        $this->totalCost += $costOverride ?? self::RAGIE_COST_PER_QUERY;
    }

    public function getTotalCost(): float
    {
        return round($this->totalCost, 6);
    }

    public function getRagieQueries(): int
    {
        return $this->ragieQueries;
    }

    /**
     * @return array{
     *     total_cost: float,
     *     ragie_queries: int
     * }
     */
    public function getSummary(): array
    {
        return [
            'total_cost' => $this->getTotalCost(),
            'ragie_queries' => $this->ragieQueries,
        ];
    }
}
