# ragie-php Documentation

`ragie-php` is a modern PHP SDK for the [Ragie](https://ragie.ai/) retrieval API. It ships a generated low-level client plus a convenience layer with caching, retries, and guardrails so you can drop Ragie search directly into CLI tools, workers, or ParaGra-based orchestrators.

- **Language**: PHP 8.2+
- **Focus**: Retrieval only. LLM orchestration lives in `paragra-php`.
- **Status**: Pre-release. We can refactor aggressively because nothing has shipped yet.

## Why this library exists
- Keep Ragie access lightweight and dependency-free.
- Provide ergonomic helpers (batching, caching, debugging) without hiding the underlying API.
- Offer a reproducible workflow: tests + docs + SDK regen all run from a clean checkout.

## What lives where
| Layer | Repository | Responsibilities |
| ----- | ---------- | ---------------- |
| SDK | `ragie-php` | HTTP client, auth, retries, caching, typed models |
| Orchestration | `paragra-php` | Provider pools, fallback plans, embeddings/vector adapters |
| Demo app | `ask.vexy.art` | Reference web app + smoke-test target |

Keep these layers decoupled—`ragie-php` must compile and test without ParaGra present.

## Documentation structure
- [Getting Started](getting-started/installation.md) covers installation and the first query.
- [Architecture](architecture/overview.md) explains the client layout and extension points.
- [Guides](guides/cookbook.md) provide production-ready snippets.
- [How-To](how-to/configuration.md) documents testing, configuration, and debugging.
- [Reference](reference/cli.md) lists utility scripts, docs tooling, and release steps.

Use the left navigation or search (⌘K / Ctrl+K) to jump around.
