<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd\recipe;

use function Deployer\desc;
use function Deployer\fail;
use function Deployer\localhost;
use function Deployer\run;
use function Deployer\set;
use function Deployer\task;
use function Deployer\writeln;

require_once 'vendor/deployer/deployer/recipe/common.php';
require_once 'recipe/systemd.php';

// configuration
set('repository', __DIR__ . '/repository');
set('branch', null);
set('http_user', getenv('USER'));

set('systemd_local_path', __DIR__ . '/systemd');
set('systemd_remote_path', __DIR__ . '/../../.build/systemd');

// Hosts
localhost()
    ->set('deploy_path', __DIR__ . '/../../.build/deployer');

// Tasks
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

desc('Test deploy fail');
task('deploy_fail', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'fail',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

task('fail', 'unknown_command');

// If deploy fails automatically unlock

fail('deploy_fail', 'deploy:unlock');

// mocks
task('deploy:vendors', function () {
    run('echo {{bin/composer}} {{composer_options}}');
});

task('systemd:start', static function (): void {
    writeln('➤➤➤ starting services in stage "{{stage}}" and release "{{release_name}}"');
});

task('systemd:stop', static function (): void {
    writeln('➤➤➤ stopping services in stage "{{stage}}"');
});
