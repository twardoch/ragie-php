<?php

// this_file: tests/Logging/SpyLogger.php

declare(strict_types=1);

namespace Ragie\Tests\Logging;

use Psr\Log\AbstractLogger;

final class SpyLogger extends AbstractLogger
{
    /** @var array<int, array{level: string, message: string, context: array<string, mixed>}> */
    public array $records = [];

    /**
     * @param mixed $level
     * @param mixed $message
     * @param array<array-key, mixed> $context
     */
    #[\Override]
    public function log($level, $message, array $context = []): void
    {
        $this->records[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
