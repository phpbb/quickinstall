<?php
/**
 *
 * QuickInstall sandbox web UI router
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

require_once __DIR__ . '/../src/QuickInstall/Sandbox/bootstrap.php';
require_once __DIR__ . '/../src/QuickInstall/Sandbox/Web/Application.php';

(new QuickInstall\Sandbox\Web\Application(dirname(__DIR__)))->run();
