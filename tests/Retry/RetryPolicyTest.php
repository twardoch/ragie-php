<?php

// this_file: tests/Retry/RetryPolicyTest.php

declare(strict_types=1);

namespace Ragie\Tests\Retry;

use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use Ragie\Api\ApiException;
use Ragie\Retry\RetryPolicy;

class RetryPolicyTest extends TestCase
{
    public function testExecuteSucceedsOnFirstAttempt(): void
    {
        $policy = new RetryPolicy();
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(1, $callCount);
    }

    public function testExecuteRetriesOnConnectException(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 3) {
                throw new ConnectException('Connection failed', new \GuzzleHttp\Psr7\Request('GET', 'test'));
            }
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $callCount);
    }

    public function testExecuteRetriesOn500Error(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 3) {
                throw new ApiException('Server error', 500);
            }
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $callCount);
    }

    public function testExecuteRetriesOn429RateLimit(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 3) {
                throw new ApiException('Rate limit exceeded', 429);
            }
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $callCount);
    }

    public function testExecuteDoesNotRetryOn400Error(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(400);

        try {
            $policy->execute(function () use (&$callCount) {
                $callCount++;
                throw new ApiException('Bad request', 400);
            });
        } finally {
            // Should only be called once (no retry)
            $this->assertEquals(1, $callCount);
        }
    }

    public function testExecuteDoesNotRetryOn401Error(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(401);

        try {
            $policy->execute(function () use (&$callCount) {
                $callCount++;
                throw new ApiException('Unauthorized', 401);
            });
        } finally {
            $this->assertEquals(1, $callCount);
        }
    }

    public function testExecuteThrowsAfterMaxAttempts(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Always fails');

        try {
            $policy->execute(function () use (&$callCount) {
                $callCount++;
                throw new ApiException('Always fails', 500);
            });
        } finally {
            // Should be called exactly maxAttempts times
            $this->assertEquals(3, $callCount);
        }
    }

    public function testExecuteDoesNotRetryOnLogicError(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $this->expectException(\LogicException::class);

        try {
            $policy->execute(function () use (&$callCount) {
                $callCount++;
                throw new \LogicException('Logic error');
            });
        } finally {
            $this->assertEquals(1, $callCount);
        }
    }

    public function testConstructorValidatesMaxAttempts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxAttempts must be at least 1');

        new RetryPolicy(maxAttempts: 0);
    }

    public function testConstructorValidatesInitialDelay(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('initialDelayMs must be non-negative');

        new RetryPolicy(initialDelayMs: -1);
    }

    public function testConstructorValidatesMultiplier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('multiplier must be at least 1.0');

        new RetryPolicy(multiplier: 0.5);
    }

    public function testConstructorValidatesMaxDelay(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxDelayMs must be >= initialDelayMs');

        new RetryPolicy(initialDelayMs: 1000, maxDelayMs: 500);
    }

    public function testGettersReturnConstructorValues(): void
    {
        $policy = new RetryPolicy(
            maxAttempts: 5,
            initialDelayMs: 200,
            multiplier: 3.0,
            maxDelayMs: 10000
        );

        $this->assertEquals(5, $policy->getMaxAttempts());
        $this->assertEquals(200, $policy->getInitialDelayMs());
        $this->assertEquals(3.0, $policy->getMultiplier());
        $this->assertEquals(10000, $policy->getMaxDelayMs());
    }

    public function testDefaultConstructorValues(): void
    {
        $policy = new RetryPolicy();

        $this->assertEquals(3, $policy->getMaxAttempts());
        $this->assertEquals(100, $policy->getInitialDelayMs());
        $this->assertEquals(2.0, $policy->getMultiplier());
        $this->assertEquals(5000, $policy->getMaxDelayMs());
    }

    public function testExecuteRetriesOn503ServiceUnavailable(): void
    {
        $policy = new RetryPolicy(maxAttempts: 2);
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 2) {
                throw new ApiException('Service unavailable', 503);
            }
            return 'recovered';
        });

        $this->assertEquals('recovered', $result);
        $this->assertEquals(2, $callCount);
    }

    public function testExecuteWithSingleAttemptDoesNotRetry(): void
    {
        $policy = new RetryPolicy(maxAttempts: 1);
        $callCount = 0;

        $this->expectException(ApiException::class);

        try {
            $policy->execute(function () use (&$callCount) {
                $callCount++;
                throw new ApiException('Error', 500);
            });
        } finally {
            $this->assertEquals(1, $callCount);
        }
    }

    public function testExecuteRetriesOnCode0NetworkError(): void
    {
        $policy = new RetryPolicy(maxAttempts: 3);
        $callCount = 0;

        $result = $policy->execute(function () use (&$callCount) {
            $callCount++;
            if ($callCount < 3) {
                // Code 0 indicates network error (wrapped ConnectException)
                throw new ApiException('[0] Connection timeout', 0);
            }
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $callCount);
    }
}
