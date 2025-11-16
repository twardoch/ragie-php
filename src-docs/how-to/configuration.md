# Configuration & Environment

## Environment variables
| Variable | Description |
| --- | --- |
| `RAGIE_API_KEY` | Required Ragie token |
| `RAGIE_BASE_URI` | Override API host (defaults to `https://api.ragie.ai`) |
| `RAGIE_TIMEOUT` | HTTP timeout in seconds |
| `RAGIE_CACHE_TTL` | Default TTL when not provided in code |

Use `.env` (via `symfony/dotenv`) or your process manager to load the variables.

## Client options
```php
$client = (new Client($apiKey))
    ->withCache($cache, ttlSeconds: 900)
    ->withDefaultSearchFilters(['collection' => 'policies'])
    ->withDefaultTopK(16)
    ->enableDebug();
```

## Error handling
- Catch `Ragie\Exception\InvalidQueryException` for validation issues.
- Catch `Ragie\Api\ApiException` for HTTP errors (429, 5xx). Inspect `$e->getResponseBody()` for more detail.
- Wrap the call in your orchestrator to translate Ragie errors into user-friendly messages.

## Benchmark knobs
Environment variables consumed by `benchmarks/RetrievalBenchmark.php`:
- `RAGIE_BENCH_RUNS` – number of iterations per query
- `RAGIE_BENCH_FAIL_FAST` – stop on first failure
- `RAGIE_BENCH_OUTPUT` – path to JSON report
