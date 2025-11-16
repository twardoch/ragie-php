#!/usr/bin/env php
<?php

declare(strict_types=1);

// this_file: ragie-php/coverage-checker.php

use Ragie\Dev\Coverage\CoverageThresholdChecker;
use RuntimeException;

require __DIR__ . '/vendor/autoload.php';

$arguments = $argv ?? [];

if (count($arguments) < 3) {
    fwrite(
        STDERR,
        "Usage: php coverage-checker.php <coverage.xml> <minimumPercent>\n"
        . "Example: php coverage-checker.php coverage.xml 80\n",
    );
    exit(1);
}

$cloverPath = $arguments[1];
$threshold = (float) $arguments[2];
$checker = new CoverageThresholdChecker();

try {
    $coverage = $checker->assertMinimum($cloverPath, $threshold);
    fwrite(STDOUT, sprintf("Line coverage %.2f%% meets the %.2f%% threshold.\n", $coverage, $threshold));
    exit(0);
} catch (RuntimeException $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(2);
}
