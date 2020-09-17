<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\after;
use function Deployer\before;

require_once 'task/systemd.php';

before('deploy:prepare', 'systemd:stop');
after('deploy:release', 'systemd:upload');

after('deploy:symlink', 'systemd:start');

// cleanup created files
after('cleanup', 'systemd:cleanup');
after('deploy:failed', 'systemd:cleanup');
