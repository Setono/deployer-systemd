<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\run;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

final class RemoteSystemdFileManager
{
    private static string $defaultPath = '{{systemd_local_path}}';

    /**
     * Returns a string like my-service---prod---r34.service
     */
    public static function getRemoteFilename(string $filename, string $stage, string $release): string
    {
        $pathInfo = pathinfo($filename);
        Assert::keyExists($pathInfo, 'extension');
        Assert::stringNotEmpty($pathInfo['extension']);

        return sprintf('%s---%s---r%s.%s', $pathInfo['filename'], $stage, $release, $pathInfo['extension']);
    }

    /**
     * @return string[]
     */
    public static function getAll(string $path = null): array
    {
        return SystemdFinder::findAll($path ?? self::$defaultPath, static function (string $command): string {
            return run($command);
        });
    }

    /**
     * @return string[]
     */
    public static function getByStage(string $stage, string $path = null): array
    {
        return SystemdFinder::findByStage($path ?? self::$defaultPath, $stage, static function (string $command): string {
            return run($command);
        });
    }

    /**
     * @return string[]
     */
    public static function getByRelease(string $release, string $path = null): array
    {
        return SystemdFinder::findByRelease($path ?? self::$defaultPath, $release, static function (string $command): string {
            return run($command);
        });
    }

    /**
     * @return string[]
     */
    public static function getByStageAndRelease(string $stage, string $release, string $path = null): array
    {
        return SystemdFinder::findByStageAndRelease($path ?? self::$defaultPath, $stage, $release, static function (string $command): string {
            return run($command);
        });
    }
}
