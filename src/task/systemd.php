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
 *
 * NOTICE: Do not name the files in the root dir the same as any of the files in the subdirs.
 * If you do that, only one of the files will be uploaded
 */
set('systemd_local_path', 'etc/systemd');
set('systemd_remote_path', '~/.config/systemd/user');

task('systemd:stop', static function (): void {
    $files = RemoteSystemdFileManager::getByStage(get('stage'));

    foreach ($files as $file) {
        run(sprintf('systemctl --user stop %s', $file));
        run(sprintf('systemctl --user disable %s', $file));
    }
})->desc('This will stop all systemd services for the running stage');

task('systemd:start', static function (): void {
    run('systemctl --user daemon-reload');

    $files = array_merge(
        RemoteSystemdFileManager::getByStageAndRelease(get('stage'), get('release_name')),
        RemoteSystemdFileManager::getByStage(get('stage'))
    );

    foreach ($files as $file) {
        run(sprintf('systemctl --user enable %s', $file));
        run(sprintf('systemctl --user start %s', $file));
    }
})->desc('This will start all systemd services for this release AND all services not having a release (i.e. manual services)');

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
            sprintf('%s/%s', $remotePath, RemoteSystemdFileManager::getRemoteFilename($file->getFilename(), $stage, get('release_name')))
        );
    }
})->desc('This will upload any systemd files to the remote path');

task('systemd:cleanup', static function (): void {
    $releasesList = get('releases_list');
    if (!isset($releasesList[1])) {
        return;
    }

    $files = RemoteSystemdFileManager::getByStageAndRelease(get('stage'), $releasesList[1]);

    foreach ($files as $file) {
        run(sprintf('rm %s', $file));
    }
})->desc('Remove systemd service files from previous release');
