<?php

// this_file: tests/Logging/StructuredLoggerTest.php

declare(strict_types=1);

namespace Ragie\Tests\Logging;

use PHPUnit\Framework\TestCase;
use Ragie\Logging\StructuredLogger;

final class StructuredLoggerTest extends TestCase
{
    public function testLogRetrievalSuccessRecordsContext(): void
    {
        $spy = new SpyLogger();
        $logger = new StructuredLogger($spy);

        $logger->logRetrievalSuccess('ragie', 'Explain retrieval augmented generation', 123, 4, true);

        $this->assertNotEmpty($spy->records);
        $record = $spy->records[0];
        $this->assertSame('info', $record['level']);
        $this->assertSame('ragie.retrieve.success', $record['message']);
        $this->assertSame(4, $record['context']['chunk_count']);
        $this->assertSame(123, $record['context']['duration_ms']);
        $this->assertTrue($record['context']['cache_hit']);
    }

    public function testLogRetrievalFailureIncludesException(): void
    {
        $spy = new SpyLogger();
        $logger = new StructuredLogger($spy);
        $error = new \RuntimeException('down');

        $logger->logRetrievalFailure('ragie', 'Explain caches', $error, 75);

        $record = $spy->records[0];
        $this->assertSame('error', $record['level']);
        $this->assertSame('ragie.retrieve.failure', $record['message']);
        $this->assertSame('RuntimeException', $record['context']['exception']);
        $this->assertSame('down', $record['context']['error_message']);
        $this->assertSame(75, $record['context']['duration_ms']);
    }

}
