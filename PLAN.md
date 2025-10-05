---
this_file: PLAN.md
---

# Ragie PHP Client Delivery Plan

**Scope**: Ship a Composer-installable PHP SDK that wraps the Ragie API for document ingestion, retrieval, and workspace management using the generated client as the transport layer.

## Current State Snapshot
- Local `openapi.json` (v1.0.0) matches production endpoints such as `/documents`, `/retrievals`, `/responses`, `/connections`, `/partitions`, `/instructions`, and `/authenticators`.
- `src/Ragie/Api` contains OpenAPI Generator output under the `OpenAPI\\Client` namespace; no root-level `composer.json`, tests, or distribution scaffolding exist yet.
- Repository docs (`README.md`, `TODO.md`) reference a future high-level client but there is no structured plan, automation, or packaging metadata.

## Guiding Principles
- Keep the OpenAPI-generated surface authoritative; never hand-edit files under `src/Ragie/Api` and rely solely on regeneration to refresh them.
- Provide a thin but expressive high-level layer focused on core RAG flows (ingest documents, manage partitions/instructions, run retrievals, read responses).
- Enforce test-first development, lean dependencies, PSR-4 autoloading, and `this_file` markers across the codebase.
- Maintain a reproducible release workflow so the package can be tagged and published to Packagist with confidence.

## Phase 0 – Baseline & OpenAPI Synchronization
- Freeze the upstream spec by mirroring `https://api.ragie.ai/openapi.json` into the repo (already present) and add checksum monitoring to detect upstream changes.
- Extend `openapitools.json` (or generator CLI flags) with `invokerPackage=Ragie\\Api`, `apiPackage=Api`, `modelPackage=Model`, and `artifactVersion` so regeneration produces the desired namespace and semantic version metadata.
- Update `build_generatapi.sh` to read from the local spec, document required tooling (Java, npm proxy) in `README.md`, and ensure the script drops a `.regen-stamp` for traceability.
- Document the regeneration process and expectations in `WORK.md`, including how to diff generated outputs safely.

## Phase 1 – Repository Scaffolding & Composer Packaging
- Create a root `composer.json` (`name: ragie/ragie-php`, MIT license, keywords, `homepage`, `support`, `type: library`, minimum PHP ≥8.1) with PSR-4 autoload sections for both `Ragie\\` (high-level code) and `Ragie\\Api\\` (generated output folder).
- Declare runtime dependencies (`guzzlehttp/guzzle`, `psr/http-client`, `psr/log`) and dev dependencies (`phpunit/phpunit`, `phpstan/phpstan`, `friendsofphp/php-cs-fixer`, `vimeo/psalm` or equivalent static analysis) while documenting rationale in `DEPENDENCIES.md`.
- Add Composer scripts for `lint`, `test`, and `qa`, plus a `test.sh` convenience wrapper that runs CS fixer (dry-run), static analysis, and PHPUnit.
- Introduce project hygiene files: `.editorconfig`, enriched `.gitignore`, `.gitattributes` (export-ignore docs/tests), `phpcs.xml` or fixer config, and a `LICENSE` reference in Composer metadata.
- Restructure source tree to reserve `src/Ragie/HighLevel/...` (or similar) for new classes while keeping generated code isolated under `src/Ragie/Api/lib`.

## Phase 2 – Low-Level Client Hardening
- Regenerate the API client with the new namespace and ensure Composer autoloaders resolve `Ragie\\Api\\Api\*` classes without the intermediate `OpenAPI\\Client` aliasing.
- Create a `Ragie\Api\ClientFactory` that centralizes configuration (API key, base URI overrides, Guzzle client injection, user-agent string) and exposes typed accessors for each generated API group.
- Add unit tests for the factory covering API key injection, custom HTTP client usage, and error handling when credentials are missing.
- Mirror API enums/constants (e.g., partition limits, response statuses) into PHP enums or constant classes only where referenced by the high-level SDK to avoid drift.

## Phase 3 – High-Level SDK Architecture
- Define DTOs (immutable value objects) for key payloads: `Document`, `DocumentChunk`, `Partition`, `Instruction`, `RetrievalResult`, `ResponseResult`, `Authenticator`, and `ConnectionStats`, each with factory methods from generated model instances.
- Implement domain-specific services grouped by API area:
  - `DocumentsService`: create from raw/text/url (`POST /documents`, `/documents/raw`, `/documents/url`), update content or metadata (`PUT`/`PATCH` endpoints), fetch chunks/entities/content/summary, delete documents.
  - `RetrievalsService`: wrap `POST /retrievals` to return ranked chunks with scores, optionally letting callers pass filters (partition IDs, instruction IDs).
  - `ResponsesService`: orchestrate `POST /responses` (ask) and `GET /responses/{response_id}` for polling, plus convenience wait/poll helpers with timeout safety.
  - `ConnectionsService`: manage external data source connections (`/connections`, `/connections/{id}/enabled|sync|stats|limit`, `/connections/source-type`) and authenticator handshakes.
  - `PartitionsService`: create/list/delete partitions and manage MCP integrations and limits.
  - `InstructionsService`: CRUD instructions and surface related entities.
- Provide a thin `Ragie\Client` facade that wires the services, exposes ergonomics (automatic retries with backoff limited to ≤3 attempts, optional logger injection), and validates configuration eagerly.
- Establish consistent exception hierarchy under `Ragie\Exception\` (base `RagieException`, plus specialized `AuthenticationException`, `DocumentException`, `RetrievalException`, etc.) translating `ApiException` status codes into domain errors.
- Ensure every public method accepts simple scalar/array inputs, handles nullable optional parameters, and returns DTOs without leaking generated model classes.

## Phase 4 – Quality Gates & Testing Strategy
- Configure PHPUnit with a `tests/unit` suite targeting service classes (use PHPUnit mocks or `guzzlehttp/psr7` `MockHandler` to stub API responses) and a `tests/integration` suite guarded by an environment flag for smoke tests against a mock server.
- Add contract tests that assert DTO factories correctly map from representative OpenAPI models, using fixture payloads derived from `openapi.json` examples.
- Introduce static analysis via PHPStan/Psalm at level 6+, enforce coding standards with PHP-CS-Fixer, and wire both into Composer `qa` plus CI.
- Measure coverage with `phpunit --coverage-xml`; gate minimum coverage (e.g., 80%) in CI and record results in `WORK.md`.
- Validate error handling by simulating HTTP 4xx/5xx responses, timeouts, and malformed payloads, ensuring the high-level client reports actionable messages.

## Phase 5 – Documentation & Developer Experience
- Rewrite `README.md` to include installation via Composer, authentication setup, quickstart for ingest→retrieve→respond, and advanced sections for partitions, connections, and instructions.
- Produce `docs/` markdown pages (or expand README subsections) with API surface area tables referencing corresponding Ragie endpoints for traceability.
- Create runnable examples under `examples/` (e.g., `basic_ingest.php`, `ask_question.php`, `manage_connections.php`) that double as smoke tests via `test.sh`.
- Maintain `WORK.md`, `CHANGELOG.md`, and `TODO.md` updates as part of each change set; automate changelog entry generation through a Composer script or release tooling.

## Phase 6 – Packaging, CI/CD, and Release Management
- Configure GitHub Actions (or alternative CI) to run on `pull_request` and `main` pushes, executing Composer install, `composer qa`, and caching dependencies; publish coverage artifacts.
- Add a release workflow that, on tagged commits, builds the package archive, validates `composer.json`, and publishes to Packagist (manual trigger until tokens available).
- Define semantic versioning policy (start at `0.x` until API stabilises), document release checklist (update changelog, bump version, create tag) in `README.md` or `RELEASE.md`.
- Add `.gitattributes` `export-ignore` entries to exclude CI config, examples, and docs from distribution archives, and verify with `composer archive`.
- Periodically regenerate the API client when Ragie publishes spec updates; log the spec version and regeneration date in `CHANGELOG.md`.

## Phase 7 – Future Enhancements & Monitoring
- Explore optional caching layers or persistent storage once primary SDK is stable (defer per anti-bloat guidance).
- Investigate providing Laravel/Symfony integration packages only after core SDK adoption justifies the maintenance cost.
- Monitor Ragie API changelog for breaking changes, and add compatibility assertions or deprecation notices in the SDK when endpoints evolve.

## Success Criteria
- Package installs cleanly via `composer require ragie/ragie-php` and auto-loads all namespaces.
- High-level services cover every endpoint present in `openapi.json` with typed DTOs and tests.
- CI pipeline remains green, with ≥80% coverage and static analysis warnings treated as failures.
- Documentation and examples enable a new developer to ingest a document and retrieve an answer within five minutes.
