<?php
/**
 *
 * QuickInstall sandbox web UI router
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath(__DIR__ . $path);
if (PHP_SAPI === 'cli-server' && $file !== false && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file))
{
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	$contentTypes = [
		'css' => 'text/css; charset=utf-8',
		'js' => 'application/javascript; charset=utf-8',
		'svg' => 'image/svg+xml; charset=utf-8',
	];
	if (isset($contentTypes[$extension]))
	{
		header('Content-Type: ' . $contentTypes[$extension]);
		readfile($file);
		return true;
	}

	return false;
}

require_once __DIR__ . '/../src/QuickInstall/Sandbox/bootstrap.php';
require_once __DIR__ . '/../src/QuickInstall/Sandbox/Web/Application.php';

(new QuickInstall\Sandbox\Web\Application(dirname(__DIR__)))->run();
