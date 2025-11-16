---
this_file: DEPENDENCIES.md
---

# Dependencies

| Package | Why it is included |
| --- | --- |
| `guzzlehttp/guzzle` | PSR-18 HTTP client used by the generated Ragie API bindings. |
| `psr/http-client` | Interface so consumers can supply their own PSR-18 HTTP client. |
| `psr/log` | Standard logging interfaces consumed by `Client::withLogger()` / `StructuredLogger`. |
| `psr/simple-cache` | PSR-16 cache contract for the optional query cache adapters. |
| `phpunit/phpunit` | Test runner for the unit + integration suites. |
| `phpstan/phpstan` | Static analysis to catch type and logic issues early in CI. |
| `vimeo/psalm` | Complements PHPStan with additional static analysis coverage. |
| `friendsofphp/php-cs-fixer` | Enforces consistent coding style across the repository. |
