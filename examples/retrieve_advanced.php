<?php

/**
 * Advanced retrieval example with options for Ragie PHP Client
 *
 * This example demonstrates using RetrievalOptions to customize retrieval behavior.
 *
 * this_file: examples/retrieve_advanced.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ragie\Client;
use Ragie\RetrievalOptions;

// Initialize the client
$apiKey = getenv('RAGIE_API_KEY') ?: 'your-api-key-here';
$ragie = new Client($apiKey);

try {
    // Example 1: Retrieval with reranking
    echo "=== Example 1: With Reranking ===\n\n";

    $options = RetrievalOptions::create()
        ->withTopK(5)
        ->withRerank();

    $result = $ragie->retrieve('artificial intelligence applications', $options);
    echo "Found {$result->count()} results with reranking.\n\n";

    // Example 2: Retrieval with metadata filter
    echo "=== Example 2: With Metadata Filter ===\n\n";

    $options = RetrievalOptions::create()
        ->withTopK(10)
        ->withFilter([
            'category' => 'documentation',
        ]);

    $result = $ragie->retrieve('API usage examples', $options);
    echo "Found {$result->count()} results in 'documentation' category.\n\n";

    // Example 3: Complex filter with operators
    echo "=== Example 3: Complex Filter ===\n\n";

    $options = RetrievalOptions::create()
        ->withTopK(10)
        ->withFilter([
            'department' => ['$in' => ['sales', 'marketing']],
            'priority' => ['$gt' => 5],
        ]);

    $result = $ragie->retrieve('quarterly targets', $options);
    echo "Found {$result->count()} high-priority results from sales/marketing.\n\n";

    // Example 4: All options combined
    echo "=== Example 4: All Options ===\n\n";

    $options = RetrievalOptions::create()
        ->withTopK(20)
        ->withRerank()
        ->withMaxChunksPerDocument(3)
        ->withRecencyBias()
        ->withFilter(['status' => 'published']);

    $result = $ragie->retrieve('product updates', $options);

    echo "Found {$result->count()} results:\n";
    echo "- Top K: 20\n";
    echo "- Reranking: enabled\n";
    echo "- Max chunks per document: 3\n";
    echo "- Recency bias: enabled\n";
    echo "- Filter: status = published\n\n";

    // Display results
    if (!$result->isEmpty()) {
        echo "Top result:\n";
        $top = $result->getTopChunk();
        if ($top !== null) {
            echo "  Document: {$top->getDocumentName()}\n";
            echo "  Score: {$top->getScore()}\n";
            echo "  Text: " . substr($top->getText(), 0, 150) . "...\n\n";
        }

        echo "Documents in results:\n";
        foreach ($result->getDocumentIds() as $docId) {
            echo "  - {$docId}\n";
        }
    }

    // Example 5: Method chaining
    echo "\n=== Example 5: Fluent Method Chaining ===\n\n";

    $result = $ragie->retrieve(
        'machine learning best practices',
        RetrievalOptions::create()
            ->withTopK(15)
            ->withRerank(true)
            ->withMaxChunksPerDocument(2)
            ->withRecencyBias(true)
            ->withFilter(['type' => 'guide'])
    );

    echo "Retrieved {$result->count()} results using fluent interface.\n";

    // Example 6: Using partition
    echo "\n=== Example 6: Partition-Scoped Search ===\n\n";

    $options = RetrievalOptions::create()
        ->withPartition('customer-123')
        ->withTopK(10);

    $result = $ragie->retrieve('account history', $options);
    echo "Found {$result->count()} results in partition 'customer-123'.\n";
} catch (\Ragie\Api\ApiException $e) {
    echo "API Error: {$e->getMessage()}\n";
    exit(1);
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n=== Done ===\n";
