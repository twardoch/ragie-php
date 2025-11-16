<?php

declare(strict_types=1);

/**
 * this_file: src/Validation/InputSanitizer.php
 */

namespace Ragie\Validation;

use Ragie\Exception\InvalidQueryException;

final class InputSanitizer
{
    private const DEFAULT_MAX_LENGTH = 10000;

    /**
     * Remove null bytes and surrounding whitespace while keeping meaningful whitespace intact.
     */
    public static function sanitize(string $query): string
    {
        $clean = str_replace("\0", '', $query);
        $clean = trim($clean);

        if (function_exists('normalizer_normalize') && class_exists(\Normalizer::class)) {
            $normalized = normalizer_normalize($clean, \Normalizer::FORM_C);
            if (is_string($normalized)) {
                $clean = $normalized;
            }
        }

        return $clean;
    }

    /**
     * Sanitize and validate a query, throwing if it remains invalid.
     */
    public static function sanitizeAndValidate(string $query, int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $sanitized = self::sanitize($query);

        if ($sanitized === '') {
            if ($query === '') {
                throw InvalidQueryException::empty();
            }

            throw InvalidQueryException::whitespace();
        }

        if (self::hasControlCharacters($sanitized)) {
            throw InvalidQueryException::controlCharacters();
        }

        if ($maxLength > 0 && mb_strlen($sanitized) > $maxLength) {
            throw InvalidQueryException::tooLong(mb_strlen($sanitized), $maxLength);
        }

        return $sanitized;
    }

    /**
     * Determine whether the string contains disallowed control characters.
     */
    public static function hasControlCharacters(string $query): bool
    {
        return preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', $query) === 1;
    }
}
