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

/**
 * This is the release to cleanup when the deployment has finished
 *
 * NOTICE that when the deployment fails, this parameter is changed to the release that was supposed to have
 * been deployed and not the previous release as this function does by default
 *
 * This is handled in the systemd:on-fail task
 */
set('systemd_cleanup_release', static function (): ?string {
    $releasesList = get('releases_list');

    return $releasesList[1] ?? null;
});

/**
 * This is the release to start when the deployment has finished
 *
 * NOTICE that when the deployment fails, this parameter is changed to the previous release instead of the
 * release that should have been deployed
 *
 * This is handled in the systemd:on-fail task
 */
set('systemd_start_release', static function (): string {
    return get('release_name');
});

task('systemd:stop', static function (): void {
    $files = RemoteSystemdFileManager::getByStage(get('stage'));

    foreach ($files as $file) {
        // todo I don't know what happens if you try to disable and stop services that are not enabled/started
        run(sprintf('systemctl --user stop %s', $file));
        run(sprintf('systemctl --user disable %s', $file));
    }
})->desc('This will stop all systemd services for the running stage');

task('systemd:start', static function (): void {
    run('systemctl --user daemon-reload');

    // todo what is the 'release_name' when a deployment fails?
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

/**
 * Should be called immediately after deploy:failed
 */
task('systemd:on-fail', static function (): void {
    set('systemd_cleanup_release', get('release_name'));

    $releasesList = get('releases_list');
    if (isset($releasesList[1])) {
        set('systemd_start_release', $releasesList[1]);
    }
})->desc('Handles the event where deployment fails');

task('systemd:cleanup', static function (): void {
    $release = get('systemd_cleanup_release');

    $files = RemoteSystemdFileManager::getByStageAndRelease(get('stage'), $release);

    foreach ($files as $file) {
        run(sprintf('rm %s', $file));
    }
})->desc('Remove unused systemd service files');
