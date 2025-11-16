# Architecture Overview

```
User code → Ragie\Client → Generated API client → Ragie REST endpoints
```

## Layers
1. **Convenience layer (`src/Client.php`, `src/RagAnswerer/` etc.)**
   - Input validation
   - Retry/caching helpers
   - Debug logging
2. **Generated API client (`src/Ragie/Api/*`)**
   - Output of `build_generatapi.sh`
   - Mirrors the official Ragie OpenAPI schema
3. **Supporting utilities**
   - `cache/` implementations (in-memory + PSR SimpleCache bridge)
   - `benchmarks/` harness for latency testing

## Key extension points
- `withCache(CacheInterface $cache, int $ttlSeconds)` – plug in Redis/Memcached.
- `withHttpClient(ClientInterface $client)` – supply a custom PSR-18 client.
- `withDefaultTopK(int $k)` / `withDefaultRerank()` – set defaults for every call.

## Generated code hygiene
- Never edit `src/Ragie/Api/*` by hand. Run `build_generatapi.sh` after updating `openapi.json`.
- Commit both the schema (`openapi.json`) and checksum (`openapi.sha256`).
- CI checks hash mismatches to prevent stale clients.

## Upcoming changes
We will remove legacy orchestration helpers, cut a clean 2.0.0 release, and let ParaGra pick up the multi-provider responsibilities.
