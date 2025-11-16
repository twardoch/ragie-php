# Ragie PHP Client

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A modern PHP client for the [Ragie AI](https://ragie.ai/) API, providing both a high-level convenience interface and full access to the underlying generated API client.

> 📚 **Documentation** – Markdown sources live in `src-docs/` and the built site ships from `docs/`. Run `uvx zensical build --clean` (or `uvx zensical serve`) to preview the latest docs locally.

## Features

- **🚀 Simple High-Level API** – Dead-simple methods for common retrieval tasks
- **📦 Batch Processing** – Process multiple queries efficiently with `retrieveBatch()`
- **⚡ Async Retrieval** – Fire off concurrent lookups with `retrieveAsync()` / `retrieveConcurrent()`
- **⚙️ Fluent Configuration** – Set defaults once with `withDefaultTopK()` and `withDefaultRerank()`
- **💾 Query Caching** – Drop in any PSR-16 cache via `withCache()` to reduce duplicate calls
- **📊 Retrieval Telemetry** – Built-in metrics + cost trackers quantify throughput and spend
- **📝 Structured Logging** – Pipe retrieval events into your PSR-3 logger for observability
- **🔧 Full API Access** – Direct access to every Ragie REST endpoint through the generated client
- **💪 Type-Safe** – Full PHP 8.1+ type hints and strict typing
- **🧪 Well-Tested** – Comprehensive PHPUnit suite covering retrieval, caching, and retries
- **📚 Fully Documented** – Inline PHPDoc with code examples
- **🎯 Fluent Interface** – Chainable methods for clean, readable code
- **Need LLM orchestration?** – Pair this library with [`vexy/paragra-php`](../paragra-php) for moderation, OpenAI/AskYoda fallbacks, and multi-provider RAG flows

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [High-Level Client](#high-level-client)
- [Advanced Usage](#advanced-usage)
- [Low-Level API Access](#low-level-api-access)
- [Work with Orchestrators (e.g., ParaGra)](#work-with-orchestrators-eg-paragra)
- [Upgrading to v0.4.0](#upgrading-to-v040)
- [Examples](#examples)
- [Testing](#testing)
- [Documentation](#documentation)
- [Architecture](#architecture)
- [Error Handling](#error-handling)
- [Environment Variables Reference](#environment-variables-reference)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

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

### Fluent Configuration

Set default retrieval parameters once and apply them to all subsequent calls:

```php
// Configure defaults
$ragie = (new Client('your-api-key'))
    ->withDefaultTopK(20)
    ->withDefaultRerank(true);

// All subsequent calls use these defaults
$result1 = $ragie->retrieve('What is RAG?');  // Uses top_k=20, rerank=true
$result2 = $ragie->retrieve('Explain vectors');  // Uses top_k=20, rerank=true

// Explicit options override defaults
$result3 = $ragie->retrieve(
    'Special query',
    RetrievalOptions::create()->withTopK(50)  // Overrides default of 20
);
```

### Optional Query Cache

Avoid duplicate API calls for identical queries by pairing the client with a cache:

```php
use Ragie\Cache\InMemoryCache;
use Ragie\Cache\SimpleCacheQueryCache;

$ragie = (new Client('your-api-key'))
    ->withDefaultTopK(20)
    ->withCache(new InMemoryCache(), ttlSeconds: 300);

$first = $ragie->retrieve('Explain retrievers');
$second = $ragie->retrieve('Explain retrievers'); // Served from cache
```

Already have Redis, Memcached, or Symfony cache pools? Bridge any PSR-16 cache through the adapter:

```php
use Psr\SimpleCache\CacheInterface;
use Ragie\Cache\SimpleCacheQueryCache;

/** @var CacheInterface $redisCache */
$redisCache = $container->get(CacheInterface::class);

$ragie = (new Client('api-key'))
    ->withCache(new SimpleCacheQueryCache($redisCache), ttlSeconds: 600);
```

Each entry is automatically scoped by query, retrieval options, and base URL so different settings never collide.

### Batch Retrieval

Process multiple queries efficiently:

```php
$queries = [
    'What is machine learning?',
    'Explain neural networks',
    'How does gradient descent work?'
];

// Process all queries
$results = $ragie->retrieveBatch($queries);

// Access results in same order as queries
foreach ($results as $i => $result) {
    echo "Query: {$queries[$i]}\n";
    echo "Top result: {$result->getTopChunk()->getText()}\n\n";
}

// Apply options to all queries in batch
$options = RetrievalOptions::create()
    ->withTopK(10)
    ->withRerank();

$results = $ragie->retrieveBatch($queries, $options);

// Batch also respects fluent defaults
$ragie->withDefaultTopK(15)->withDefaultRerank();
$results = $ragie->retrieveBatch($queries);  // All use top_k=15, rerank=true
```

### Asynchronous Retrieval

Need to overlap outbound requests or keep your worker free while Ragie searches? Every retrieval helper now has a promise-based counterpart.

- `retrieveAsync()` sanitizes, applies defaults, and returns a `PromiseInterface` that resolves to `RetrievalResult`
- `retrieveConcurrent()` fans out queries via Guzzle promises and unwraps them in original order
- Metrics, caching, cost tracking, and structured logging behave exactly like the synchronous path

```php
use Ragie\Client;

$client = (new Client('your-api-key'))
    ->withDefaultTopK(12);

// Kick off a single query and wait later
$promise = $client->retrieveAsync('Explain embeddings vs dense vector search');
$result = $promise->wait();

// Fan out three lookups concurrently
$concurrent = $client->retrieveConcurrent([
    'What is retrieval augmented generation?',
    'How does reranking help?',
    'Give me a refresher on chunking strategies',
]);

foreach ($concurrent as $i => $retrieval) {
    $topChunk = $retrieval->getTopChunk();
    echo "Query {$i}: " . ($topChunk?->getText() ?? 'No results') . PHP_EOL;
}
```

### Structured Logging (PSR-3)

Need structured telemetry without building another logging stack? `Client::withLogger()` accepts any PSR-3 logger and `Ragie\Logging\StructuredLogger` emits stable events for retrieval start/success/failure so you can centralise observability.

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ragie\Client;
use Ragie\Logging\StructuredLogger;

$logger = new Logger('ragie');
$logger->pushHandler(new StreamHandler('php://stdout'));

$client = (new Client($_ENV['RAGIE_API_KEY']))
    ->withLogger(new StructuredLogger($logger));

$client->retrieve('Explain the latest release notes');
```

Event names stay stable, which makes downstream filtering trivial:

- `ragie.retrieve.start`
- `ragie.retrieve.success`
- `ragie.retrieve.failure`

Call `Client::getStructuredLogger()` when you need to re-use the configured logger elsewhere (for example, to pipe the same instance into ParaGra or another orchestrator).

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

## Work with Orchestrators (e.g., ParaGra)

Version 0.5 cements **ragie-php** as a Ragie-only SDK: retrieval, caching, retries, and instrumentation. LLM sessions, moderation guards, AskYoda fallbacks, and config factories now belong to orchestration layers such as [`vexy/paragra-php`](../paragra-php).

Those orchestrators sit on top of ragie-php and typically provide:

- Shared prompt builders plus wrappers for OpenAI/Cerebras/Gemini/AskYoda.
- Pool-based fallback + key rotation so free/paid tiers rotate predictably.
- Optional moderation middleware before Ragie retrieval happens.
- Configuration builders (`fromEnv()`, `fromConfig()`) that wire Ragie + LLM secrets at once.

If you previously used `RagAnswerer::fromEnv()` or the AskYoda fallback scripts, add your orchestrator alongside ragie-php:

```bash
composer require ragie/ragie-php vexy/paragra-php
```

```php
use ParaGra\ParaGra;

$config = require __DIR__ . '/paragra.php'; // Pools + API keys, built via ParaGra tools
$paragra = ParaGra::fromConfig($config);

$answer = $paragra->answer('Summarise the changelog', [
    'retrieval' => ['top_k' => 6],
]);

echo $answer['answer'];
```

ask.vexy.art already consumes ParaGra, so you can copy its configuration approach when you are ready to adopt a richer multi-provider stack. ragie-php deliberately remains a single-provider SDK.

## Upgrading After the Namespace Cleanup

`ragie-php` v0.5 removes the temporary proxy layer that used to emit “install ParaGra” warnings. Any references to the old namespaces below now result in a regular PHP “class not found” error until you install a third-party orchestrator:

- `Ragie\Assistant\{RagAnswerer,RagAnswer}`
- `Ragie\Llm\{OpenAiChatClient,OpenAiChatConfig,PromptBuilder,AskYodaClient,AskYodaResponse,ChatResponse,ChatRequestOptions,ChatUsage}`
- `Ragie\Moderation\{OpenAiModerator,ModerationException,ModerationResult}`
- `Ragie\ConfigValidator` and `Ragie\ExceptionEnhancer`

To upgrade older projects:

1. Install ParaGra next to ragie-php: `composer require vexy/paragra-php`.
2. Update imports from the removed Ragie namespaces to their ParaGra equivalents.
3. Remove moderation/chat/config helpers from your codebase unless they live inside ParaGra (or another orchestrator).
4. Run your PHPUnit/CI suite to confirm no files still reference the removed namespaces before bumping ragie-php.

## Examples

Each example is a runnable PHP script inside `examples/` so you can see the client in action with minimal setup.

### Basic Retrieval ([examples/retrieve.php](examples/retrieve.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve.php
```
Covers the core `Client::retrieve()` call, iterating chunks, and inspecting document metadata.

### Advanced Retrieval ([examples/retrieve_advanced.php](examples/retrieve_advanced.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve_advanced.php
```
Shows reranking toggles, metadata filters, partitions, and fluent option composition.

### Input Validation ([examples/input_validation.php](examples/input_validation.php))
```bash
RAGIE_API_KEY=your-key php examples/input_validation.php
```
Demonstrates how `InputSanitizer::sanitizeAndValidate()` guards against empty or overlong queries, plus how to handle validation errors in web controllers.

### Error Handling ([examples/error_handling.php](examples/error_handling.php))
```bash
RAGIE_API_KEY=your-key php examples/error_handling.php
```
Walks through structured try/catch blocks, HTTP status inspection, exponential backoff retries, and production logging without relying on the old ExceptionEnhancer helper.

### Interactive CLI ([examples/interactive-demo.php](examples/interactive-demo.php))
```bash
RAGIE_API_KEY=your-key php examples/interactive-demo.php
```
Launches a readline-powered CLI that streams retrieval timing, chunk previews, and lets you tweak chunk limits via `RAGIE_INTERACTIVE_MAX_*` env vars.

### Cached Retrieval ([examples/retrieve_with_cache.php](examples/retrieve_with_cache.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve_with_cache.php "Summarize Ragie caching"
```
Shows how to pass the bundled `InMemoryCache` into `Client::withCache()` so identical questions resolve instantly from memory. Adjust `RAGIE_CACHE_TTL` or change the question text to observe cache invalidation.

### Concurrent Retrieval ([examples/retrieve_concurrent.php](examples/retrieve_concurrent.php))
```bash
RAGIE_API_KEY=your-key php examples/retrieve_concurrent.php
```
Runs the same set of queries via `retrieveBatch()` and `retrieveConcurrent()` while timing both approaches, highlighting when to reach for promises to fan out Ragie API calls.

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

# Generate HTML/text coverage reports
composer coverage

# Enforce minimum coverage (80% line coverage)
composer coverage-check
```

### Benchmarking Retrieval Latency

Measure end-to-end retrieval timings against your live Ragie workspace using the optional benchmark script:

```bash
cd ragie-php
RAGIE_API_KEY=sk-xxx php benchmarks/RetrievalBenchmark.php
```

Available knobs (all optional, set as environment variables):

| Variable | Default | Description |
| --- | --- | --- |
| `RAGIE_BENCH_ITERATIONS` | `3` | Number of retrievals per query |
| `RAGIE_BENCH_QUERIES` | `What is Ragie AI? \| ...` | Pipe-separated list of questions to run |
| `RAGIE_BENCH_DELAY` | `0` | Seconds to wait between iterations (helps dodge rate limits) |

Each query logs individual runs plus a `TimingSummary` (avg, min, max, p95, success ratio, chunk count). The script exits with a non-zero code if every iteration for a query fails so CI can catch upstream outages.

## Documentation

### Cookbook

- [docs/COOKBOOK.md](docs/COOKBOOK.md) — Pragmatic recipes for one-liner setups, caching, fallback orchestration, error handling, and batch retrieval patterns.

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

## Environment Variables Reference

### Core Configuration

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `RAGIE_API_KEY` | Yes | - | Your Ragie API key from https://ragie.ai/ |

Need OpenAI/AskYoda/Gemini credentials, moderation toggles, or fallback knobs? Those belong to your orchestrator (for example, [`../paragra-php/README.md`](../paragra-php/README.md) covers ParaGra’s env matrix).

### Example .env File

```bash
RAGIE_API_KEY=your_ragie_api_key_here
```

## Troubleshooting

### "API key is required"

**Cause:** Missing or invalid `RAGIE_API_KEY` environment variable.

**Solution:**
```php
// Check if environment variable is set
if (!getenv('RAGIE_API_KEY')) {
    die("Error: RAGIE_API_KEY environment variable not set\n");
}

$ragie = new Client(getenv('RAGIE_API_KEY'));
```

### "HTTP 401 Unauthorized"

**Cause:** Invalid API key or incorrect authentication.

**Solutions:**
1. Verify your API key is correct
2. Check for extra whitespace in the key
3. Ensure the key hasn't expired
4. Generate a new key from https://ragie.ai/

```php
try {
    $ragie = new Client(trim(getenv('RAGIE_API_KEY')));
    $result = $ragie->retrieve('test');
} catch (\Ragie\Api\ApiException $e) {
    if ($e->getCode() === 401) {
        echo "Authentication failed. Please check your API key.\n";
    }
}
```

### "HTTP 429 Rate Limit Exceeded"

**Cause:** Too many requests to Ragie API.

**Solutions:**

1. **Delegate fallback to an orchestrator (e.g., ParaGra)** – ParaGra’s `PriorityPool` automatically rotates across free-tier and paid providers (Ragie, Cerebras, AskYoda, etc.). See [Work with Orchestrators (e.g., ParaGra)](#work-with-orchestrators-eg-paragra) for installation details.

2. **Implement retry logic with exponential backoff**:
```php
function retrieveWithRetry($ragie, $query, $maxRetries = 3) {
    $attempt = 0;
    $delay = 1; // Start with 1 second

    while ($attempt < $maxRetries) {
        try {
            return $ragie->retrieve($query);
        } catch (\Ragie\Api\ApiException $e) {
            if ($e->getCode() === 429 && $attempt < $maxRetries - 1) {
                echo "Rate limited. Retrying in {$delay}s...\n";
                sleep($delay);
                $delay *= 2; // Exponential backoff
                $attempt++;
            } else {
                throw $e;
            }
        }
    }
}
```

3. **Add delay between requests**:
```php
$queries = ['query1', 'query2', 'query3'];
foreach ($queries as $query) {
    $result = $ragie->retrieve($query);
    sleep(1); // 1 second delay between requests
}
```

### "Connection timeout" or "Network errors"

**Cause:** Network connectivity issues or slow API responses.

**Solutions:**

1. **Increase timeout**:
```php
use GuzzleHttp\Client as GuzzleClient;

$httpClient = new GuzzleClient([
    'timeout' => 60,          // 60 seconds request timeout
    'connect_timeout' => 10,  // 10 seconds connection timeout
]);

$ragie = new Client('your-api-key', null, $httpClient);
```

2. **Enable connection keep-alive** (already configured by default):
```php
// The client automatically uses keep-alive for better performance
// Connections are reused for up to 300 seconds
```

### "Empty results" or "No chunks found"

**Cause:** Query doesn't match any documents or filters are too restrictive.

**Solutions:**

1. **Check if documents exist**:
```php
$documents = $ragie->getDocumentsApi()->listDocuments();
if (empty($documents)) {
    echo "No documents found. Please upload documents first.\n";
}
```

2. **Broaden your search**:
```php
// Instead of:
$result = $ragie->retrieve('very specific technical term',
    RetrievalOptions::create()->withTopK(3)
);

// Try:
$result = $ragie->retrieve('general topic',
    RetrievalOptions::create()->withTopK(20)
);
```

3. **Remove filters**:
```php
// If using filters, try without them first
$result = $ragie->retrieve('your query');  // No filters
```

4. **Check document content**:
```php
$result = $ragie->retrieve('test query');
if ($result->isEmpty()) {
    echo "No results. This might mean:\n";
    echo "- No documents uploaded\n";
    echo "- Query doesn't match document content\n";
    echo "- Filters are too restrictive\n";
}
```

### "Content flagged by moderation"

**Cause:** Your own moderation guard rejected the query.

**Solution:** ragie-php no longer bundles OpenAI moderation wrappers. Either:

1. Use your orchestrator’s moderation middleware (ParaGra ships `ParaGra\Moderation\OpenAiModerator`, for example) before calling its `answer()` helper, or
2. Call the OpenAI Moderation API (or another provider) directly in your application. If the response is flagged, short-circuit before handing the query to Ragie; otherwise call `$client->retrieve($query)` as usual.

### "Memory exhausted" with batch operations

**Cause:** Processing too many results at once.

**Solution:**

1. **Process in smaller batches**:
```php
$allQueries = [...]; // Large array
$batchSize = 10;

foreach (array_chunk($allQueries, $batchSize) as $batch) {
    $results = $ragie->retrieveBatch($batch);
    // Process results
    unset($results); // Free memory
}
```

2. **Use generators for large result sets**:
```php
function processQueries($ragie, $queries) {
    foreach ($queries as $query) {
        yield $ragie->retrieve($query);
    }
}

foreach (processQueries($ragie, $queries) as $result) {
    // Process one result at a time
}
```

### Performance is slow

**Solutions:**

1. **Use batch retrieval** instead of individual calls:
```php
// Slow:
foreach ($queries as $query) {
    $result = $ragie->retrieve($query);
}

// Faster:
$results = $ragie->retrieveBatch($queries);
```

2. **Enable connection pooling** (enabled by default):
```php
// Already configured - connections are reused automatically
```

3. **Use fluent defaults** to avoid rebuilding options:
```php
// Configure once
$ragie->withDefaultTopK(15)->withDefaultRerank(true);

// All subsequent calls reuse these settings
$result1 = $ragie->retrieve('query1');  // Fast
$result2 = $ragie->retrieve('query2');  // Fast
```

4. **Reduce top_k if you don't need many results**:
```php
$result = $ragie->retrieve('query',
    RetrievalOptions::create()->withTopK(5)  // Instead of default 10
);
```

## Contributing

This is an auto-generated client with a manually crafted high-level layer.

- **High-Level Code** (`src/*.php`): Contributions welcome
- **Generated Code** (`src/Ragie/Api/`): Do not edit - regenerated from OpenAPI spec

### Regenerating the OpenAPI client

Keep the generated layer current whenever Ragie ships a new spec:

1. **Download the latest spec** into the repo root (never point the generator at the remote URL directly):
   ```bash
   curl -sS https://api.ragie.ai/openapi.json -o openapi.json
   ```
2. **Run the generator** to refresh `src/Ragie/Api`, `.regen-stamp`, and the checksum:
   ```bash
   ./build_generatapi.sh
   ```
3. **Run the verification tests** (they confirm `openapi.json`, `openapi.sha256`, and `.regen-stamp` stay in sync):
   ```bash
   ./test.sh -- tests/unit/OpenApiArtifactsTest.php
   # or just run ./test.sh for the full suite
   ```
4. **Commit the artifacts**: include `openapi.json`, `openapi.sha256`, `src/Ragie/Api/`, and `src/Ragie/Api/.regen-stamp`.

`tests/unit/OpenApiArtifactsTest.php` enforces these steps, so CI will fail if the spec, checksum, or stamp drift apart.

## License

This project is licensed under the MIT License.

## Support

- **Issues**: [GitHub Issues](https://github.com/ragie/ragie-php/issues)
- **Documentation**: [Ragie Docs](https://docs.ragie.ai/)
- **API Support**: support@ragie.ai

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.
