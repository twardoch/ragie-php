<?php

/**
 * Cached retrieval example for Ragie PHP Client.
 *
 * Demonstrates how to enable the built-in QueryCache so repeat questions
 * reuse the last RetrievalResult instead of hitting the Ragie API again.
 *
 * this_file: ragie-php/examples/retrieve_with_cache.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ragie\Cache\InMemoryCache;
use Ragie\Client;
use Ragie\RetrievalResult;

$apiKey = getenv('RAGIE_API_KEY');

if ($apiKey === false || $apiKey === '') {
    fwrite(STDERR, "Set RAGIE_API_KEY before running this example.\n");
    exit(1);
}

$cacheTtlSeconds = (int) (getenv('RAGIE_CACHE_TTL') ?: 600);
$query = $argv[1] ?? 'How does Ragie index my documents?';

// Configure the client with fluent defaults plus cache wiring.
$ragie = (new Client($apiKey))
    ->withDefaultTopK(5)
    ->withCache(new InMemoryCache(), $cacheTtlSeconds);

printf("Running cached retrieval for \"%s\" (TTL: %ds)\n\n", $query, $cacheTtlSeconds);

try {
    $first = timedRetrieve($ragie, $query, 'First call');
    $second = timedRetrieve($ragie, $query, 'Second call (cache hit expected)');

    echo "\nResult summary:\n";
    echo sprintf("  Chunk count: %d\n", $first->count());
    echo sprintf("  Unique document IDs: %d\n", count($first->getDocumentIds()));

    $top = $first->getTopChunk();
    if ($top !== null) {
        echo "\nTop chunk preview:\n";
        echo sprintf("  Document: %s\n", $top->getDocumentName());
        echo sprintf("  Score: %.4f\n", $top->getScore());
        echo sprintf("  Text: %s\n", substr($top->getText(), 0, 200)) . "\n";
    }

    echo "\nTip: change the query text or lower the TTL to observe cache invalidation.\n";
} catch (\Ragie\Api\ApiException $e) {
    fwrite(STDERR, "API Error: {$e->getMessage()}\n");
    exit(1);
} catch (\Throwable $e) {
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}

/**
 * Retrieve a question while printing elapsed time so cache behavior is visible.
 */
function timedRetrieve(Client $client, string $query, string $label): RetrievalResult
{
    $start = microtime(true);
    $result = $client->retrieve($query);
    $elapsedMs = (int) round((microtime(true) - $start) * 1000);

    echo sprintf("%s: %d chunks in %d ms\n", $label, $result->count(), $elapsedMs);

    return $result;
}
