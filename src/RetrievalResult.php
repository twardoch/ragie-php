<?php

// this_file: src/RetrievalResult.php

declare(strict_types=1);

namespace Ragie;

use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;

/**
 * Convenient wrapper for retrieval results
 *
 * Wraps the API's Retrieval response and provides convenient accessor methods
 * for common operations. Implements Countable for natural count() usage.
 *
 * @example
 * ```php
 * $result = $ragie->retrieve('question');
 *
 * if ($result->isEmpty()) {
 *     echo "No results found\n";
 *     return;
 * }
 *
 * echo "Found {$result->count()} chunks\n";
 *
 * // Get best match
 * $topChunk = $result->getTopChunk();
 * echo "Best: {$topChunk->getText()}\n";
 *
 * // Get all chunk texts
 * $texts = $result->getChunkTexts();
 *
 * // Access full chunk objects
 * foreach ($result->getChunks() as $chunk) {
 *     echo "{$chunk->getDocumentName()}: {$chunk->getText()}\n";
 * }
 * ```
 *
 * @package Ragie
 */
final class RetrievalResult implements \Countable
{
    private Retrieval $retrieval;

    /** @var string[]|null Memoized chunk texts */
    private ?array $chunkTexts = null;

    /** @var string[]|null Memoized document IDs */
    private ?array $documentIds = null;

    /**
     * Wrap an API Retrieval response
     *
     * @param Retrieval $retrieval The underlying API response
     */
    public function __construct(Retrieval $retrieval)
    {
        $this->retrieval = $retrieval;
    }

    /**
     * Get all scored chunks from the retrieval
     *
     * @return ScoredChunk[] Array of scored chunk objects
     */
    public function getChunks(): array
    {
        $chunks = $this->retrieval->getScoredChunks();
        // @phpstan-ignore-next-line getScoredChunks() can return null from generated API
        return $chunks ?? [];
    }

    /**
     * Get the highest scoring chunk
     *
     * Returns null if there are no results.
     *
     * @return ScoredChunk|null The top-scoring chunk, or null if empty
     */
    public function getTopChunk(): ?ScoredChunk
    {
        $chunks = $this->getChunks();

        if (empty($chunks)) {
            return null;
        }

        return $chunks[0];
    }

    /**
     * Get array of just the text content from all chunks
     *
     * Convenient method to extract just the text strings without the full chunk objects.
     * Results are memoized for performance on repeated calls.
     *
     * @return string[] Array of text strings
     */
    public function getChunkTexts(): array
    {
        if ($this->chunkTexts === null) {
            $this->chunkTexts = array_map(
                static function (ScoredChunk $chunk): string {
                    $text = $chunk->getText();
                    // @phpstan-ignore-next-line getText() can return null from generated API
                    return $text ?? '';
                },
                $this->getChunks()
            );
        }

        return $this->chunkTexts;
    }

    /**
     * Get unique document IDs from the results
     *
     * Returns an array of unique document IDs that appear in the results.
     * Results are memoized for performance on repeated calls.
     *
     * @return string[] Array of unique document IDs
     */
    public function getDocumentIds(): array
    {
        if ($this->documentIds === null) {
            $ids = array_map(
                static function (ScoredChunk $chunk): string {
                    $id = $chunk->getDocumentId();
                    // @phpstan-ignore-next-line getDocumentId() can return null from generated API
                    return $id ?? '';
                },
                $this->getChunks()
            );

            $this->documentIds = array_values(array_unique(array_filter($ids)));
        }

        return $this->documentIds;
    }

    /**
     * Check if the result is empty (no chunks found)
     *
     * @return bool True if no results were found
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Get the number of chunks in the result
     *
     * Implements the Countable interface, allowing natural count($result) usage.
     *
     * @return int Number of chunks
     */
    #[\Override]
    public function count(): int
    {
        return count($this->getChunks());
    }

    /**
     * Access the underlying API Retrieval object
     *
     * Provides an escape hatch to access the raw API response when needed.
     *
     * @return Retrieval The underlying API response object
     */
    public function getUnderlyingRetrieval(): Retrieval
    {
        return $this->retrieval;
    }
}
