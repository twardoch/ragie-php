---
this_file: PLAN.md
---

# Ragie PHP High-Level Client Implementation Plan

## Project Overview

Create a thin, expressive high-level convenience layer in `src/Ragie/` that simplifies common RAG (Retrieval-Augmented Generation) operations. This layer wraps the auto-generated API client in `src/Ragie/Api/` and focuses exclusively on retrieval and response operations—the most frequent RAG inference tasks.

**Scope in one sentence**: Provide dead-simple methods for retrieving relevant document chunks and reading AI responses, while keeping full access to the generated client for administrative tasks.

## Design Principles

1. **Wrap, Don't Duplicate**: Use the generated client internally; never reimplement API calls
2. **80/20 Rule**: Make the most common operations trivial; provide escape hatches for everything else
3. **Minimal Surface Area**: Keep the API small and focused
4. **Modern PHP**: Use PHP 8.1+ features (typed properties, constructor promotion, named arguments)
5. **Testable**: Design for easy unit testing with clear boundaries

## Architecture

### Component Structure

```
src/Ragie/
├── Client.php                  # Main entry point - facade for common operations
├── RetrievalOptions.php        # Value object for retrieval parameters
├── RetrievalResult.php         # Wrapper for retrieval response with convenient accessors
└── Api/                        # Generated client (untouched)
    └── lib/
        ├── Api/
        │   ├── RetrievalsApi.php
        │   ├── ResponsesApi.php
        │   └── ...
        └── Model/
            ├── RetrieveParams.php
            ├── Retrieval.php
            └── ...
```

### Class Responsibilities

#### 1. `Ragie\Client` (Main Entry Point)

**Purpose**: Single entry point for all Ragie operations. Provides simple methods for common RAG tasks.

**Key Methods**:
- `__construct(string $apiKey, ?string $baseUrl = null)` - Initialize with API key
- `retrieve(string $query, ?RetrievalOptions $options = null): RetrievalResult` - Run retrieval
- `getRetrievalsApi(): RetrievalsApi` - Access underlying retrieval API
- `getResponsesApi(): ResponsesApi` - Access underlying responses API
- `getDocumentsApi(): DocumentsApi` - Access documents API for admin tasks
- `getConfiguration(): Configuration` - Access API configuration

**Internal State**:
- Holds configured `Configuration` instance
- Holds lazy-initialized instances of API classes
- HTTP client (Guzzle) instance

**Design Notes**:
- Uses lazy initialization for API instances (only create when accessed)
- Configuration is immutable after construction
- All API instances share the same configuration and HTTP client
- Named constructor pattern for alternative initialization strategies

**Example Usage**:
```php
<?php
use Ragie\Client;

// Simple initialization
$ragie = new Client('your-api-key');

// Simple retrieval
$result = $ragie->retrieve('What is RAG?');
foreach ($result->getChunks() as $chunk) {
    echo $chunk->getText() . "\n";
}

// With options
$result = $ragie->retrieve('query',
    RetrievalOptions::create()
        ->withTopK(10)
        ->withRerank(true)
        ->withFilter(['category' => 'docs'])
);

// Access underlying API for advanced use
$documents = $ragie->getDocumentsApi()->listDocuments();
```

#### 2. `Ragie\RetrievalOptions` (Value Object)

**Purpose**: Fluent builder for retrieval parameters. Simplifies constructing `RetrieveParams`.

**Properties**:
- `?int $topK` - Number of results to return (default: 10)
- `?array $filter` - Metadata filter
- `?bool $rerank` - Whether to rerank results (default: false)
- `?int $maxChunksPerDocument` - Max chunks per document
- `?string $partition` - Partition to search within
- `?bool $recencyBias` - Apply recency bias (default: false)

**Key Methods**:
- `static create(): self` - Create new instance
- `withTopK(int $topK): self` - Set top K
- `withFilter(array $filter): self` - Set filter
- `withRerank(bool $rerank = true): self` - Enable reranking
- `withMaxChunksPerDocument(int $max): self` - Limit chunks per doc
- `withPartition(string $partition): self` - Set partition
- `withRecencyBias(bool $bias = true): self` - Enable recency bias
- `toRetrieveParams(string $query): RetrieveParams` - Convert to API model

**Design Notes**:
- Immutable (each `with*` method returns new instance)
- Fluent interface for easy chaining
- Provides sensible defaults
- Validates parameters

**Example Usage**:
```php
<?php
use Ragie\RetrievalOptions;

$options = RetrievalOptions::create()
    ->withTopK(20)
    ->withRerank()
    ->withFilter(['department' => ['$in' => ['sales', 'marketing']]])
    ->withMaxChunksPerDocument(3);

$result = $ragie->retrieve('sales strategy', $options);
```

#### 3. `Ragie\RetrievalResult` (Response Wrapper)

**Purpose**: Wraps `Ragie\Api\Model\Retrieval` and provides convenient accessors with better names.

**Properties**:
- `private Retrieval $retrieval` - The underlying API response

**Key Methods**:
- `__construct(Retrieval $retrieval)` - Wrap API response
- `getChunks(): array` - Get all scored chunks (returns `ScoredChunk[]`)
- `getTopChunk(): ?ScoredChunk` - Get highest scoring chunk
- `getChunkTexts(): array` - Get array of just the text from all chunks
- `getDocumentIds(): array` - Get unique document IDs from results
- `isEmpty(): bool` - Check if no results
- `count(): int` - Get number of chunks
- `getUnderlyingRetrieval(): Retrieval` - Access raw API response

**Design Notes**:
- Thin wrapper, doesn't transform data heavily
- Provides convenience methods for common access patterns
- Always allows access to underlying model
- Implements `Countable` interface for natural `count($result)`

**Example Usage**:
```php
<?php
$result = $ragie->retrieve('question');

// Convenient accessors
if ($result->isEmpty()) {
    echo "No results found\n";
    return;
}

echo "Found {$result->count()} chunks\n";

// Get just the text
$texts = $result->getChunkTexts();

// Get best result
$topChunk = $result->getTopChunk();
echo "Best match: {$topChunk->getText()}\n";
echo "Score: {$topChunk->getScore()}\n";

// Access full chunk objects
foreach ($result->getChunks() as $chunk) {
    echo "{$chunk->getDocumentName()}: {$chunk->getText()}\n";
}

// Access underlying model if needed
$raw = $result->getUnderlyingRetrieval();
```

## Implementation Phases

### Phase 1: Core Client and Configuration
**Goal**: Get basic client initialization working with proper configuration.

**Tasks**:
1. Create `src/Ragie/Client.php` with constructor and configuration setup
2. Implement lazy initialization for API instances
3. Add getter methods for underlying API classes
4. Write unit tests for initialization and configuration
5. Test API key validation and error handling

**Files Created**:
- `src/Ragie/Client.php`
- `tests/ClientTest.php`

**Test Cases**:
- Client initializes with API key
- Configuration is properly set
- API instances are lazy-loaded
- Invalid API key throws appropriate exception
- Base URL override works correctly

### Phase 2: RetrievalOptions Value Object
**Goal**: Create fluent builder for retrieval parameters.

**Tasks**:
1. Create `src/Ragie/RetrievalOptions.php` with all properties
2. Implement fluent `with*` methods
3. Implement `toRetrieveParams()` conversion method
4. Add parameter validation
5. Write comprehensive unit tests

**Files Created**:
- `src/Ragie/RetrievalOptions.php`
- `tests/RetrievalOptionsTest.php`

**Test Cases**:
- Default values are correct
- Each `with*` method returns new instance (immutability)
- Chaining works correctly
- Conversion to `RetrieveParams` is accurate
- Invalid parameters throw exceptions
- Edge cases (null values, empty arrays, etc.)

### Phase 3: RetrievalResult Wrapper
**Goal**: Create convenient wrapper for retrieval responses.

**Tasks**:
1. Create `src/Ragie/RetrievalResult.php`
2. Implement all accessor methods
3. Implement `Countable` interface
4. Write unit tests with mock `Retrieval` objects

**Files Created**:
- `src/Ragie/RetrievalResult.php`
- `tests/RetrievalResultTest.php`

**Test Cases**:
- Empty results handled correctly
- `getChunks()` returns correct array
- `getTopChunk()` returns highest scorer
- `getChunkTexts()` extracts text properly
- `getDocumentIds()` returns unique IDs
- `Countable` interface works
- Access to underlying model works

### Phase 4: Client Retrieval Method
**Goal**: Implement the main `retrieve()` method on `Client`.

**Tasks**:
1. Implement `Client::retrieve()` method
2. Handle options parameter (null and provided cases)
3. Call underlying `RetrievalsApi::retrieve()`
4. Wrap response in `RetrievalResult`
5. Add error handling and exceptions
6. Write integration-style tests (can mock API)

**Files Modified**:
- `src/Ragie/Client.php`
- `tests/ClientTest.php`

**Test Cases**:
- Basic retrieval works (no options)
- Retrieval with options works
- Options are properly converted to `RetrieveParams`
- API exceptions are propagated appropriately
- Empty results return empty `RetrievalResult`

### Phase 5: Documentation and Examples
**Goal**: Comprehensive documentation and working examples.

**Tasks**:
1. Add PHPDoc to all classes and methods
2. Create `examples/retrieve.php` - basic retrieval example
3. Create `examples/retrieve_advanced.php` - with filters and options
4. Update `README.md` with usage examples
5. Add inline code examples in docblocks

**Files Created/Modified**:
- `examples/retrieve.php`
- `examples/retrieve_advanced.php`
- `README.md` (updated)
- All source files (docblock updates)

### Phase 6: Polish and Testing
**Goal**: Final polish, full test coverage, QA checks.

**Tasks**:
1. Run full test suite (`./test.sh`)
2. Ensure 100% code coverage for new classes
3. Run static analysis (PHPStan, Psalm)
4. Run code formatter (PHP-CS-Fixer)
5. Manual testing with real API (if key available)
6. Update `DEPENDENCIES.md` if needed
7. Update `WORK.md` with test results

**QA Checklist**:
- [ ] All unit tests pass
- [ ] Code coverage ≥ 80% for new code
- [ ] PHPStan level 9 passes
- [ ] Psalm passes with no errors
- [ ] PHP-CS-Fixer passes
- [ ] Examples run without errors
- [ ] Documentation is complete and accurate

## API Design Decisions

### Why No Response Generation in V1?

The Responses API (`ResponsesApi::createResponseResponsesPost()`) is more complex and less commonly used for basic RAG. It involves:
- Creating requests with specific instructions
- Polling for completion (async nature)
- Parsing complex response structures
- Handling streaming responses

This adds significant complexity. For V1, we focus on the core retrieval flow. Response generation can be added in V2 if needed, or users can access it via `$client->getResponsesApi()`.

### Why Separate Options Object?

1. **Cleaner Method Signatures**: `retrieve(string $query, ?RetrievalOptions $options)` is cleaner than 7 optional parameters
2. **Discoverable**: IDE autocomplete shows all options via fluent methods
3. **Testable**: Easy to test different option combinations
4. **Extensible**: New options can be added without breaking existing code
5. **Reusable**: Same options object can be used for multiple retrievals

### Why Wrapper Result Object?

1. **Better Names**: `getChunks()` vs `getScoredChunks()`
2. **Convenience**: `isEmpty()`, `getTopChunk()`, `getChunkTexts()`
3. **Future-Proof**: Can add caching, lazy loading, or other features without breaking API
4. **Type Safety**: Explicit return types, implements interfaces
5. **Escape Hatch**: Always allows access to underlying model

### Error Handling Strategy

- Don't catch exceptions from generated client - let them bubble up
- Users can catch `Ragie\Api\ApiException` at their level
- Validate input parameters early (before API call) to provide better error messages
- Document expected exceptions in method docblocks

## Testing Strategy

### Unit Tests
- Mock generated API classes
- Test each class in isolation
- Test edge cases and error conditions
- Aim for 100% coverage on new code

### Integration Tests (Optional)
- Requires real API key
- Can be run separately from unit tests
- Tests actual API calls and responses
- Put in `tests/Integration/` directory

### Functional Tests
- Working examples in `examples/` directory
- Should run successfully with real API key
- Serve as both tests and documentation
- Can be executed via `composer functional-test` script

## Documentation Structure

### README.md Updates

Add a "High-Level Client" section:

```markdown
## High-Level Client

For common RAG operations, use the high-level client:

```php
<?php
use Ragie\Client;

$ragie = new Client('your-api-key');

// Retrieve relevant chunks
$result = $ragie->retrieve('What is machine learning?');

// Access results
foreach ($result->getChunks() as $chunk) {
    echo $chunk->getText() . "\n";
}
```

### Advanced Options

```php
<?php
use Ragie\Client;
use Ragie\RetrievalOptions;

$ragie = new Client('your-api-key');

$options = RetrievalOptions::create()
    ->withTopK(20)
    ->withRerank()
    ->withFilter(['category' => 'documentation']);

$result = $ragie->retrieve('query', $options);
```

### Low-Level API Access

For document management and other operations:

```php
<?php
// Create document
$document = $ragie->getDocumentsApi()->createDocument($params);

// List documents
$documents = $ragie->getDocumentsApi()->listDocuments();
```
```

### Code Comments

Each class should have:
1. Class-level docblock explaining purpose
2. Usage example in docblock
3. Method docblocks with `@param`, `@return`, `@throws`
4. Inline comments for complex logic only

## Future Enhancements (Not V1)

These are explicitly OUT OF SCOPE for V1 but documented for future consideration:

1. **Response Generation Helper**: Simple wrapper around `ResponsesApi`
   ```php
   $response = $ragie->ask('What is RAG?', $retrievalOptions);
   ```

2. **Streaming Support**: Handle streaming responses elegantly
   ```php
   $ragie->retrieveStream('query', function($chunk) {
       echo $chunk->getText();
   });
   ```

3. **Async Support**: PHP 8.1+ Fiber support or Guzzle promises
   ```php
   $promise = $ragie->retrieveAsync('query');
   ```

4. **Result Caching**: Optional in-memory cache for repeated queries
   ```php
   $ragie = new Client('key', cache: true);
   ```

5. **Batch Operations**: Retrieve for multiple queries at once
   ```php
   $results = $ragie->retrieveBatch(['query1', 'query2']);
   ```

6. **Pagination Helpers**: For document listing, etc.
   ```php
   foreach ($ragie->documents()->all() as $doc) { }
   ```

## Risk Assessment

### Low Risk
- **Pure Wrapper**: No custom HTTP logic, just wraps generated client
- **Small Surface Area**: Three simple classes, minimal functionality
- **No State Changes**: Read-only operations, no mutations
- **Type Safety**: Full PHP 8.1 type hints

### Medium Risk
- **API Changes**: If Ragie API changes, need to regenerate and possibly update wrappers
- **Breaking Changes**: Future versions might need breaking changes if we got API wrong

### Mitigation
- Semantic versioning strictly followed
- Generated client can always be used directly as escape hatch
- Comprehensive tests catch regressions
- Examples serve as additional regression tests

## Success Criteria

1. **Simplicity**: Common retrieval is <5 lines of code
2. **Completeness**: All retrieval parameters are accessible
3. **Testability**: 100% code coverage on new classes
4. **Documentation**: README examples, inline docs, working examples
5. **Quality**: Passes all QA checks (PHPStan, Psalm, CS-Fixer)
6. **Usability**: Positive feedback from manual testing
7. **Maintainability**: Clear code, well-structured, easy to extend

## Definition of Done

For each phase:
- [ ] Code written and follows PHP standards
- [ ] Unit tests written and passing
- [ ] Code coverage ≥ 80%
- [ ] PHPDoc complete with examples
- [ ] Static analysis passes
- [ ] Code formatter passes
- [ ] Manual testing completed
- [ ] WORK.md updated with results
- [ ] No regressions in existing tests

For entire project:
- [ ] All phases complete
- [ ] Examples work with real API
- [ ] README updated
- [ ] CHANGELOG updated
- [ ] All QA checks pass
- [ ] Ready for release
