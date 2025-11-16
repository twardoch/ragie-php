# Ragie PHP Client: Development Guidelines

This document provides development guidelines for the `ragie-php` project.

## Project Overview

`ragie-php` is a PHP client for the [Ragie AI](https://ragie.ai/) API. It features a dual-layer architecture:

1.  **High-Level Layer (`src/`)**: A manually crafted, opinionated client that provides convenience methods for common RAG operations (e.g., `retrieve`, `retrieveBatch`). This is the primary interface for most users.
2.  **Generated Layer (`src/Ragie/Api/`)**: A low-level, comprehensive API client that is auto-generated from the Ragie `openapi.json` specification. It provides direct access to all API endpoints.

## Development Workflows

### Modifying the Client

-   **High-Level Features**: All new convenience methods and business logic should be added to the high-level layer in the `src/` directory (e.g., `Client.php`, `RetrievalOptions.php`).
-   **Generated Code**: The `src/Ragie/Api/` directory is auto-generated. **DO NOT EDIT FILES IN THIS DIRECTORY MANUALLY.** Any changes will be overwritten.

### Regenerating the API Client

To update the low-level API client after a change in the `openapi.json` specification:

1.  **Fetch the latest spec**: Update `openapi.json` from the Ragie AI API.
2.  **Run the build script**:
    ```bash
    ./build_generatapi.sh
    ```
    This script uses `openapi-generator-cli` to regenerate the files in `src/Ragie/Api/` and updates the `openapi.sha256` checksum.

### Testing and Quality Assurance

The project uses PHPUnit for tests, PHP-CS-Fixer for linting, and PHPStan/Psalm for static analysis.

-   **Run all checks**:
    ```bash
    ./test.sh
    ```
    This is the main script for CI and local validation. It installs dependencies and runs the entire QA suite.

-   **Run individual checks**: The `composer.json` file defines scripts for more granular checks:
    ```bash
    # Run unit tests
    composer test

    # Fix coding standards
    composer lint

    # Run PHPStan static analysis
    composer stan

    # Run Psalm static analysis
    composer psalm

    # Run all QA tools
    composer qa
    ```

### Adding New Features

1.  **Add code**: Implement the feature in the high-level client files within `src/`.
2.  **Add tests**: Create corresponding unit tests in the `tests/` directory.
3.  **Add examples**: Create a runnable example script in the `examples/` directory to demonstrate the new feature.
4.  **Run validation**: Execute `./test.sh` to ensure all checks pass.
5.  **Update documentation**: Update the `README.md` and other relevant documentation.

## Environment Variables

For running tests or examples that interact with the live Ragie API, you must set the following environment variable:

-   `RAGIE_API_KEY`: Your API key for Ragie AI.

## Dependencies

-   PHP 8.1+
-   Composer for package management.
-   `openapi-generator-cli` for regenerating the API client.