<?php

declare(strict_types=1);

// this_file: ragie-php/dev/Coverage/CoverageThresholdChecker.php

namespace Ragie\Dev\Coverage;

use RuntimeException;

/**
 * Parses PHPUnit Clover reports and enforces a minimum coverage percentage.
 */
final class CoverageThresholdChecker
{
    public function calculate(string $cloverPath): float
    {
        if (!is_file($cloverPath)) {
            throw new RuntimeException(sprintf('Coverage file "%s" not found.', $cloverPath));
        }

        $report = simplexml_load_file($cloverPath);
        if ($report === false) {
            throw new RuntimeException(sprintf('Coverage file "%s" is not valid Clover XML.', $cloverPath));
        }

        $metrics = $report->xpath('/coverage/project/metrics');
        if (!isset($metrics[0])) {
            throw new RuntimeException('Coverage metrics missing from Clover report.');
        }

        $statements = (int) $metrics[0]['statements'];
        $coveredStatements = (int) $metrics[0]['coveredstatements'];

        if ($statements === 0) {
            return 100.0;
        }

        return round(($coveredStatements / $statements) * 100, 2);
    }

    public function assertMinimum(string $cloverPath, float $minimumPercent): float
    {
        if ($minimumPercent < 0 || $minimumPercent > 100) {
            throw new RuntimeException('Coverage threshold must be between 0 and 100.');
        }

        $coverage = $this->calculate($cloverPath);

        if ($coverage < $minimumPercent) {
            throw new RuntimeException(
                sprintf('Coverage %.2f%% is below the required %.2f%% threshold.', $coverage, $minimumPercent),
            );
        }

        return $coverage;
    }
}
