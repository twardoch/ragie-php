<?php

// this_file: tests/unit/Metrics/MetricsCollectorTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Metrics\MetricsCollector;

final class MetricsCollectorTest extends TestCase
{
    public function testRecordRetrievalAndSummary(): void
    {
        $collector = new MetricsCollector();
        $collector->recordRetrieval('ragie', 120.4, 3, true);
        $collector->recordRetrieval('ragie', 250, 0, false);

        $events = $collector->getEvents();

        $this->assertCount(2, $events);
        $this->assertSame('ragie', $events[0]['source']);
        $this->assertSame(3, $events[0]['chunk_count']);
        $this->assertFalse($events[1]['success']);

        $summary = $collector->getSummary();
        $this->assertSame(2, $summary['total_requests']);
        $this->assertSame(1, $summary['successful_requests']);
        $this->assertGreaterThan(0, $summary['avg_duration_ms']);
    }
}
