# Cookbook

Short recipes for common Ragie tasks. These snippets assume you already set `RAGIE_API_KEY`.

## Minimal CLI search
```php
use Ragie\Client;

require __DIR__ . '/../vendor/autoload.php';

$ragie = new Client(getenv('RAGIE_API_KEY'));
$query = trim($argv[1] ?? 'What is Ragie?');

$result = $ragie->retrieve($query);

if ($result->isEmpty()) {
    echo "No context found.\n";
    exit(0);
}

foreach ($result->getChunks() as $i => $chunk) {
    printf("%02d. %s (%s)\n", $i + 1, $chunk->getText(), $chunk->getDocumentName());
}
```

## Cache expensive queries
```php
use Ragie\Cache\InMemoryCache;
use Ragie\Client;

$client = (new Client($apiKey))
    ->withCache(new InMemoryCache(), ttlSeconds: 600)
    ->withDefaultTopK(12);

$result = $client->retrieve('How do I reset MFA tokens?');
```

Replace `InMemoryCache` with `SimpleCacheQueryCache` + Redis/Memcached to stretch rate limits and speed up popular FAQs.

## Custom error handling
```php
use Ragie\Client;
use Ragie\Exception\InvalidQueryException;

$client = new Client($apiKey);

try {
    $client->retrieve($userQuery);
} catch (InvalidQueryException $invalid) {
    logError('Invalid query', ['message' => $invalid->getMessage()]);
    throw $invalid;
} catch (\Ragie\Api\ApiException $api) {
    logError('Ragie API error', [
        'code' => $api->getCode(),
        'body' => $api->getResponseBody(),
    ]);
    throw new \RuntimeException('Temporary RAG outage, try again soon.');
}
```

## Batch + concurrency
```php
use Ragie\Client;

$client = (new Client($apiKey))
    ->withDefaultTopK(8)
    ->withDefaultRerank();

$results = $client->retrieveBatch([
    'What is Vexy?',
    'Show me onboarding docs.',
    'How do I clear caches?',
]);

foreach ($results as $i => $result) {
    printf("#%d returned %d chunks\n", $i + 1, count($result));
}
```

`retrieveBatch()` executes sequentially, but you can upgrade to `retrieveAsync()` / `retrieveConcurrent()` later without touching business logic.

## Production checklist
- **Moderation / LLM flows:** Keep them in ParaGra or your orchestrator; ragie-php only retrieves.
- **Benchmarking:** `php benchmarks/RetrievalBenchmark.php` records min/max/p95 latency per query once `RAGIE_BENCH_*` variables are set.
- **Debug logging:** `(new Client($apiKey))->enableDebug()` writes payloads and chunk counts to `error_log` for staging rollouts.
