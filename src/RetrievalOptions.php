<?php

// this_file: src/RetrievalOptions.php

declare(strict_types=1);

namespace Ragie;

use Ragie\Api\Model\RetrieveParams;
use Ragie\Exception\InvalidParameterException;

/**
 * Fluent builder for retrieval parameters
 *
 * Provides a convenient, immutable builder interface for constructing retrieval parameters.
 * Each `with*` method returns a new instance, allowing for safe method chaining.
 *
 * @example
 * ```php
 * use Ragie\RetrievalOptions;
 *
 * $options = RetrievalOptions::create()
 *     ->withTopK(20)
 *     ->withRerank()
 *     ->withFilter(['category' => ['$in' => ['sales', 'marketing']]])
 *     ->withMaxChunksPerDocument(3);
 *
 * $result = $ragie->retrieve('sales strategy', $options);
 * ```
 *
 * @package Ragie
 */
final class RetrievalOptions
{
    private ?int $topK = null;

    /** @var array<string, mixed>|null */
    private ?array $filter = null;
    private ?bool $rerank = null;
    private ?int $maxChunksPerDocument = null;
    private ?string $partition = null;
    private ?bool $recencyBias = null;

    /**
     * Private constructor to enforce use of factory method
     */
    private function __construct()
    {
    }

    /**
     * Create a new RetrievalOptions instance
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the number of results to return
     *
     * @param int $topK Number of chunks to retrieve (must be positive)
     *
     * @example
     * ```php
     * $options = RetrievalOptions::create()->withTopK(10);
     * // Returns top 10 most relevant chunks
     * ```
     *
     * @throws InvalidParameterException If topK is not positive
     * @return self New instance with the updated value
     */
    public function withTopK(int $topK): self
    {
        if ($topK <= 0) {
            throw InvalidParameterException::invalidTopK($topK);
        }

        $clone = clone $this;
        $clone->topK = $topK;

        return $clone;
    }

    /**
     * Set metadata filter for results
     *
     * Filters results to only include documents matching the specified metadata criteria.
     * See Ragie documentation for filter syntax and supported operators.
     *
     * @param array<string, mixed> $filter Metadata filter criteria
     *
     * @return self New instance with the updated value
     *
     * @example
     * ```php
     * // Equal to
     * ->withFilter(['category' => 'docs'])
     *
     * // In array
     * ->withFilter(['department' => ['$in' => ['sales', 'marketing']]])
     *
     * // Greater than
     * ->withFilter(['priority' => ['$gt' => 5]])
     * ```
     */
    public function withFilter(array $filter): self
    {
        $clone = clone $this;
        $clone->filter = $filter;

        return $clone;
    }

    /**
     * Enable or disable result reranking
     *
     * When enabled, results are reranked using a more sophisticated model for better relevance.
     *
     * @param bool $rerank Whether to rerank results (default: true)
     *
     * @example
     * ```php
     * $options = RetrievalOptions::create()->withRerank();
     * // Enable reranking for more accurate results (slight performance cost)
     *
     * $options = RetrievalOptions::create()->withRerank(false);
     * // Disable reranking for faster retrieval
     * ```
     *
     * @return self New instance with the updated value
     */
    public function withRerank(bool $rerank = true): self
    {
        $clone = clone $this;
        $clone->rerank = $rerank;

        return $clone;
    }

    /**
     * Set maximum chunks per document
     *
     * Limits how many chunks can be returned from a single document.
     *
     * @param int $max Maximum chunks per document (must be positive)
     *
     *
     * @throws InvalidParameterException If max is not positive
     * @return self New instance with the updated value
     */
    public function withMaxChunksPerDocument(int $max): self
    {
        if ($max <= 0) {
            throw InvalidParameterException::invalidMaxChunks($max);
        }

        $clone = clone $this;
        $clone->maxChunksPerDocument = $max;

        return $clone;
    }

    /**
     * Set partition to search within
     *
     * Scopes the search to a specific partition.
     *
     * @param string $partition Partition identifier
     *
     *
     * @throws InvalidParameterException If partition is empty or whitespace
     * @return self New instance with the updated value
     */
    public function withPartition(string $partition): self
    {
        $trimmed = trim($partition);
        if ($trimmed === '') {
            if ($partition === '') {
                throw InvalidParameterException::emptyPartition();
            }
            throw InvalidParameterException::whitespacePartition();
        }

        $clone = clone $this;
        $clone->partition = $partition;

        return $clone;
    }

    /**
     * Enable or disable recency bias
     *
     * When enabled, more recent documents are given higher scores.
     *
     * @param bool $bias Whether to apply recency bias (default: true)
     *
     * @return self New instance with the updated value
     */
    public function withRecencyBias(bool $bias = true): self
    {
        $clone = clone $this;
        $clone->recencyBias = $bias;

        return $clone;
    }

    /**
     * Convert to API RetrieveParams model
     *
     * Internal method that converts this options object into the API's expected parameter format.
     *
     * @param string $query The search query
     *
     * @return RetrieveParams The API parameter object
     *
     * @internal
     */
    public function toRetrieveParams(string $query): RetrieveParams
    {
        $params = new RetrieveParams([
            'query' => $query,
        ]);

        if ($this->topK !== null) {
            $params->setTopK($this->topK);
        }

        if ($this->filter !== null) {
            $params->setFilter($this->filter);
        }

        if ($this->rerank !== null) {
            $params->setRerank($this->rerank);
        }

        if ($this->maxChunksPerDocument !== null) {
            $params->setMaxChunksPerDocument($this->maxChunksPerDocument);
        }

        if ($this->partition !== null) {
            $params->setPartition($this->partition);
        }

        if ($this->recencyBias !== null) {
            $params->setRecencyBias($this->recencyBias);
        }

        return $params;
    }
}
