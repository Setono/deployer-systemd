<?php
declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class SystemdFinderTest extends TestCase
{
    private const PATH = __DIR__ . '/systemd';
    /**
     * @test
     */
    public function it_finds_all(): void
    {
        $files = SystemdFinder::findAll(self::PATH, self::getCallback());

        self::assertFiles([
            'service.service',
            'service---dev.service',
            'service---dev---r23.service',
            'service---dev---r24.service',
            'service---prod.service',
            'service---prod-r23.service',
        ], $files);
    }

    /**
     * @test
     */
    public function it_finds_dev_services(): void
    {
        $files = SystemdFinder::findByStage(self::PATH, 'dev', self::getCallback());

        self::assertFiles([
            'service---dev.service',
            'service---dev---r23.service',
            'service---dev---r24.service',
        ], $files);
    }

    /**
     * @test
     */
    public function it_finds_prod_services(): void
    {
        $files = SystemdFinder::findByStage(self::PATH, 'prod', self::getCallback());

        self::assertFiles([
            'service---prod.service',
            'service---prod-r23.service',
        ], $files);
    }

    /**
     * @test
     */
    public function it_does_not_find_other_stage(): void
    {
        $files = SystemdFinder::findByStage(self::PATH, 'staging', self::getCallback());
        self::assertEmpty($files);
    }

    /**
     * @test
     */
    public function it_finds_by_stage_and_release(): void
    {
        $files = SystemdFinder::findByStageAndRelease(self::PATH, 'dev', '23', self::getCallback());

        self::assertFiles([
            'service---dev---r23.service',
        ], $files);
    }

    /**
     * @test
     */
    public function it_does_not_find_other_release_and_stage(): void
    {
        $files = SystemdFinder::findByStageAndRelease(self::PATH, 'dev', '25', self::getCallback());
        self::assertEmpty($files);
    }

    private static function getCallback(): callable
    {
        return static function(string $command): string {
            $process = Process::fromShellCommandline($command);
            $process->run();
            return $process->getOutput();
        };
    }

    private static function assertFiles(array $expected, array $actual): void
    {
        $actual = array_map(static function(string $path): string {
            return basename($path);
        }, $actual);

        self::assertEqualsCanonicalizing($expected, $actual);
    }
}
