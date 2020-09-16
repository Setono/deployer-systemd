<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\get;
use function Deployer\run;
use function Deployer\set;
use function Deployer\task;
use function Deployer\upload;
use function Safe\sprintf;
use Symfony\Component\Finder\Finder;

/**
 * The directory structure of this path should be as follows
 *
 * etc
 * └── systemd
 *     ├── file.service
 *     ├── dev
 *         └── file.service
 *     ├── prod
 *         └── file.service
 *     └── staging
 *         └── file.service
 *
 * This structure allows you to put services that should run on all stage in the root
 * and services that should only run in a specific env you can put in the respective folder
 */
set('systemd_local_path', 'etc/systemd');
set('systemd_remote_path', '~/.config/systemd/user');

task('systemd:stop', static function (): void {
    $files = getRemoteSystemdFiles();

    foreach ($files as $file) {
        run(sprintf('systemctl --user stop %s', $file));
        run(sprintf('systemctl --user disable %s', $file));
    }
})->desc('This will stop any systemd services');

task('systemd:start', static function (): void {
    run('systemctl --user daemon-reload');

    $files = getRemoteSystemdFiles();

    foreach ($files as $file) {
        run(sprintf('systemctl --user enable %s', $file));
        run(sprintf('systemctl --user start %s', $file));
    }
})->desc('This will start any systemd services');

task('systemd:remove', static function (): void {
    // todo create backup of these if the deployment fails
    $files = getRemoteSystemdFiles();

    foreach ($files as $file) {
        run(sprintf('rm %s', $file));
    }
})->desc('Removes any systemd service files in the remote file system');

task('systemd:upload', static function (): void {
    $stage = get('stage');
    $remotePath = get('systemd_remote_path');

    $finder = new Finder();
    $finder->files()->in(get('systemd_local_path'));

    foreach ($finder as $file) {
        $env = basename($file->getRelativePath());
        if ($env !== '' && $env !== $stage) {
            continue;
        }

        upload(
            $file->getRelativePathname(),
            sprintf('%s/%s---%s.%s', $remotePath, $file->getFilenameWithoutExtension(), $stage, $file->getExtension())
        );
    }
})->desc('This will upload any systemd files to the remote path');
