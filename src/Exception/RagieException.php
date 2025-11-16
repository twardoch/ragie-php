<?php

// this_file: src/Exception/RagieException.php

declare(strict_types=1);

namespace Ragie\Exception;

use Throwable;

/**
 * Base exception interface for all Ragie-specific exceptions.
 *
 * This allows catching all Ragie exceptions with a single catch block:
 * <code>
 * try {
 *     $client->retrieve($query);
 * } catch (RagieException $e) {
 *     // Handle any Ragie-specific error
 * }
 * </code>
 */
interface RagieException extends Throwable
{
}
