<?php

declare(strict_types=1);

/**
 * this_file: tests/unit/Validation/InputSanitizerTest.php
 */

namespace Ragie\Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Ragie\Exception\InvalidQueryException;
use Ragie\Validation\InputSanitizer;

final class InputSanitizerTest extends TestCase
{
    public function testSanitizeRemovesNullBytesAndTrims(): void
    {
        $dirty = "  \0Hello World\0  ";

        $sanitized = InputSanitizer::sanitize($dirty);

        $this->assertSame('Hello World', $sanitized);
    }

    public function testSanitizeLeavesAllowedWhitespaceInside(): void
    {
        $dirty = " Hello   Ragie \t Rocks ";

        $sanitized = InputSanitizer::sanitize($dirty);

        $this->assertSame("Hello   Ragie \t Rocks", $sanitized);
    }

    public function testSanitizeAndValidateReturnsCleanedString(): void
    {
        $result = InputSanitizer::sanitizeAndValidate("  Ask\nRagie  ");

        $this->assertSame("Ask\nRagie", $result);
    }

    public function testSanitizeAndValidateRejectsControlCharacters(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('control characters');

        InputSanitizer::sanitizeAndValidate("Hello\x07World");
    }

    public function testSanitizeAndValidateRejectsWhitespaceOnlyInput(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('whitespace');

        InputSanitizer::sanitizeAndValidate("    ");
    }

    public function testSanitizeAndValidateRejectsEmptyInput(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('empty');

        InputSanitizer::sanitizeAndValidate('');
    }

    public function testSanitizeAndValidateRespectsMaxLength(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('exceeds');

        InputSanitizer::sanitizeAndValidate(str_repeat('a', 5), 4);
    }
}
