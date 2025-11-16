# Installation

## Requirements
- PHP 8.2+
- Composer 2.7+
- `ext-json`, `ext-mbstring`, and `ext-curl`
- Ragie API key in `RAGIE_API_KEY`

## Install via Composer
```bash
composer require ragie/ragie-php:^2.0@dev
```

During local development inside this monorepo:
```bash
cd ragie-php
composer install
```

## Configure environment
```bash
export RAGIE_API_KEY=sk_live_...
```

Optional knobs:
- `RAGIE_BASE_URI` – point at a staging Ragie endpoint.
- `RAGIE_TIMEOUT` – override default HTTP timeout (seconds).

## Verify the install
```bash
php -r "require 'vendor/autoload.php'; echo \Ragie\\Client::class, PHP_EOL;"
```

When Composer autoloads without errors you are ready to query Ragie.
