<?php

// this_file: tests/unit/Benchmark/RetrievalBenchmarkScriptTest.php

declare(strict_types=1);

namespace Ragie\Tests;

use PHPUnit\Framework\TestCase;

final class RetrievalBenchmarkScriptTest extends TestCase
{
    public function testBenchmarkExitsWithHelpfulErrorWhenApiKeyMissing(): void
    {
        $script = realpath(__DIR__ . '/../../../benchmarks/RetrievalBenchmark.php');
        $this->assertIsString($script, 'Benchmark script path should resolve');

        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            escapeshellcmd(\PHP_BINARY) . ' ' . escapeshellarg($script),
            $descriptorSpec,
            $pipes,
            dirname($script),
            $this->buildEnvWithoutApiKey()
        );

        $this->assertIsResource($process, 'Benchmark process failed to start');

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $this->assertSame(1, $exitCode, 'Benchmark should exit with status 1 when API key missing');
        $this->assertSame('', trim((string) $stdout), 'Benchmark must not emit stdout for missing API key');
        $this->assertStringContainsString('Missing RAGIE_API_KEY', (string) $stderr);
    }

    /**
     * Build a clean environment array without the Ragie API key.
     *
     * @return array<string, string>
     */
    private function buildEnvWithoutApiKey(): array
    {
        $env = [];

        foreach ($_ENV as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            $env[$key] = $value;
        }

        $path = getenv('PATH');
        if (is_string($path)) {
            $env['PATH'] = $path;
        } elseif (!isset($env['PATH'])) {
            $env['PATH'] = '';
        }

        $env['RAGIE_API_KEY'] = '';
        unset($env['RAGIE_BENCH_ITERATIONS'], $env['RAGIE_BENCH_DELAY'], $env['RAGIE_BENCH_QUERIES']);

        return $env;
    }
}
