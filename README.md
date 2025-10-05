# Ragie PHP Client

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A modern PHP client for the [Ragie AI](https://ragie.ai/) API, providing both a high-level convenience interface and full access to the underlying generated API client.

## Features

- **🚀 Simple High-Level API** - Dead-simple methods for common RAG operations
- **🔧 Full API Access** - Complete access to all Ragie API endpoints via generated client
- **💪 Type-Safe** - Full PHP 8.1+ type hints and strict typing
- **🧪 Well-Tested** - Comprehensive test suite with 51 unit tests
- **📚 Fully Documented** - Inline PHPDoc with code examples
- **🎯 Fluent Interface** - Chainable methods for clean, readable code

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [High-Level Client](#high-level-client)
- [Advanced Usage](#advanced-usage)
- [Low-Level API Access](#low-level-api-access)
- [Examples](#examples)
- [Testing](#testing)
- [Documentation](#documentation)

## Installation

Install via Composer:

```bash
composer require ragie/ragie-php
```

**Requirements:** PHP 8.1 or higher

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use Ragie\Client;

// Initialize the client
$ragie = new Client('your-api-key');

// Perform a simple retrieval
$result = $ragie->retrieve('What is machine learning?');

// Access results
foreach ($result->getChunks() as $chunk) {
    echo $chunk->getText() . "\n";
}
```

## High-Level Client

The high-level client provides simplified methods for the most common RAG operations:

### Initialization

```php
use Ragie\Client;

// With default base URL (https://api.ragie.ai)
$ragie = new Client('your-api-key');

// With custom base URL
$ragie = new Client('your-api-key', 'https://custom.api.url');

// With custom HTTP client
$ragie = new Client('your-api-key', null, new GuzzleHttp\Client([
    'timeout' => 30,
]));
```

### Basic Retrieval

```php
// Simple retrieval with defaults
$result = $ragie->retrieve('What is RAG?');

// Check if results were found
if ($result->isEmpty()) {
    echo "No results found\n";
    exit;
}

// Get result count
echo "Found {$result->count()} chunks\n";

// Get the top result
$topChunk = $result->getTopChunk();
echo "Best match: {$topChunk->getText()}\n";
echo "Score: {$topChunk->getScore()}\n";
```

### Working with Results

The `RetrievalResult` class provides convenient methods for accessing retrieval data:

```php
$result = $ragie->retrieve('query');

// Get all chunks
$chunks = $result->getChunks();

// Get just the text from all chunks
$texts = $result->getChunkTexts();

// Get unique document IDs
$documentIds = $result->getDocumentIds();

// Check if empty
if ($result->isEmpty()) {
    // Handle no results
}

// Use Countable interface
echo count($result) . " results found\n";

// Access underlying API response if needed
$rawRetrieval = $result->getUnderlyingRetrieval();
```

## Advanced Usage

### Using RetrievalOptions

Customize retrieval behavior with the fluent `RetrievalOptions` builder:

```php
use Ragie\RetrievalOptions;

$options = RetrievalOptions::create()
    ->withTopK(20)                    // Get top 20 results
    ->withRerank()                    // Enable reranking
    ->withMaxChunksPerDocument(3)     // Max 3 chunks per document
    ->withRecencyBias()               // Prefer recent documents
    ->withPartition('customer-123')   // Scope to partition
    ->withFilter([                    // Apply metadata filter
        'category' => 'documentation',
        'priority' => ['$gt' => 5]
    ]);

$result = $ragie->retrieve('sales strategy', $options);
```

### Metadata Filters

RetrievalOptions supports powerful metadata filtering:

```php
// Simple equality
$options = RetrievalOptions::create()
    ->withFilter(['category' => 'docs']);

// In array operator
$options = RetrievalOptions::create()
    ->withFilter([
        'department' => ['$in' => ['sales', 'marketing']]
    ]);

// Greater than operator
$options = RetrievalOptions::create()
    ->withFilter([
        'priority' => ['$gt' => 5]
    ]);

// Complex filters
$options = RetrievalOptions::create()
    ->withFilter([
        'status' => 'published',
        'category' => ['$in' => ['blog', 'article']],
        'views' => ['$gte' => 1000]
    ]);
```

### Inline Options

For quick customization, options can be built inline:

```php
$result = $ragie->retrieve(
    'machine learning best practices',
    RetrievalOptions::create()
        ->withTopK(15)
        ->withRerank(true)
        ->withFilter(['type' => 'guide'])
);
```

## Low-Level API Access

For document management and other operations, access the underlying generated API:

### Document Management

```php
// Create a document from file
$document = $ragie->getDocumentsApi()->createDocument([
    'file' => new \SplFileObject('/path/to/file.pdf'),
    'metadata' => ['category' => 'manual']
]);

// Create from URL
$document = $ragie->getDocumentsApi()->createDocumentFromUrl([
    'url' => 'https://example.com/document.pdf',
    'name' => 'Example Document'
]);

// Create from raw text
$document = $ragie->getDocumentsApi()->createDocumentRaw([
    'text' => 'This is the document content...',
    'name' => 'Text Document'
]);

// List documents
$documents = $ragie->getDocumentsApi()->listDocuments();

// Delete a document
$ragie->getDocumentsApi()->deleteDocument($documentId);
```

### Other API Operations

```php
// Access Retrievals API (same as high-level retrieve() but lower level)
$retrieval = $ragie->getRetrievalsApi()->retrieve($retrieveParams);

// Access Responses API (for AI response generation)
$response = $ragie->getResponsesApi()->createResponseResponsesPost($request);

// Access Configuration
$config = $ragie->getConfiguration();
```

## Examples

The `examples/` directory contains working examples:

### Basic Retrieval ([examples/retrieve.php](examples/retrieve.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve.php
```

Demonstrates:
- Simple retrieval
- Accessing top result
- Iterating through all chunks
- Extracting text and document IDs

### Advanced Retrieval ([examples/retrieve_advanced.php](examples/retrieve_advanced.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve_advanced.php
```

Demonstrates:
- Reranking
- Metadata filters
- Complex filter operators
- Partition scoping
- Fluent method chaining
- All retrieval options combined

## Testing

Run the test suite:

```bash
# Run all tests
./test.sh

# Run specific test suites
vendor/bin/phpunit tests/ClientTest.php
vendor/bin/phpunit tests/RetrievalOptionsTest.php
vendor/bin/phpunit tests/RetrievalResultTest.php

# Run with detailed output
vendor/bin/phpunit --testdox
```

Quality assurance:

```bash
# Code style check
composer lint

# Static analysis (PHPStan level 9)
composer stan

# Psalm static analysis
composer psalm

# Run all QA checks
composer qa
```

## Documentation

### API Documentation

- [High-Level Client Classes](src/)
  - `Ragie\Client` - Main entry point
  - `Ragie\RetrievalOptions` - Fluent options builder
  - `Ragie\RetrievalResult` - Result wrapper

- [Generated API Documentation](src/Ragie/Api/docs/)
  - Full OpenAPI-generated documentation
  - All available endpoints and models

### Additional Resources

- [Ragie AI Documentation](https://docs.ragie.ai/)
- [API Reference](https://docs.ragie.ai/api-reference)
- [Metadata Filters Guide](https://docs.ragie.ai/docs/metadata-filters)

## Architecture

The client has two layers:

1. **High-Level Layer** (`src/`)
   - Simple, opinionated API for common tasks
   - Focuses on retrieval operations (most common RAG use case)
   - Fluent interfaces and convenience methods

2. **Generated Layer** (`src/Ragie/Api/`)
   - Complete, low-level access to all API endpoints
   - Auto-generated from OpenAPI specification
   - Never manually edited (regenerated when API updates)

This design gives you:
- **Simplicity** for common operations
- **Power** for advanced use cases
- **Future-proof** as API evolves

## Error Handling

```php
use Ragie\Client;

try {
    $ragie = new Client('your-api-key');
    $result = $ragie->retrieve('query');

    // Work with results...

} catch (\InvalidArgumentException $e) {
    // Invalid input (empty query, invalid options, etc.)
    echo "Invalid input: {$e->getMessage()}\n";

} catch (\Ragie\Api\ApiException $e) {
    // API error (network, auth, rate limit, etc.)
    echo "API error: {$e->getMessage()}\n";
    echo "HTTP code: {$e->getCode()}\n";

} catch (\Exception $e) {
    // Other errors
    echo "Error: {$e->getMessage()}\n";
}
```

## Contributing

This is an auto-generated client with a manually crafted high-level layer.

- **High-Level Code** (`src/*.php`): Contributions welcome
- **Generated Code** (`src/Ragie/Api/`): Do not edit - regenerated from OpenAPI spec

## License

This project is licensed under the MIT License.

## Support

- **Issues**: [GitHub Issues](https://github.com/ragie/ragie-php/issues)
- **Documentation**: [Ragie Docs](https://docs.ragie.ai/)
- **API Support**: support@ragie.ai

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.
