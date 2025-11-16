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

    public function testRegenStampTracksChecksumAndTimestamp(): void
    {
        $stampPath = $this->projectRoot() . '/src/Ragie/Api/.regen-stamp';
        self::assertFileExists($stampPath, '.regen-stamp is missing. Run build_generatapi.sh after updating the spec.');

        $stampContent = trim((string) file_get_contents($stampPath));
        self::assertNotSame('', $stampContent, '.regen-stamp must contain metadata.');

        $data = [];
        foreach (preg_split('/\r\n|\r|\n/', $stampContent) as $line) {
            if ($line === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $data[$key] = $value;
        }

        self::assertArrayHasKey('spec_sha', $data, '.regen-stamp must record spec_sha.');
        self::assertArrayHasKey('generated_at', $data, '.regen-stamp must record generated_at timestamp.');

        $expectedSha = trim((string) file_get_contents($this->projectRoot() . '/openapi.sha256'));
        self::assertSame(
            $expectedSha,
            $data['spec_sha'],
            '.regen-stamp spec_sha must match openapi.sha256.'
        );

        $timestamp = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data['generated_at']);
        self::assertInstanceOf(
            \DateTimeImmutable::class,
            $timestamp,
            '.regen-stamp generated_at must be a valid ISO-8601 timestamp.'
        );
    }
}
