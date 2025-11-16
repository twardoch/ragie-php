<?php

declare(strict_types=1);

// this_file: ragie-php/tests/unit/Dev/Coverage/CoverageThresholdCheckerTest.php

namespace Ragie\Tests\Unit\Dev\Coverage;

use PHPUnit\Framework\TestCase;
use Ragie\Dev\Coverage\CoverageThresholdChecker;
use RuntimeException;

final class CoverageThresholdCheckerTest extends TestCase
{
    private CoverageThresholdChecker $checker;

    #[\Override]
    protected function setUp(): void
    {
        $this->checker = new CoverageThresholdChecker();
    }

    public function testCalculate_whenReportValid_thenReturnsRoundedPercent(): void
    {
        $path = __DIR__ . '/../../../fixtures/coverage/high.xml';
        $coverage = $this->checker->calculate($path);

        self::assertSame(95.0, $coverage, 'Expected to return rounded coverage percentage.');
    }

    public function testCalculate_whenStatementsZero_thenReturnsHundredPercent(): void
    {
        $path = __DIR__ . '/../../../fixtures/coverage/empty.xml';
        $coverage = $this->checker->calculate($path);

        self::assertSame(100.0, $coverage, 'No statements should be treated as full coverage.');
    }

    public function testAssertMinimum_whenBelowThreshold_thenThrows(): void
    {
        $path = __DIR__ . '/../../../fixtures/coverage/low.xml';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Coverage 60.00% is below the required 80.00% threshold.');

        $this->checker->assertMinimum($path, 80.0);
    }

    public function testAssertMinimum_whenWithinThreshold_thenReturnsCoverage(): void
    {
        $path = __DIR__ . '/../../../fixtures/coverage/high.xml';

        $result = $this->checker->assertMinimum($path, 80.0);

        self::assertSame(95.0, $result);
    }

    public function testAssertMinimum_whenThresholdOutsideBounds_thenThrows(): void
    {
        $path = __DIR__ . '/../../../fixtures/coverage/high.xml';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Coverage threshold must be between 0 and 100.');

        $this->checker->assertMinimum($path, 120.0);
    }
}
