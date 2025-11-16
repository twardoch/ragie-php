#!/usr/bin/env php
<?php

// this_file: benchmarks/RetrievalBenchmark.php

declare(strict_types=1);

use Ragie\Benchmark\TimingSummary;
use Ragie\Client;

require __DIR__ . '/../vendor/autoload.php';

$apiKey = getenv('RAGIE_API_KEY') ?: ($_ENV['RAGIE_API_KEY'] ?? null);

if ($apiKey === null) {
    fwrite(
        STDERR,
        "Missing RAGIE_API_KEY. Set the variable before running the benchmark.\n"
    );
    exit(1);
}

$iterations = (int) (getenv('RAGIE_BENCH_ITERATIONS') ?? ($_ENV['RAGIE_BENCH_ITERATIONS'] ?? 3));

if ($iterations < 1) {
    $iterations = 3;
}

$delaySeconds = (float) (getenv('RAGIE_BENCH_DELAY') ?? ($_ENV['RAGIE_BENCH_DELAY'] ?? 0));

$queriesEnv = getenv('RAGIE_BENCH_QUERIES') ?? ($_ENV['RAGIE_BENCH_QUERIES'] ?? '');
$queries = array_values(array_filter(array_map('trim', explode('|', $queriesEnv))));

if ($queries === []) {
    $queries = [
        'What is Ragie AI?',
        'How does the AskYoda fallback behave?',
        'Summarize the Ragie PHP caching layer.',
    ];
}

printf(
    "\nRunning Ragie retrieval benchmark with %d iteration(s) per query...\n\n",
    $iterations
);

$client = new Client($apiKey);
$overallSuccess = true;

foreach ($queries as $query) {
    printf("Query: %s\n", $query);

    $timings = [];
    $chunkCounts = [];
    $errors = [];

    for ($i = 1; $i <= $iterations; $i++) {
        $start = microtime(true);

        try {
            $result = $client->retrieve($query);
            $duration = (microtime(true) - $start) * 1000;
            $timings[] = $duration;
            $chunkCounts[] = count($result->getChunks());

            printf(
                "  #%d success in %6.1f ms (%d chunks)\n",
                $i,
                $duration,
                end($chunkCounts)
            );
        } catch (Throwable $exception) {
            $duration = (microtime(true) - $start) * 1000;
            $errors[] = [
                'iteration' => $i,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'duration' => $duration,
            ];

            printf(
                "  #%d error after %6.1f ms: %s\n",
                $i,
                $duration,
                $exception->getMessage()
            );
        }

        if ($delaySeconds > 0 && $i < $iterations) {
            usleep((int) ($delaySeconds * 1_000_000));
        }
    }

    if ($timings !== []) {
        $summary = TimingSummary::fromTimings($timings);
        $avgChunks = $chunkCounts === [] ? 0.0 : array_sum($chunkCounts) / count($chunkCounts);

        printf(
            "  Summary: avg=%6.1f ms | p95=%6.1f ms | min=%6.1f ms | max=%6.1f ms | successes=%d/%d | chunks≈%.1f\n",
            $summary->getAverageMs(),
            $summary->getPercentileMs(95),
            $summary->getMinMs(),
            $summary->getMaxMs(),
            count($timings),
            $iterations,
            $avgChunks
        );
    } else {
        $overallSuccess = false;
        echo "  No successful runs recorded for this query.\n";
    }

    if ($errors !== []) {
        echo "  Failures:\n";
        foreach ($errors as $error) {
            printf(
                "    #%d code:%s after %6.1f ms -> %s\n",
                $error['iteration'],
                (string) $error['code'],
                $error['duration'],
                $error['message']
            );
        }
    }

    echo "\n";
}

if (!$overallSuccess) {
    fwrite(STDERR, "One or more queries failed completely. Check credentials and retry later.\n");
    exit(2);
}
