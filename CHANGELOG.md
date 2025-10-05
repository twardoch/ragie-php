---
this_file: CHANGELOG.md
---

# Changelog

## 2024-07-05
- Ran /test workflow; skipped because project lacks `composer.json` and associated test suite.
- Verified via /report run that automated tests remain unavailable pending composer scaffolding.
- Configured OpenAPI generator to use local spec with Ragie namespaces, added checksum tracking, and refreshed regeneration workflow.
- Created Composer package scaffolding, QA tooling configs, dependency log, and baseline unit tests (`composer test`).
- Established lint (`composer lint`), static analysis (`composer stan`, `composer psalm`), and full QA automation (`./test.sh`).
