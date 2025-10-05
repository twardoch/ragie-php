<?php
// this_file: tests/RetrievalOptionsTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;
use Ragie\RetrievalOptions;

final class RetrievalOptionsTest extends TestCase
{
    public function testCreateReturnsNewInstance(): void
    {
        $options = RetrievalOptions::create();

        $this->assertInstanceOf(RetrievalOptions::class, $options);
    }

    public function testWithTopKReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withTopK(20);

        $this->assertNotSame($original, $modified, 'withTopK should return a new instance');
    }

    public function testWithTopKInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('topK must be a positive integer');

        RetrievalOptions::create()->withTopK(0);
    }

    public function testWithTopKNegativeValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RetrievalOptions::create()->withTopK(-1);
    }

    public function testWithFilterReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withFilter(['category' => 'docs']);

        $this->assertNotSame($original, $modified);
    }

    public function testWithRerankReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withRerank();

        $this->assertNotSame($original, $modified);
    }

    public function testWithMaxChunksPerDocumentReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withMaxChunksPerDocument(5);

        $this->assertNotSame($original, $modified);
    }

    public function testWithMaxChunksPerDocumentInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxChunksPerDocument must be a positive integer');

        RetrievalOptions::create()->withMaxChunksPerDocument(0);
    }

    public function testWithPartitionReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withPartition('test-partition');

        $this->assertNotSame($original, $modified);
    }

    public function testWithPartitionEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('partition cannot be empty');

        RetrievalOptions::create()->withPartition('');
    }

    public function testWithPartitionWhitespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RetrievalOptions::create()->withPartition('   ');
    }

    public function testWithRecencyBiasReturnsNewInstance(): void
    {
        $original = RetrievalOptions::create();
        $modified = $original->withRecencyBias();

        $this->assertNotSame($original, $modified);
    }

    public function testMethodChaining(): void
    {
        $options = RetrievalOptions::create()
            ->withTopK(20)
            ->withRerank()
            ->withFilter(['category' => 'docs'])
            ->withMaxChunksPerDocument(3)
            ->withPartition('test')
            ->withRecencyBias();

        $this->assertInstanceOf(RetrievalOptions::class, $options);
    }

    public function testToRetrieveParamsWithMinimalOptions(): void
    {
        $options = RetrievalOptions::create();
        $params = $options->toRetrieveParams('test query');

        $this->assertSame('test query', $params->getQuery());
    }

    public function testToRetrieveParamsWithAllOptions(): void
    {
        $filter = ['category' => ['$in' => ['sales', 'marketing']]];

        $options = RetrievalOptions::create()
            ->withTopK(25)
            ->withFilter($filter)
            ->withRerank(true)
            ->withMaxChunksPerDocument(5)
            ->withPartition('my-partition')
            ->withRecencyBias(true);

        $params = $options->toRetrieveParams('my query');

        $this->assertSame('my query', $params->getQuery());
        $this->assertSame(25, $params->getTopK());
        $this->assertSame($filter, $params->getFilter());
        $this->assertTrue($params->getRerank());
        $this->assertSame(5, $params->getMaxChunksPerDocument());
        $this->assertSame('my-partition', $params->getPartition());
        $this->assertTrue($params->getRecencyBias());
    }

    public function testToRetrieveParamsWithRerankFalse(): void
    {
        $options = RetrievalOptions::create()
            ->withRerank(false);

        $params = $options->toRetrieveParams('query');

        $this->assertFalse($params->getRerank());
    }

    public function testToRetrieveParamsWithRecencyBiasFalse(): void
    {
        $options = RetrievalOptions::create()
            ->withRecencyBias(false);

        $params = $options->toRetrieveParams('query');

        $this->assertFalse($params->getRecencyBias());
    }
}
