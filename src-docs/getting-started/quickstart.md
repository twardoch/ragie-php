# Quickstart

## Minimal query
```php
<?php

use Ragie\Client;

require __DIR__ . '/vendor/autoload.php';

$ragie = new Client(getenv('RAGIE_API_KEY'));
$result = $ragie->retrieve('What is Ragie?');

foreach ($result->getChunks() as $chunk) {
    printf("- %s\n", $chunk->getText());
}
```

## Batch retrieval
```php
$client = (new Client($apiKey))
    ->withDefaultTopK(8)
    ->withDefaultRerank();

$results = $client->retrieveBatch([
    'Show onboarding steps',
    'Reset MFA tokens',
]);
```

## Cache popular queries
```php
use Ragie\Cache\InMemoryCache;

$client = (new Client($apiKey))
    ->withCache(new InMemoryCache(), ttlSeconds: 300);
```

Swap in `SimpleCacheQueryCache` with Redis or Memcached when you need persistence.

## Debug requests
```php
$client = (new Client($apiKey))->enableDebug();
```

Debug mode logs payloads, headers, and chunk counts to the PHP error log to help diagnose staging issues.
