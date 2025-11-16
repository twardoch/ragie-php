#!/usr/bin/env php
<?php

// this_file: verify-deployment.php

declare(strict_types=1);

/**
 * Post-Deployment Verification Script
 *
 * Tests remote RAG endpoints to ensure deployment was successful.
 * Run this after deploying to production to verify functionality.
 *
 * Usage:
 *   php verify-deployment.php [base-url]
 *
 * Example:
 *   php verify-deployment.php https://ask.vexy.art
 */

const TIMEOUT_SECONDS = 30;
const TEST_QUERY = 'What is Ragie?';

class Colors
{
    public const RESET = "\033[0m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const BOLD = "\033[1m";
}

class VerificationTest
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly int $expectedStatus,
        public readonly array $expectedFields = [],
        public readonly ?int $maxResponseTime = null
    ) {
    }
}

function colored(string $text, string $color): string
{
    return $color . $text . Colors::RESET;
}

function success(string $message): void
{
    echo colored('✓', Colors::GREEN) . ' ' . $message . PHP_EOL;
}

function error(string $message): void
{
    echo colored('✗', Colors::RED) . ' ' . $message . PHP_EOL;
}

function info(string $message): void
{
    echo colored('ℹ', Colors::BLUE) . ' ' . $message . PHP_EOL;
}

function warning(string $message): void
{
    echo colored('⚠', Colors::YELLOW) . ' ' . $message . PHP_EOL;
}

function runTest(VerificationTest $test): bool
{
    echo PHP_EOL . colored('Testing: ', Colors::BOLD) . $test->name . PHP_EOL;
    echo '  URL: ' . $test->url . PHP_EOL;

    $startTime = microtime(true);

    $ch = curl_init($test->url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => TIMEOUT_SECONDS,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => 'ragie-php-verification/1.0',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $responseTime = (microtime(true) - $startTime) * 1000;
    curl_close($ch);

    $passed = true;

    // Check for cURL errors
    if ($curlError) {
        error("cURL error: {$curlError}");
        return false;
    }

    // Check HTTP status
    if ($httpCode !== $test->expectedStatus) {
        error("HTTP status: expected {$test->expectedStatus}, got {$httpCode}");
        $passed = false;
    } else {
        success("HTTP status: {$httpCode}");
    }

    // Check response time
    if ($test->maxResponseTime !== null && $responseTime > $test->maxResponseTime) {
        warning(sprintf('Response time: %.0fms (exceeds max %dms)', $responseTime, $test->maxResponseTime));
    } else {
        success(sprintf('Response time: %.0fms', $responseTime));
    }

    // Check response content
    if ($response === false || $response === '') {
        error('Empty response body');
        return false;
    }

    // Try to decode JSON
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        success('Valid JSON response');

        // Check expected fields
        foreach ($test->expectedFields as $field) {
            if (isset($data[$field])) {
                success("Field '{$field}' present");
            } else {
                error("Field '{$field}' missing");
                $passed = false;
            }
        }

        // Show sample data
        if (isset($data['count'])) {
            info("Retrieved {$data['count']} chunks");
        }
        if (isset($data['execution_time_ms'])) {
            info("Server execution: {$data['execution_time_ms']}ms");
        }
        if (isset($data['rag_source'])) {
            info("RAG source: {$data['rag_source']}");
        }
    } else {
        warning('Non-JSON response (may be expected for HTML endpoints)');
    }

    return $passed;
}

function main(array $argv): int
{
    $baseUrl = $argv[1] ?? 'https://ask.vexy.art';
    $baseUrl = rtrim($baseUrl, '/');

    echo colored('═══════════════════════════════════════════════════', Colors::BOLD) . PHP_EOL;
    echo colored('  RAG Endpoint Deployment Verification', Colors::BOLD) . PHP_EOL;
    echo colored('═══════════════════════════════════════════════════', Colors::BOLD) . PHP_EOL;
    echo PHP_EOL;
    info("Base URL: {$baseUrl}");
    info('Test query: ' . TEST_QUERY);

    $tests = [
        new VerificationTest(
            name: 'RAG Endpoint - JSON Format',
            url: $baseUrl . '/rag/?text=' . urlencode(TEST_QUERY) . '&format=json',
            expectedStatus: 200,
            expectedFields: ['success', 'chunks', 'count', 'execution_time_ms', 'rag_source'],
            maxResponseTime: 5000
        ),
        new VerificationTest(
            name: 'RAG Endpoint - Text Format',
            url: $baseUrl . '/rag/?text=' . urlencode(TEST_QUERY),
            expectedStatus: 200,
            maxResponseTime: 5000
        ),
        new VerificationTest(
            name: 'RAG Endpoint - Missing Query Parameter',
            url: $baseUrl . '/rag/',
            expectedStatus: 400,
            expectedFields: ['error']
        ),
        new VerificationTest(
            name: 'Main Chatbot Interface',
            url: $baseUrl . '/',
            expectedStatus: 200
        ),
    ];

    $results = [];
    foreach ($tests as $test) {
        $results[] = runTest($test);
    }

    echo PHP_EOL;
    echo colored('═══════════════════════════════════════════════════', Colors::BOLD) . PHP_EOL;

    $passed = count(array_filter($results));
    $total = count($results);

    if ($passed === $total) {
        success("All {$total} tests passed!");
        return 0;
    }

    error("{$passed}/{$total} tests passed");
    return 1;
}

exit(main($argv));
