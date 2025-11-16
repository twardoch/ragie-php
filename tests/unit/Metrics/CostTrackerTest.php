<?php

// this_file: tests/unit/Metrics/CostTrackerTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Metrics\CostTracker;

final class CostTrackerTest extends TestCase
{
    public function testRecordRagieQueryAccumulatesCost(): void
    {
        $tracker = new CostTracker();

        $tracker->recordRagieQuery();
        $tracker->recordRagieQuery(0.005);

        $this->assertSame(2, $tracker->getRagieQueries());
        $this->assertGreaterThan(0.0, $tracker->getTotalCost());

        $summary = $tracker->getSummary();
        $this->assertSame(2, $summary['ragie_queries']);
        $this->assertEquals($tracker->getTotalCost(), $summary['total_cost']);
    }

}
