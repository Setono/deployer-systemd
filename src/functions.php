<?php

declare(strict_types=1);

namespace Setono\Deployer\Systemd;

use function Deployer\run;
use function Safe\preg_split;

/**
 * @return string[]
 */
function getRemoteSystemdFiles(): array
{
    $output = run('find {{systemd_local_path}} -type f -name "*---{{stage}}.service"');

    return preg_split('/[\r\n]+/', $output);
}
