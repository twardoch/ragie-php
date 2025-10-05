<?php
// this_file: tests/RetrievalResultTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\Api\Model\Retrieval;
use Ragie\Api\Model\ScoredChunk;
use Ragie\RetrievalResult;

final class RetrievalResultTest extends TestCase
{
    public function testConstructorAcceptsRetrieval(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertInstanceOf(RetrievalResult::class, $result);
    }

    public function testGetChunksReturnsEmptyArrayForNoResults(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertSame([], $result->getChunks());
    }

    public function testGetChunksReturnsArrayOfScoredChunks(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk1->setText('First chunk');
        $chunk1->setScore(0.95);

        $chunk2 = new ScoredChunk();
        $chunk2->setText('Second chunk');
        $chunk2->setScore(0.85);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2]);

        $result = new RetrievalResult($retrieval);
        $chunks = $result->getChunks();

        $this->assertCount(2, $chunks);
        $this->assertInstanceOf(ScoredChunk::class, $chunks[0]);
        $this->assertInstanceOf(ScoredChunk::class, $chunks[1]);
        $this->assertSame('First chunk', $chunks[0]->getText());
        $this->assertSame('Second chunk', $chunks[1]->getText());
    }

    public function testGetTopChunkReturnsNullForEmptyResult(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertNull($result->getTopChunk());
    }

    public function testGetTopChunkReturnsFirstChunk(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk1->setText('Best match');
        $chunk1->setScore(0.98);

        $chunk2 = new ScoredChunk();
        $chunk2->setText('Second match');
        $chunk2->setScore(0.75);

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2]);

        $result = new RetrievalResult($retrieval);
        $topChunk = $result->getTopChunk();

        $this->assertInstanceOf(ScoredChunk::class, $topChunk);
        $this->assertSame('Best match', $topChunk->getText());
        $this->assertSame(0.98, $topChunk->getScore());
    }

    public function testGetChunkTextsReturnsEmptyArrayForNoResults(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertSame([], $result->getChunkTexts());
    }

    public function testGetChunkTextsExtractsTextStrings(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk1->setText('First text');

        $chunk2 = new ScoredChunk();
        $chunk2->setText('Second text');

        $chunk3 = new ScoredChunk();
        $chunk3->setText('Third text');

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2, $chunk3]);

        $result = new RetrievalResult($retrieval);
        $texts = $result->getChunkTexts();

        $this->assertSame(['First text', 'Second text', 'Third text'], $texts);
    }

    public function testGetDocumentIdsReturnsEmptyArrayForNoResults(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertSame([], $result->getDocumentIds());
    }

    public function testGetDocumentIdsReturnsUniqueIds(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk1->setDocumentId('doc-123');

        $chunk2 = new ScoredChunk();
        $chunk2->setDocumentId('doc-456');

        $chunk3 = new ScoredChunk();
        $chunk3->setDocumentId('doc-123'); // Duplicate

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2, $chunk3]);

        $result = new RetrievalResult($retrieval);
        $ids = $result->getDocumentIds();

        $this->assertCount(2, $ids);
        $this->assertContains('doc-123', $ids);
        $this->assertContains('doc-456', $ids);
    }

    public function testIsEmptyReturnsTrueForNoResults(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertTrue($result->isEmpty());
    }

    public function testIsEmptyReturnsFalseForResults(): void
    {
        $chunk = new ScoredChunk();
        $chunk->setText('Some text');

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $result = new RetrievalResult($retrieval);

        $this->assertFalse($result->isEmpty());
    }

    public function testCountReturnsZeroForEmptyResult(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertSame(0, $result->count());
        $this->assertCount(0, $result); // Test Countable interface
    }

    public function testCountReturnsCorrectNumber(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk2 = new ScoredChunk();
        $chunk3 = new ScoredChunk();

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2, $chunk3]);

        $result = new RetrievalResult($retrieval);

        $this->assertSame(3, $result->count());
        $this->assertCount(3, $result); // Test Countable interface
    }

    public function testGetUnderlyingRetrievalReturnsOriginalObject(): void
    {
        $retrieval = new Retrieval();
        $result = new RetrievalResult($retrieval);

        $this->assertSame($retrieval, $result->getUnderlyingRetrieval());
    }

    public function testGetChunkTextsHandlesNullText(): void
    {
        $chunk = new ScoredChunk();
        // Don't set text - should be null

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk]);

        $result = new RetrievalResult($retrieval);
        $texts = $result->getChunkTexts();

        $this->assertSame([''], $texts);
    }

    public function testGetDocumentIdsFiltersOutNullIds(): void
    {
        $chunk1 = new ScoredChunk();
        $chunk1->setDocumentId('doc-123');

        $chunk2 = new ScoredChunk();
        // Don't set document ID - should be null

        $retrieval = new Retrieval();
        $retrieval->setScoredChunks([$chunk1, $chunk2]);

        $result = new RetrievalResult($retrieval);
        $ids = $result->getDocumentIds();

        $this->assertSame(['doc-123'], $ids);
    }
}
