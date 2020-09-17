<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Safe\preg_split;
use function Safe\sprintf;

final class SystemdFinder
{
    /**
     * @return string[]
     */
    public static function findAll(string $path, callable $callable): array
    {
        $output = $callable(sprintf('find %s -type f -name "*.service"', $path));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function findByStage(string $path, string $stage, callable $callable): array
    {
        $output = $callable(sprintf('find %s -type f -name "*---%s*.service"', $path, $stage));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function findByRelease(string $path, string $release, callable $callable): array
    {
        $output = $callable(sprintf('find %s -type f -name "*---*---r%s.service"', $path, $release));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    public static function findByStageAndRelease(string $path, string $stage, string $release, callable $callable): array
    {
        $output = $callable(sprintf('find %s -type f -name "*---%s---r%s.service"', $path, $stage, $release));

        return self::handleOutput($output);
    }

    /**
     * @return string[]
     */
    private static function handleOutput(string $output): array
    {
        $output = rtrim($output);
        if ('' === $output) {
            return [];
        }

        return preg_split('/[\r\n]+/', $output);
    }
}
