# Systemd functions for Deployer

[![Latest Version][ico-version]][link-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]

Manage systemd service files in your deployment process.

## Installation

```bash
$ composer require setono/deployer-systemd
```

## Usage
The easiest usage is to include the cron recipe which hooks into default Deployer events:

```php
<?php
// deploy.php

require_once 'recipe/systemd.php';
```

## Deployer parameters

The following Deployer parameters are defined:

| Parameter               | Description                                                        | Default value               |
|-------------------------|--------------------------------------------------------------------|-----------------------------|
| systemd_local_path      | The local directory to search for systemd service files            | `etc/systemd`               |
| systemd_remote_path     | The remote directory where systemd service files will be uploaded  | `~/.config/systemd/user`    |

[ico-version]: https://poser.pugx.org/setono/deployer-systemd/v/stable
[ico-unstable-version]: https://poser.pugx.org/setono/deployer-systemd/v/unstable
[ico-license]: https://poser.pugx.org/setono/deployer-systemd/license
[ico-github-actions]: https://github.com/Setono/deployer-systemd/workflows/build/badge.svg

[link-packagist]: https://packagist.org/packages/setono/deployer-systemd
[link-github-actions]: https://github.com/Setono/deployer-systemd/actions
