<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\run;
use function Safe\preg_split;
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
        $path = $path ?? self::$defaultPath;

        $output = run(sprintf('find %s -type f -name "*.service"', $path));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function getByStage(string $stage, string $path = null): array
    {
        $path = $path ?? self::$defaultPath;

        $output = run(sprintf('find %s -type f -name "*---%s---*.service"', $path, $stage));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function getByRelease(string $release, string $path = null): array
    {
        $path = $path ?? self::$defaultPath;

        $output = run(sprintf('find %s -type f -name "*---*---r%s.service"', $path, $release));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function getByStageAndRelease(string $stage, string $release, string $path = null): array
    {
        $path = $path ?? self::$defaultPath;

        $output = run(sprintf('find %s -type f -name "*---%s---r%s.service"', $path, $stage, $release));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    private static function handleOutput(string $output): array
    {
        return preg_split('/[\r\n]+/', $output);
    }
}
