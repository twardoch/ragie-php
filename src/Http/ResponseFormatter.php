<?php

declare(strict_types=1);

/**
 * this_file: src/Http/ResponseFormatter.php
 */

namespace Ragie\Http;

/**
 * Utility for formatting consistent HTTP responses
 *
 * Provides standardized success and error response formatting
 * with support for both JSON and plain text output.
 *
 * @example
 * ```php
 * // Success response
 * ResponseFormatter::success(['result' => 'data'], 'json');
 *
 * // Error response
 * ResponseFormatter::error('Not found', 404, 'json');
 * ```
 */
final class ResponseFormatter
{
    /**
     * Format and send a success response
     *
     * @param mixed $data Response data
     * @param string $format Output format ('json' or 'text')
     * @return string Formatted output (for testing)
     */
    public static function success(mixed $data, string $format = 'json'): string
    {
        $output = self::formatSuccess($data, $format);

        if (!defined('PHPUNIT_RUNNING')) {
            if ($format === 'json') {
                header('Content-Type: application/json');
            } else {
                header('Content-Type: text/plain; charset=utf-8');
            }
            echo $output;
            exit(0);
        }

        return $output;
    }

    /**
     * Format and send an error response
     *
     * @param string $message Error message
     * @param int $httpCode HTTP status code
     * @param string $format Output format ('json' or 'text')
     * @param array<string, mixed>|null $details Additional error details
     * @return string Formatted output (for testing)
     */
    public static function error(
        string $message,
        int $httpCode = 500,
        string $format = 'json',
        ?array $details = null
    ): string {
        $output = self::formatError($message, $format, $details);

        if (!defined('PHPUNIT_RUNNING')) {
            http_response_code($httpCode);
            if ($format === 'json') {
                header('Content-Type: application/json');
            } else {
                header('Content-Type: text/plain; charset=utf-8');
            }
            echo $output;
            exit($httpCode >= 500 ? 1 : 0);
        }

        return $output;
    }

    /**
     * Format success response without sending
     *
     * @param mixed $data Response data
     * @param string $format Output format
     * @return string Formatted output
     * @internal
     */
    public static function formatSuccess(mixed $data, string $format = 'json'): string
    {
        if ($format === 'json') {
            return json_encode(['success' => true, 'data' => $data], JSON_THROW_ON_ERROR);
        }

        return is_string($data) ? $data : print_r($data, true);
    }

    /**
     * Format error response without sending
     *
     * @param string $message Error message
     * @param string $format Output format
     * @param array<string, mixed>|null $details Additional details
     * @return string Formatted output
     * @internal
     */
    public static function formatError(
        string $message,
        string $format = 'json',
        ?array $details = null
    ): string {
        if ($format === 'json') {
            $response = [
                'success' => false,
                'error' => $message,
            ];
            if ($details !== null) {
                $response['details'] = $details;
            }
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        $output = "Error: $message";
        if ($details !== null) {
            $output .= "\n\nDetails:\n" . print_r($details, true);
        }
        return $output;
    }
}
