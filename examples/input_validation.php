<?php

/**
 * Input Validation Example
 *
 * Demonstrates input validation patterns and best practices
 * for building robust RAG applications.
 *
 * this_file: examples/input_validation.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ragie\Client;
use Ragie\RetrievalOptions;
use Ragie\Exception\InvalidQueryException;
use Ragie\Exception\InvalidParameterException;

// Example 1: Query validation
echo "=== Example 1: Query Validation ===\n\n";

$apiKey = getenv('RAGIE_API_KEY') ?: 'your-api-key-here';
$ragie = new Client($apiKey);

function testQuery(Client $client, string $query, string $description): void
{
    echo "Testing: {$description}\n";
    echo "Query: \"" . (strlen($query) > 50 ? substr($query, 0, 50) . '...' : $query) . "\"\n";

    try {
        $result = $client->retrieve($query);
        echo "✓ Valid - Retrieved {$result->count()} chunks\n\n";
    } catch (InvalidQueryException $e) {
        echo "✗ Invalid - {$e->getMessage()}\n\n";
    }
}

// Test empty query
testQuery($ragie, '', 'Empty query');

// Test whitespace-only query
testQuery($ragie, '   ', 'Whitespace-only query');

// Test valid short query
testQuery($ragie, 'AI', 'Valid short query');

// Test valid normal query
testQuery($ragie, 'What is machine learning?', 'Valid normal query');

// Test query at max length (10K characters)
$maxQuery = str_repeat('a', 10000);
testQuery($ragie, $maxQuery, 'Query at max length (10K chars)');

// Test query exceeding max length
$tooLongQuery = str_repeat('a', 10001);
testQuery($ragie, $tooLongQuery, 'Query exceeding max length');

// Example 2: RetrievalOptions validation
echo "=== Example 2: RetrievalOptions Validation ===\n\n";

function testOptions(string $description, callable $optionsBuilder): void
{
    echo "Testing: {$description}\n";

    try {
        $options = $optionsBuilder();
        echo "✓ Valid options created\n\n";
    } catch (InvalidParameterException $e) {
        echo "✗ Invalid - {$e->getMessage()}\n\n";
    }
}

// Test valid options
testOptions('Valid top_k value', fn () => RetrievalOptions::create()->withTopK(10));

// Test invalid top_k (too small)
testOptions('top_k too small (0)', fn () => RetrievalOptions::create()->withTopK(0));

// Test invalid top_k (too large)
testOptions('top_k too large (1001)', fn () => RetrievalOptions::create()->withTopK(1001));

// Test valid max_chunks_per_document
testOptions('Valid max_chunks_per_document', fn () => RetrievalOptions::create()->withMaxChunksPerDocument(5));

// Test invalid max_chunks_per_document (negative)
testOptions(
    'max_chunks_per_document negative',
    fn () => RetrievalOptions::create()->withMaxChunksPerDocument(-1)
);

// Example 3: User input sanitization
echo "=== Example 3: User Input Sanitization ===\n\n";

class QuerySanitizer
{
    /**
     * Sanitize and validate user query
     */
    public static function sanitize(string $query): string
    {
        // Trim whitespace
        $query = trim($query);

        // Remove null bytes
        $query = str_replace("\0", '', $query);

        // Normalize whitespace
        $query = preg_replace('/\s+/', ' ', $query);

        // Remove potentially dangerous characters for logging
        // (but keep them for the actual search)
        return $query;
    }

    /**
     * Validate query meets requirements
     */
    public static function validate(string $query): void
    {
        $sanitized = self::sanitize($query);

        if ($sanitized === '') {
            throw new InvalidQueryException('Query cannot be empty after sanitization');
        }

        if (mb_strlen($sanitized) > 10000) {
            throw new InvalidQueryException(
                sprintf('Query too long: %d characters (max 10000)', mb_strlen($sanitized))
            );
        }

        // Check for suspiciously repetitive content
        if (preg_match('/(.)\1{100,}/', $sanitized)) {
            throw new InvalidQueryException('Query contains suspicious repetitive content');
        }
    }
}

$testInputs = [
    "  normal query  \n",
    "query\0with\0nullbytes",
    "query    with     multiple      spaces",
    str_repeat('a', 150),
    '',
];

foreach ($testInputs as $input) {
    $display = str_replace(["\0", "\n"], ['\\0', '\\n'], substr($input, 0, 50));
    echo "Input: \"{$display}\"\n";

    try {
        $sanitized = QuerySanitizer::sanitize($input);
        QuerySanitizer::validate($sanitized);
        echo "✓ Sanitized: \"{$sanitized}\"\n\n";
    } catch (InvalidQueryException $e) {
        echo "✗ {$e->getMessage()}\n\n";
    }
}

// Example 4: Production-ready search handler
echo "=== Example 4: Production Search Handler ===\n\n";

class SearchHandler
{
    public function __construct(private readonly Client $client)
    {
    }

    public function search(string $rawQuery, array $options = []): array
    {
        // Sanitize input
        $query = QuerySanitizer::sanitize($rawQuery);

        // Validate
        try {
            QuerySanitizer::validate($query);
        } catch (InvalidQueryException $e) {
            return [
                'success' => false,
                'error' => 'Invalid query: ' . $e->getMessage(),
                'code' => 'INVALID_QUERY',
            ];
        }

        // Build retrieval options
        try {
            $retrievalOptions = RetrievalOptions::create()
                ->withTopK($options['top_k'] ?? 10)
                ->withRerank($options['rerank'] ?? true);

            if (isset($options['filter'])) {
                $retrievalOptions->withFilter($options['filter']);
            }
        } catch (InvalidParameterException $e) {
            return [
                'success' => false,
                'error' => 'Invalid options: ' . $e->getMessage(),
                'code' => 'INVALID_OPTIONS',
            ];
        }

        // Execute search
        try {
            $result = $this->client->retrieve($query, $retrievalOptions);

            return [
                'success' => true,
                'query' => $query,
                'count' => $result->count(),
                'chunks' => array_map(
                    fn ($chunk) => [
                        'text' => $chunk->getText(),
                        'score' => $chunk->getScore(),
                        'document' => $chunk->getDocumentName(),
                    ],
                    $result->getChunks()
                ),
            ];
        } catch (\Exception $e) {
            error_log("Search failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => 'Search failed. Please try again.',
                'code' => 'SEARCH_FAILED',
            ];
        }
    }
}

$handler = new SearchHandler($ragie);

// Test with various inputs
$testCases = [
    ['query' => '  valid query  ', 'options' => ['top_k' => 5]],
    ['query' => '', 'options' => []],
    ['query' => 'valid query', 'options' => ['top_k' => 0]],
];

foreach ($testCases as $i => $testCase) {
    echo "Test case " . ($i + 1) . ":\n";
    $result = $handler->search($testCase['query'], $testCase['options']);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
}

echo "=== Done ===\n";
