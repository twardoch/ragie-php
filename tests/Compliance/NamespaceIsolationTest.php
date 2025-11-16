<?php

declare(strict_types=1);

// this_file: ragie-php/tests/Compliance/NamespaceIsolationTest.php

namespace Ragie\Tests\Compliance;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class NamespaceIsolationTest extends TestCase
{
    public function test_source_files_contain_no_paragra_namespaces(): void
    {
        $projectRoot = dirname(__DIR__);
        $sourceDir = $projectRoot . '/src';
        $composerFile = $projectRoot . '/composer.json';

        $filesToScan = $this->gatherPhpFiles($sourceDir);
        $filesToScan[] = new SplFileInfo($composerFile);

        $violations = [];

        foreach ($filesToScan as $file) {
            $contents = file_get_contents($file->getPathname());
            if ($contents === false) {
                self::fail(sprintf('Unable to read %s', $file->getPathname()));
            }

            if (str_contains($contents, 'ParaGra\\')) {
                $violations[] = $file->getPathname();
            }
        }

        $list = $violations === [] ? '' : PHP_EOL . implode(PHP_EOL, $violations);
        self::assertSame([], $violations, 'ParaGra namespace references found in:' . $list);
    }

    /**
     * @return list<SplFileInfo>
     */
    private function gatherPhpFiles(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }
}
