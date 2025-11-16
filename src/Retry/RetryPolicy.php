<?php

// this_file: src/Retry/RetryPolicy.php

declare(strict_types=1);

namespace Ragie\Retry;

use GuzzleHttp\Exception\ConnectException;
use Ragie\Api\ApiException;

/**
 * Retry policy with exponential backoff for transient failures
 */
class RetryPolicy
{
    public function __construct(
        private int $maxAttempts = 3,
        private int $initialDelayMs = 100,
        private float $multiplier = 2.0,
        private int $maxDelayMs = 5000,
    ) {
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('maxAttempts must be at least 1');
        }
        if ($initialDelayMs < 0) {
            throw new \InvalidArgumentException('initialDelayMs must be non-negative');
        }
        if ($multiplier < 1.0) {
            throw new \InvalidArgumentException('multiplier must be at least 1.0');
        }
        if ($maxDelayMs < $initialDelayMs) {
            throw new \InvalidArgumentException('maxDelayMs must be >= initialDelayMs');
        }
    }

    /**
     * Execute operation with retry logic
     *
     * @template T
     * @param callable(): T $operation
     * @throws \Throwable
     * @return T
     */
    public function execute(callable $operation): mixed
    {
        $attempt = 0;
        $delay = $this->initialDelayMs;
        /** @var \Throwable|null $lastException */
        $lastException = null;

        while ($attempt < $this->maxAttempts) {
            try {
                return $operation();
            } catch (\Throwable $e) {
                $lastException = $e;
                $attempt++;

                // Don't retry if we've exhausted attempts
                if ($attempt >= $this->maxAttempts) {
                    break;
                }

                // Don't retry if the error is not retryable
                if (!$this->isRetryable($e)) {
                    break;
                }

                // Wait before retrying
                usleep($delay * 1000);

                // Exponential backoff
                $delayFloat = (float) $delay * $this->multiplier;
                $delay = (int) min($delayFloat, (float) $this->maxDelayMs);
            }
        }

        // All retries exhausted or non-retryable error
        if ($lastException === null) {
            throw new \LogicException('No exception was thrown during execution');
        }

        throw $lastException;
    }

    /**
     * Determine if an exception is retryable
     *
     * Retryable errors:
     * - Network connection errors (ConnectException or ApiException with code 0)
     * - 5xx server errors
     * - 429 rate limit errors
     *
     * Non-retryable errors:
     * - 4xx client errors (except 429)
     * - Invalid arguments
     * - Logic errors
     */
    private function isRetryable(\Throwable $e): bool
    {
        // Network/connection errors are retryable
        if ($e instanceof ConnectException) {
            return true;
        }

        // API exceptions - check status code
        if ($e instanceof ApiException) {
            $code = $e->getCode();

            // Code 0 typically indicates network/connection error (wrapped ConnectException)
            if ($code === 0) {
                return true;
            }

            // 5xx server errors are retryable
            if ($code >= 500 && $code < 600) {
                return true;
            }

            // 429 rate limit is retryable
            if ($code === 429) {
                return true;
            }

            // Other 4xx errors are not retryable (client errors)
            return false;
        }

        // Other exceptions are not retryable by default
        return false;
    }

    /**
     * Get maximum number of attempts
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * Get initial delay in milliseconds
     */
    public function getInitialDelayMs(): int
    {
        return $this->initialDelayMs;
    }

    /**
     * Get backoff multiplier
     */
    public function getMultiplier(): float
    {
        return $this->multiplier;
    }

    /**
     * Get maximum delay in milliseconds
     */
    public function getMaxDelayMs(): int
    {
        return $this->maxDelayMs;
    }
}
