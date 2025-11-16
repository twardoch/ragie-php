#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Interactive CLI for exploring Ragie retrievals.
 *
 * this_file: examples/interactive-demo.php
 */

use Ragie\Api\ApiException;
use Ragie\Client;
use Ragie\Examples\ChunkFormatter;
use Ragie\Exception\InvalidQueryException;

require __DIR__ . '/../vendor/autoload.php';

const RAGIE_INTERACTIVE_DEFAULT_MAX_CHUNKS = 5;
const RAGIE_INTERACTIVE_DEFAULT_MAX_LENGTH = 220;

$topChunkLimit = (int) ($_ENV['RAGIE_INTERACTIVE_MAX_CHUNKS'] ?? getenv('RAGIE_INTERACTIVE_MAX_CHUNKS') ?: RAGIE_INTERACTIVE_DEFAULT_MAX_CHUNKS);
$topChunkLimit = $topChunkLimit > 0 ? $topChunkLimit : RAGIE_INTERACTIVE_DEFAULT_MAX_CHUNKS;

$maxChunkLength = (int) ($_ENV['RAGIE_INTERACTIVE_MAX_LENGTH'] ?? getenv('RAGIE_INTERACTIVE_MAX_LENGTH') ?: RAGIE_INTERACTIVE_DEFAULT_MAX_LENGTH);
$maxChunkLength = $maxChunkLength >= 40 ? $maxChunkLength : RAGIE_INTERACTIVE_DEFAULT_MAX_LENGTH;

$apiKey = getenv('RAGIE_API_KEY');
if ($apiKey === false || trim($apiKey) === '') {
    if (function_exists('readline')) {
        $apiKey = readline('Enter Ragie API key: ');
    } else {
        fwrite(STDOUT, 'Enter Ragie API key: ');
        $apiKeyInput = fgets(STDIN);
        $apiKey = $apiKeyInput === false ? '' : $apiKeyInput;
    }
}

$apiKey = trim((string) $apiKey);
if ($apiKey === '') {
    fwrite(STDERR, "RAGIE_API_KEY is required to run the demo.\n");
    exit(1);
}

$client = new Client($apiKey);
echo PHP_EOL;
echo "Ragie Interactive Retrieval Demo\n";
echo "Type 'quit' or press Ctrl+D to exit.\n";
echo "Showing top {$topChunkLimit} chunks (max {$maxChunkLength} chars each).\n\n";

while (true) {
    if (function_exists('readline')) {
        $input = readline('Query> ');
        if ($input === false) {
            echo PHP_EOL;
            break;
        }
    } else {
        fwrite(STDOUT, 'Query> ');
        $read = fgets(STDIN);
        if ($read === false) {
            echo PHP_EOL;
            break;
        }
        $input = $read;
    }

    $query = trim((string) $input);

    if ($query === '') {
        continue;
    }

    if (in_array(strtolower($query), ['quit', 'exit'], true)) {
        break;
    }

    $start = microtime(true);

    try {
        $result = $client->retrieve($query);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        if ($result->isEmpty()) {
            echo "No context found. Try refining the question.\n\n";
            continue;
        }

        $chunkCount = count($result);
        printf("→ %d chunk(s) in %d ms\n", $chunkCount, $durationMs);

        $chunks = array_slice($result->getChunks(), 0, $topChunkLimit);
        foreach ($chunks as $i => $chunk) {
            echo ChunkFormatter::formatChunk($chunk, $i + 1, $maxChunkLength) . PHP_EOL . PHP_EOL;
        }
    } catch (InvalidQueryException $invalidQuery) {
        printf("Invalid query: %s\n\n", $invalidQuery->getMessage());
    } catch (ApiException $apiException) {
        printf("Ragie API error (%d): %s\n\n", $apiException->getCode(), $apiException->getMessage());
    } catch (\Throwable $throwable) {
        printf("Unexpected error: %s\n\n", $throwable->getMessage());
    }
}

echo "Bye!\n";
