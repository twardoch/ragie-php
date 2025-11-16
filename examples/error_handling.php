<?php

declare(strict_types=1);

/**
 * this_file: examples/error_handling.php
 *
 * Demonstrates resilient error handling around the Ragie\Client without relying
 * on higher-level helpers that now live in paragra-php.
 */

use Ragie\Api\ApiException;
use Ragie\Client;
use Ragie\Exception\InvalidQueryException;

require_once __DIR__ . '/../vendor/autoload.php';

$apiKey = getenv('RAGIE_API_KEY') ?: 'invalid-key-for-demo';
$client = new Client($apiKey);

echo "=== Example 1: Basic try/catch ===\n";
try {
    $result = $client->retrieve('What is RAG?');
    echo "Retrieved {$result->count()} chunks\n\n";
} catch (InvalidQueryException $invalid) {
    echo "Query rejected: {$invalid->getMessage()}\n\n";
} catch (ApiException $api) {
    echo formatApiError($api) . "\n\n";
}

echo "=== Example 2: Status-aware responses ===\n";
try {
    $client->retrieve('Trigger rate limiting?');
} catch (ApiException $api) {
    $status = $api->getCode();
    $message = match ($status) {
        401, 403 => 'Authentication failed — check API key.',
        404 => 'Resource not found.',
        429 => 'Too many requests — slow down.',
        500, 502, 503 => 'Server hiccup — try again shortly.',
        default => 'Unexpected Ragie error.',
    };
    echo "{$message} ({$status})\n";
    error_log(formatApiError($api));
}
echo "\n";

echo "=== Example 3: Simple retry with backoff ===\n";
$result = retryRetrieve($client, 'machine learning', maxAttempts: 3);
echo $result === null ? "All attempts failed.\n\n" : "Retry succeeded.\n\n";

echo "=== Example 4: Service wrapper with logging ===\n";
$service = new RetrievalService($client);
$maybeResult = $service->search('docs about caching', ['user_id' => '123']);
if ($maybeResult === null) {
    echo "Returned fallback content to caller.\n";
}
echo "\n=== Done ===\n";

function formatApiError(ApiException $exception): string
{
    $status = $exception->getCode();
    $details = $exception->getResponseBody();
    $extra = '';

    if (is_object($details) && isset($details->detail)) {
        $extra = (string) $details->detail;
    } elseif (is_array($details) && isset($details['detail'])) {
        $extra = (string) $details['detail'];
    }

    return sprintf(
        'Ragie API error (%d): %s%s',
        $status,
        $exception->getMessage(),
        $extra !== '' ? " — {$extra}" : ''
    );
}

function retryRetrieve(Client $client, string $query, int $maxAttempts = 3): ?object
{
    $attempt = 0;
    $delaySeconds = 1;

    while ($attempt < $maxAttempts) {
        $attempt++;
        try {
            echo "Attempt {$attempt}... ";
            $result = $client->retrieve($query);
            echo "success\n";
            return $result;
        } catch (ApiException $api) {
            $status = $api->getCode();
            echo "failed ({$status})\n";

            if ($status >= 400 && $status < 500 && $status !== 429) {
                echo "Client error, not retrying.\n";
                return null;
            }

            if ($attempt < $maxAttempts) {
                echo "Sleeping {$delaySeconds}s before retry...\n";
                sleep($delaySeconds);
                $delaySeconds *= 2;
            }
        }
    }

    return null;
}

final class RetrievalService
{
    public function __construct(private readonly Client $client)
    {
    }

    public function search(string $query, array $context = []): ?object
    {
        try {
            return $this->client->retrieve($query);
        } catch (InvalidQueryException $invalid) {
            $this->logError('invalid_query', $invalid, $context);
            throw $invalid;
        } catch (ApiException $api) {
            $this->logError('ragie_api_error', $api, $context);
            return null;
        }
    }

    private function logError(string $type, \Throwable $exception, array $context): void
    {
        $payload = [
            'type' => $type,
            'error' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'context' => $context,
            'timestamp' => date(DATE_ATOM),
        ];

        error_log(json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
