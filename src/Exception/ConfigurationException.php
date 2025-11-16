<?php

// this_file: src/Exception/ConfigurationException.php

declare(strict_types=1);

namespace Ragie\Exception;

use RuntimeException;

/**
 * Thrown when required configuration is missing or invalid.
 *
 * This exception is thrown during initialization when environment variables
 * or configuration values are missing or invalid.
 *
 * @example
 * ```php
 * $apiKey = getenv('RAGIE_API_KEY');
 * if ($apiKey === false || trim($apiKey) === '') {
 *     throw ConfigurationException::missingEnv('RAGIE_API_KEY');
 * }
 * ```
 */
class ConfigurationException extends RuntimeException implements RagieException
{
    /**
     * Create exception for missing required environment variable.
     *
     * @param string $varName Name of the missing environment variable
     * @psalm-suppress PossiblyUnusedMethod Legacy helper retained for backward compatibility.
     */
    public static function missingEnv(string $varName): self
    {
        return new self(sprintf(
            'Required environment variable "%s" is not set',
            $varName
        ));
    }

    /**
     * Create exception for empty environment variable.
     *
     * @param string $varName Name of the empty environment variable
     * @psalm-suppress PossiblyUnusedMethod Legacy helper retained for backward compatibility.
     */
    public static function emptyEnv(string $varName): self
    {
        return new self(sprintf(
            'Environment variable "%s" is set but empty',
            $varName
        ));
    }

    /**
     * Create exception for invalid configuration value.
     *
     * @param string $key Configuration key
     * @param string $reason Why the value is invalid
     * @api
     */
    public static function invalid(string $key, string $reason): self
    {
        return new self(sprintf(
            'Invalid configuration for "%s": %s',
            $key,
            $reason
        ));
    }
}
