# Reference Commands

## Composer scripts
| Script | Description |
| ------ | ----------- |
| `composer lint` | PHP-CS fixer + coding standard checks |
| `composer stan` | PHPStan static analysis |
| `composer psalm` | Psalm analysis |
| `composer test` | PHPUnit suites |
| `composer qa` | Aggregate lint + analysis + PHPUnit |

## Helper scripts
| Script | Description |
| ------ | ----------- |
| `./test.sh` | Runs the full QA chain |
| `./build_generatapi.sh` | Regenerates the Ragie API client |
| `php benchmarks/RetrievalBenchmark.php` | Latency + throughput measurements |
| `php tools/**` | Misc helpers (see inline docs) |

## Docs tooling
- `uvx zensical serve` – hot-reload docs preview (serves `src-docs/`).
- `uvx zensical build --clean` – render docs into `docs/`.

## Continuous integration
- `ci-docs` workflow builds/publishes `docs/`.
- QA workflows must pass before merging and before tagging releases.

## Releases
1. Ensure `CHANGELOG.md` covers the change set.
2. Run `./test.sh` and `uvx zensical build --clean`.
3. Tag with semantic version (e.g., `v2.0.0-beta.1`).
4. Push tag and create GitHub release notes (include Ragie API compatibility info).
