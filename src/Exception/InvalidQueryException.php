<?php

// this_file: src/Exception/InvalidQueryException.php

declare(strict_types=1);

namespace Ragie\Exception;

use InvalidArgumentException;

/**
 * Thrown when an invalid query is provided to the retrieve() method.
 *
 * Invalid queries include:
 * - Empty strings
 * - Whitespace-only strings
 * - Null values (caught by type system)
 *
 * @example
 * ```php
 * try {
 *   final   $client->retrieve('');  // Empty query
 * } catch (InvalidQueryException $e) {
 *     echo "Invalid query: " . $e->getMessage();
 * }
 * ```
 */
class InvalidQueryException extends InvalidArgumentException implements RagieException
{
    /**
     * Create exception for empty query.
     */
    public static function empty(): self
    {
        return new self('Query cannot be empty');
    }

    /**
     * Create exception for whitespace-only query.
     */
    public static function whitespace(): self
    {
        return new self('Query cannot consist only of whitespace');
    }

    /**
     * Create exception for query that exceeds maximum length.
     *
     * @param int $length Actual query length
     * @param int $maxLength Maximum allowed length
     */
    public static function tooLong(int $length, int $maxLength): self
    {
        return new self(sprintf(
            'Query length (%d characters) exceeds maximum allowed length (%d characters)',
            $length,
            $maxLength
        ));
    }

    /**
     * Create exception for disallowed control characters.
     */
    public static function controlCharacters(): self
    {
        return new self('Query contains invalid control characters');
    }
}
