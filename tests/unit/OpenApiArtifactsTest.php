<?php

// this_file: tests/unit/OpenApiArtifactsTest.php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 * @psalm-suppress UnusedClass Tests are discovered via PHPUnit configuration.
 */
final class OpenApiArtifactsTest extends TestCase
{
    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    public function testOpenApiChecksumMatchesSpec(): void
    {
        $specPath = $this->projectRoot() . '/openapi.json';
        $checksumPath = $this->projectRoot() . '/openapi.sha256';

        self::assertFileExists($specPath, 'Spec file openapi.json is missing.');
        self::assertFileExists($checksumPath, 'Checksum file openapi.sha256 is missing.');

        $expected = trim((string) file_get_contents($checksumPath));
        $actual = hash_file('sha256', $specPath);

        self::assertSame(
            $expected,
            $actual,
            'openapi.sha256 checksum does not match the local spec. Regenerate the checksum after updating the spec.'
        );
    }

    public function testOpenApiToolsConfigConfiguresRagieNamespaces(): void
    {
        $configPath = $this->projectRoot() . '/openapitools.json';
        self::assertFileExists($configPath, 'Expected openapitools.json to be present.');

        $config = json_decode((string) file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);

        $additional = $config['additionalProperties'] ?? [];
        self::assertSame('Ragie\\Api', $additional['invokerPackage'] ?? null, 'invokerPackage must be Ragie\\Api');
        self::assertSame('Api', $additional['apiPackage'] ?? null, 'apiPackage must be Api');
        self::assertSame('Model', $additional['modelPackage'] ?? null, 'modelPackage must be Model');
        self::assertArrayHasKey('packageVersion', $additional, 'packageVersion must be pinned to track releases.');

        $global = $config['globalProperties'] ?? [];
        self::assertSame('false', $global['apiDocs'] ?? null, 'Generator should skip API docs output.');
        self::assertSame('false', $global['modelDocs'] ?? null, 'Generator should skip model docs output.');
        self::assertSame('php', $config['generatorName'] ?? null, 'generatorName must be php.');
    }

    public function testBuildScriptUsesLocalSpec(): void
    {
        $scriptPath = $this->projectRoot() . '/build_generatapi.sh';
        self::assertFileExists($scriptPath, 'build_generatapi.sh is missing.');

        $contents = (string) file_get_contents($scriptPath);
        self::assertStringContainsString('openapi.json', $contents, 'Script must use the local openapi.json.');
        self::assertStringNotContainsString('https://api.ragie.ai/openapi.json', $contents, 'Script should not pull the remote spec directly.');
    }
}
