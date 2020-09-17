<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\after;
use function Deployer\before;

require_once 'task/systemd.php';

before('deploy:symlink', 'systemd:stop');
after('systemd:stop', 'systemd:upload');

after('deploy:symlink', 'systemd:start');

// cleanup created files
after('cleanup', 'systemd:cleanup');

// handle failure
after('deploy:failed', 'systemd:on-fail');
after('systemd:on-fail', 'systemd:cleanup');
after('systemd:on-fail', 'systemd:start');
