<?php

declare(strict_types=1);

/**
 * this_file: tests/Http/ResponseFormatterTest.php
 */

namespace Ragie\Tests\Http;

use PHPUnit\Framework\TestCase;
use Ragie\Http\ResponseFormatter;

// Define constant to prevent exit() in tests
if (!defined('PHPUNIT_RUNNING')) {
    define('PHPUNIT_RUNNING', true);
}

final class ResponseFormatterTest extends TestCase
{
    public function testSuccessJsonResponse(): void
    {
        $output = ResponseFormatter::success(['result' => 'test data'], 'json');
        $decoded = json_decode($output, true);

        $this->assertTrue($decoded['success']);
        $this->assertEquals(['result' => 'test data'], $decoded['data']);
    }

    public function testSuccessTextResponse(): void
    {
        $output = ResponseFormatter::success('Plain text result', 'text');

        $this->assertEquals('Plain text result', $output);
    }

    public function testSuccessWithArrayInTextMode(): void
    {
        $output = ResponseFormatter::success(['key' => 'value'], 'text');

        $this->assertStringContainsString('[key]', $output);
        $this->assertStringContainsString('value', $output);
    }

    public function testErrorJsonResponse(): void
    {
        $output = ResponseFormatter::error('Test error message', 404, 'json');
        $decoded = json_decode($output, true);

        $this->assertFalse($decoded['success']);
        $this->assertEquals('Test error message', $decoded['error']);
        $this->assertArrayNotHasKey('details', $decoded);
    }

    public function testErrorJsonWithDetails(): void
    {
        $output = ResponseFormatter::error(
            'Validation failed',
            400,
            'json',
            ['field' => 'email', 'reason' => 'invalid format']
        );
        $decoded = json_decode($output, true);

        $this->assertFalse($decoded['success']);
        $this->assertEquals('Validation failed', $decoded['error']);
        $this->assertArrayHasKey('details', $decoded);
        $this->assertEquals('email', $decoded['details']['field']);
        $this->assertEquals('invalid format', $decoded['details']['reason']);
    }

    public function testErrorTextResponse(): void
    {
        $output = ResponseFormatter::error('Simple error', 500, 'text');

        $this->assertStringStartsWith('Error: Simple error', $output);
        $this->assertStringNotContainsString('Details:', $output);
    }

    public function testErrorTextWithDetails(): void
    {
        $output = ResponseFormatter::error(
            'Database error',
            500,
            'text',
            ['query' => 'SELECT *', 'code' => 1045]
        );

        $this->assertStringContainsString('Error: Database error', $output);
        $this->assertStringContainsString('Details:', $output);
        $this->assertStringContainsString('query', $output);
        $this->assertStringContainsString('SELECT *', $output);
    }

    public function testSuccessDefaultFormat(): void
    {
        $output = ResponseFormatter::success(['test' => 'data']);

        $this->assertJson($output);
    }
}
