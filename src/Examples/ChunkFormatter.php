<?php

declare(strict_types=1);

// this_file: src/Examples/ChunkFormatter.php

namespace Ragie\Examples;

use Ragie\Api\Model\ScoredChunk;

/**
 * Formats retrieved chunks for CLI demonstrations.
 */
final class ChunkFormatter
{
    private const DEFAULT_MAX_LENGTH = 180;
    private const MIN_LENGTH = 10;

    public static function formatChunk(ScoredChunk $chunk, int $index, int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $normalizedIndex = max(1, $index);
        $effectiveLength = $maxLength >= self::MIN_LENGTH ? $maxLength : self::DEFAULT_MAX_LENGTH;

        $text = trim((string) $chunk->getText());
        if ($text === '') {
            $text = '[empty chunk text]';
        }

        if (mb_strlen($text) > $effectiveLength) {
            $text = rtrim(mb_substr($text, 0, $effectiveLength - 1)) . '…';
        }

        $document = $chunk->getDocumentName();
        $documentLabel = ($document !== null && trim($document) !== '') ? $document : 'Unknown document';

        /** @var float|null $score */
        $score = $chunk->getScore();
        $scoreText = $score !== null ? number_format((float) $score, 3) : 'N/A';

        $documentId = trim((string) $chunk->getDocumentId());
        $documentIdLine = $documentId !== '' ? sprintf("\n   Doc ID: %s", $documentId) : '';

        return sprintf(
            "%d. [%s] score=%s%s\n   %s",
            $normalizedIndex,
            $documentLabel,
            $scoreText,
            $documentIdLine,
            $text
        );
    }
}
