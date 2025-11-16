<?php

declare(strict_types=1);

// this_file: tests/unit/Examples/ChunkFormatterTest.php

namespace Ragie\Tests\Examples;

use PHPUnit\Framework\TestCase;
use Ragie\Api\Model\ScoredChunk;
use Ragie\Examples\ChunkFormatter;

final class ChunkFormatterTest extends TestCase
{
    public function testFormatChunkTruncatesTextAndDisplaysMetadata(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText(str_repeat('A', 200));
        $chunk->setDocumentName('Docs/Guide.md');
        $chunk->setDocumentId('doc-123');
        $chunk->setScore(0.95234);

        $formatted = ChunkFormatter::formatChunk($chunk, 1, 50);

        $this->assertStringStartsWith('1. [Docs/Guide.md] score=0.952', $formatted);
        $this->assertStringContainsString('Doc ID: doc-123', $formatted);
        $this->assertStringContainsString('…', $formatted, 'Long text should be truncated with ellipsis');
    }

    public function testFormatChunkHandlesMissingFieldsGracefully(): void
    {
        $chunk = new ScoredChunk();
        // Leave text, document, and score unset to simulate incomplete payload

        $formatted = ChunkFormatter::formatChunk($chunk, 3);

        $this->assertStringStartsWith('3. [Unknown document] score=N/A', $formatted);
        $this->assertStringContainsString('[empty chunk text]', $formatted);
    }

    public function testFormatChunkNormalizesIndexAndLength(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('Short text');

        $formatted = ChunkFormatter::formatChunk($chunk, 0, -10);

        $this->assertStringStartsWith('1.', $formatted, 'Index should be normalized to at least 1');
        $this->assertStringContainsString('Short text', $formatted);
    }
}
