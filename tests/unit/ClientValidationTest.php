<?php

// this_file: tests/unit/ClientValidationTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Client;
use Ragie\Exception\InvalidQueryException;

class ClientValidationTest extends TestCase
{
    private Client $client;

    #[\Override]
    protected function setUp(): void
    {
        // Create client with dummy API key (won't make real requests in validation tests)
        $this->client = new Client('test_api_key_for_validation');
    }

    public function test_retrieve_throws_on_empty_query(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot be empty');

        $this->client->retrieve('');
    }

    public function test_retrieve_throws_on_whitespace_only_query(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query cannot consist only of whitespace');

        $this->client->retrieve('   ');
    }

    public function test_retrieve_throws_on_query_exceeding_max_length(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Query length (10001 characters) exceeds maximum allowed length (10000 characters)');

        // Create a query that's exactly 10001 characters
        $longQuery = str_repeat('a', 10001);
        $this->client->retrieve($longQuery);
    }

    public function test_retrieve_accepts_query_at_max_length(): void
    {
        // Create a query that's exactly 10000 characters
        $maxQuery = str_repeat('a', 10000);

        // Should not throw InvalidQueryException (validation passes)
        // Will throw API exception, but that's expected
        try {
            $this->client->retrieve($maxQuery);
            $this->fail('Expected API exception to be thrown');
        } catch (InvalidQueryException $e) {
            $this->fail('Query at max length should pass validation, got: ' . $e->getMessage());
        } catch (\Throwable $e) {
            // Expected - API call fails, but validation passed
            $this->assertNotInstanceOf(InvalidQueryException::class, $e);
        }
    }

    public function test_retrieve_accepts_valid_short_query(): void
    {
        // Valid query should pass validation (will fail at API call)
        try {
            $this->client->retrieve('valid query');
            $this->fail('Expected API exception to be thrown');
        } catch (InvalidQueryException $e) {
            $this->fail('Valid query should pass validation, got: ' . $e->getMessage());
        } catch (\Throwable $e) {
            // Expected - API call fails, but validation passed
            $this->assertNotInstanceOf(InvalidQueryException::class, $e);
        }
    }

    public function test_retrieve_throws_on_control_characters(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('control characters');

        $this->client->retrieve("Hello\x07World");
    }
}
