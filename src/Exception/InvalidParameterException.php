<?php

// this_file: src/Exception/InvalidParameterException.php

declare(strict_types=1);

namespace Ragie\Exception;

use InvalidArgumentException;

/**
 * Thrown when invalid parameters are provided to RetrievalOptions.
 *
 * This exception is thrown for various parameter validation failures:
 * - Invalid topK values (must be positive)
 * - Invalid maxChunksPerDocument values (must be positive)
 * - Empty partition strings
 * - Whitespace-only partition strings
 *
 * @example
 * ```php
 * try {
 *     Retrifinal evalOptions::create()->withTopK(-1);
 * } catch (InvalidParameterException $e) {
 *     echo "Invalid parameter: " . $e->getMessage();
 * }
 * ```
 */
class InvalidParameterException extends InvalidArgumentException implements RagieException
{
    /**
     * Create exception for invalid topK value.
     */
    public static function invalidTopK(int $value): self
    {
        return new self("topK must be a positive integer, got {$value}");
    }

    /**
     * Create exception for invalid maxChunksPerDocument value.
     */
    public static function invalidMaxChunks(int $value): self
    {
        return new self("maxChunksPerDocument must be a positive integer, got {$value}");
    }

    /**
     * Create exception for empty partition string.
     */
    public static function emptyPartition(): self
    {
        return new self('partition cannot be empty');
    }

    /**
     * Create exception for whitespace-only partition string.
     */
    public static function whitespacePartition(): self
    {
        return new self('partition cannot consist only of whitespace');
    }
}
