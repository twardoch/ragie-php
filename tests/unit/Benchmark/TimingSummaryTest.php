<?php

// this_file: tests/unit/Benchmark/TimingSummaryTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ragie\Benchmark\TimingSummary;

final class TimingSummaryTest extends TestCase
{
    public function testFromTimingsCalculatesBasicStats(): void
    {
        $summary = TimingSummary::fromTimings([10, 20, 30, 40]);

        $this->assertSame(25.0, $summary->getAverageMs());
        $this->assertSame(10.0, $summary->getMinMs());
        $this->assertSame(40.0, $summary->getMaxMs());
        $this->assertSame(4, $summary->getCount());
    }

    public function testPercentileInterpolationProducesExpectedValues(): void
    {
        $summary = TimingSummary::fromTimings([10, 20, 30, 40, 50]);

        $this->assertSame(30.0, $summary->getPercentileMs(50));
        $this->assertSame(46.0, $summary->getPercentileMs(90));
        $this->assertSame(50.0, $summary->getPercentileMs(100));

        $expected = [
            'count' => 5,
            'average_ms' => 30.0,
            'min_ms' => 10.0,
            'max_ms' => 50.0,
            'p50_ms' => 30.0,
            'p95_ms' => 48.0,
            'p99_ms' => 49.6,
        ];

        $this->assertEqualsWithDelta($expected, $summary->toArray(), 0.0001);
    }

    public function testInvalidInputsThrowExceptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TimingSummary::fromTimings([]);
    }

    public function testNegativeTimingsThrowExceptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TimingSummary::fromTimings([-1]);
    }

    public function testInvalidPercentileThrows(): void
    {
        $summary = TimingSummary::fromTimings([1]);

        $this->expectException(InvalidArgumentException::class);
        $summary->getPercentileMs(0);
    }
}
