<?php

/**
 * Concurrent retrieval example for Ragie PHP Client.
 *
 * Demonstrates the difference between sequential `retrieveBatch()` calls
 * and the built-in `retrieveConcurrent()` helper that reuses promises.
 *
 * this_file: ragie-php/examples/retrieve_concurrent.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ragie\Client;
use Ragie\RetrievalOptions;

$apiKey = getenv('RAGIE_API_KEY');

if ($apiKey === false || $apiKey === '') {
    fwrite(STDERR, "Set RAGIE_API_KEY before running this example.\n");
    exit(1);
}

$queries = [
    'What is Retrieval Augmented Generation?',
    'How do Ragie partitions work?',
    'Summarize Ragie client best practices',
];

$options = RetrievalOptions::create()
    ->withTopK(5)
    ->withRerank(true);

$ragie = (new Client($apiKey))
    ->withDefaultTopK(5)
    ->withDefaultRerank(true);

try {
    echo "Running sequential batch retrieval...\n";
    $sequential = timeOperation(fn (): array => $ragie->retrieveBatch($queries, $options));

    echo "\nRunning concurrent retrieval...\n";
    $concurrent = timeOperation(fn (): array => $ragie->retrieveConcurrent($queries, $options));

    echo "\nSequential duration: {$sequential['duration_ms']} ms\n";
    echo "Concurrent duration: {$concurrent['duration_ms']} ms\n";
    echo "Queries executed: " . count($queries) . "\n";

    echo "\nTop chunk previews:\n";
    foreach ($concurrent['results'] as $index => $result) {
        $top = $result->getTopChunk();
        printf(
            "  [%d] %s -> %s (score %.4f)\n",
            $index + 1,
            $queries[$index],
            $top?->getDocumentName() ?? 'no chunks',
            $top?->getScore() ?? 0.0
        );
    }

    echo "\nTip: increase the query list or compare durations with/without Ragie caching enabled.\n";
} catch (\Ragie\Api\ApiException $e) {
    fwrite(STDERR, "API Error: {$e->getMessage()}\n");
    exit(1);
} catch (\Throwable $e) {
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}

/**
 * Helper to measure execution time for a callable returning RetrievalResult[].
 *
 * @param callable():array $operation
 *
 * @return array{results:array,duration_ms:int}
 */
function timeOperation(callable $operation): array
{
    $start = microtime(true);
    $results = $operation();

    return [
        'results' => $results,
        'duration_ms' => (int) round((microtime(true) - $start) * 1000),
    ];
}
