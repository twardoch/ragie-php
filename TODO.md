---
this_file: TODO.md
---

# TODO

- [x] Mirror the upstream OpenAPI spec locally, extend generator config with Ragie namespaces, and document checksum tracking.
- [x] Update `build_generatapi.sh` and `WORK.md` with the regeneration workflow using the local spec.
- [x] Create root-level `composer.json` with package metadata, PSR-4 autoload configuration, and required dependencies.
- [x] Add repository scaffolding (`.editorconfig`, `.gitattributes`, QA configs, `test.sh`) and document the tooling in `DEPENDENCIES.md`.
- [ ] Regenerate the API client to use the `Ragie\Api` namespace and implement a `ClientFactory` that centralizes configuration.
- [ ] Introduce DTOs plus `DocumentsService` covering all `/documents` endpoints with unit tests.
- [ ] Build `RetrievalsService` and `ResponsesService` with polling helpers and accompanying tests.
- [ ] Implement `ConnectionsService` and authenticator helpers for `/connections` and `/authenticators`, with tests for enable/disable/sync flows.
- [ ] Implement `PartitionsService` and `InstructionsService`, including MCP handling and entity retrieval tests.
- [ ] Assemble the top-level `Ragie\Client` facade with retry policy, logger hooks, and consolidated exception mapping.
- [ ] Configure PHPUnit, PHPStan/Psalm, and PHP-CS-Fixer in Composer scripts and ensure coverage thresholds are enforced.
- [ ] Author runnable examples plus README documentation updates covering installation, quickstart, and advanced topics.
- [ ] Set up CI workflows, release checklist, and Packagist publication steps.
