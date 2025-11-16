# Testing & QA

## Local QA script
```bash
./test.sh
```
This runs Composer scripts for linting, PHPStan, Psalm, and PHPUnit.

## Focused PHPUnit runs
```bash
vendor/bin/phpunit --testsuite unit
vendor/bin/phpunit tests/Integration/RetrieveTest.php
```

## Static analysis
```bash
composer lint
composer stan
composer psalm
```

## OpenAPI regeneration gate
Whenever `openapi.json` changes:
1. Run `./build_generatapi.sh`.
2. Commit regenerated files plus checksum updates.
3. Ensure `tests/OpenApi/OpenApiArtifactsTest.php` passes.

## Docs build check
```bash
uvx zensical build --clean
```
Commit the resulting `docs/` directory so GitHub Pages stays in sync.
