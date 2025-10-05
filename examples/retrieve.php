<?php

/**
 * Basic retrieval example for Ragie PHP Client
 *
 * This example demonstrates the simplest way to retrieve relevant document chunks.
 *
 * this_file: examples/retrieve.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ragie\Client;

// Initialize the client with your API key
$apiKey = getenv('RAGIE_API_KEY') ?: 'your-api-key-here';
$ragie = new Client($apiKey);

try {
    // Perform a simple retrieval
    $result = $ragie->retrieve('What is machine learning?');

    // Check if we got results
    if ($result->isEmpty()) {
        echo "No results found.\n";
        exit(0);
    }

    // Display the number of results
    echo "Found {$result->count()} relevant chunks:\n\n";

    // Display the top result
    $topChunk = $result->getTopChunk();
    if ($topChunk !== null) {
        echo "Top result (score: {$topChunk->getScore()}):\n";
        echo "Document: {$topChunk->getDocumentName()}\n";
        echo "Text: {$topChunk->getText()}\n\n";
    }

    // Display all results
    echo "All results:\n";
    echo str_repeat('-', 80) . "\n";

    foreach ($result->getChunks() as $i => $chunk) {
        $num = $i + 1;
        echo "{$num}. [{$chunk->getDocumentName()}] (score: {$chunk->getScore()})\n";
        echo "   " . substr($chunk->getText(), 0, 100) . "...\n\n";
    }

    // Get just the text content
    $texts = $result->getChunkTexts();
    echo "\nExtracted " . count($texts) . " text snippets.\n";

    // Get unique document IDs
    $documentIds = $result->getDocumentIds();
    echo "Results span " . count($documentIds) . " unique documents.\n";
} catch (\Ragie\Api\ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}
