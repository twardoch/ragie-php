---
this_file: DEPENDENCIES.md
---

# Dependencies

| Package | Why it is included |
| --- | --- |
| `guzzlehttp/guzzle` | Provides PSR-compliant HTTP client required by the generated Ragie API bindings. |
| `psr/http-client` | Defines the HTTP client interface so consumers can plug their own PSR-18 implementations. |
| `psr/log` | Supplies standard logging interfaces for future high-level SDK components. |
| `phpunit/phpunit` | Enables unit testing for configuration and future high-level services. |
| `phpstan/phpstan` | Static analysis to catch type and logic issues early in CI. |
| `friendsofphp/php-cs-fixer` | Enforces consistent coding style across the repository. |
| `vimeo/psalm` | Complements PHPStan with additional static analysis coverage and taint checks. |
