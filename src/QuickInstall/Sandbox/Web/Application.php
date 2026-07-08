<?php
/**
 *
 * QuickInstall sandbox web UI
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstall\Sandbox\Web;

use InvalidArgumentException;
use QuickInstall\Sandbox\BoardRefreshService;
use QuickInstall\Sandbox\BoardService;
use QuickInstall\Sandbox\BufferedOutput;
use QuickInstall\Sandbox\ExtensionManager;
use QuickInstall\Sandbox\Project;
use QuickInstall\Sandbox\SourceService;
use QuickInstall\Sandbox\StyleManager;
use RuntimeException;

class Application
{
	private Project $project;
	private BufferedOutput $output;
	private string $notice = '';
	private string $error = '';

	public function __construct(string $root)
	{
		$this->project = new Project($root);
		$this->output = new BufferedOutput();
	}

	public function run(): void
	{
		$this->assertLocalRequest();

		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$this->handlePost();
			if ($this->isAjax())
			{
				$this->renderJson();
				return;
			}
		}

		$this->notice = $this->notice ?: (string) ($_GET['notice'] ?? '');
		$this->error = $this->error ?: (string) ($_GET['error'] ?? '');
		$this->render();
	}

	private function assertLocalRequest(): void
	{
		$remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
		if (!in_array($remote, ['127.0.0.1', '::1'], true))
		{
			http_response_code(403);
			echo 'QuickInstall sandbox UI is local-only.';
			exit;
		}
	}

	private function handlePost(): void
	{
		try
		{
			$action = (string) ($_POST['action'] ?? '');
			switch ($action)
			{
				case 'init':
					$created = $this->project->init();
					$this->notice = $created ? 'Workspace initialized.' : 'Workspace already initialized.';
				break;

				case 'source_fetch':
					$version = $this->required('version');
					(new SourceService($this->project, $this->output))->fetch($version, $this->checked('git'), $this->optional('url'), $this->checked('allow_external'));
					$this->notice = "Fetched source: $version";
				break;

				case 'source_remove':
					$source = $this->required('source');
					$removed = (new SourceService($this->project))->remove($source, $this->checked('force'));
					$this->notice = "Removed source: {$removed['source']['source_key']}";
					if (!empty($removed['used_by']))
					{
						$this->notice .= ' (was referenced by ' . implode(', ', $removed['used_by']) . ')';
					}
				break;

				case 'board_create':
					$name = $this->required('name');
					$version = $this->optional('phpbb') ?: 'latest';
					$db = $this->optional('db') ?: 'mariadb';
					$db = $db === 'sqlite3' ? 'sqlite' : $db;
					$port = (int) ($this->optional('port') ?: '8080');
					$populate = $this->optional('populate') ?: 'none';
					$this->validateBoardCreateOptions($db, $port, $populate);
					(new BoardService($this->project, $this->output))->create($name, $version, $db, $port, $populate, $this->checked('debug'), $this->checked('replace'));
					$this->notice = "Created board: $name";
				break;

				case 'board_start':
					$name = $this->required('name');
					(new BoardService($this->project, $this->output))->start($name);
					$this->notice = "Started board: $name";
				break;

				case 'board_stop':
					$name = $this->required('name');
					(new BoardService($this->project, $this->output))->stop($name);
					$this->notice = "Stopped board: $name";
				break;

				case 'board_destroy':
					$name = $this->required('name');
					(new BoardService($this->project, $this->output))->destroy($name);
					$this->notice = "Destroyed board: $name";
				break;

				case 'board_seed':
					$name = $this->required('name');
					$preset = $this->optional('preset') ?: 'extension-dev';
					$seed = (int) ($this->optional('seed') ?: '1');
					$this->validatePreset($preset);
					if ($seed < 1)
					{
						throw new InvalidArgumentException('Seed must be a positive integer.');
					}
					(new BoardService($this->project, $this->output))->seed($name, $preset, $seed, $this->optional('seed_action') ?: 'seed');
					$this->notice = "Seed action completed for board: $name";
				break;

				case 'ext_mount':
					$this->mountCustomisation('extension', new ExtensionManager($this->project));
				break;

				case 'ext_unmount':
					$board = $this->required('board');
					$name = $this->required('name');
					$extensions = new ExtensionManager($this->project);
					$extensions->unmount($board, $name);
					(new BoardRefreshService($this->project, $this->output))->refreshIfRunning($board);
					$extensions->cleanupStaleTarget($board, $name);
					$this->notice = "Unmounted extension: $name";
				break;

				case 'style_mount':
					$this->mountCustomisation('style', new StyleManager($this->project));
				break;

				case 'style_unmount':
					$board = $this->required('board');
					$name = $this->required('name');
					$styles = new StyleManager($this->project);
					$styles->unmount($board, $name);
					(new BoardRefreshService($this->project, $this->output))->refreshIfRunning($board);
					$styles->cleanupStaleTarget($board, $name);
					$this->notice = "Unmounted style: $name";
				break;

				default:
					throw new InvalidArgumentException('Unknown action.');
			}
		}
		catch (InvalidArgumentException | RuntimeException $e)
		{
			$this->error = $e->getMessage();
		}
	}

	private function mountCustomisation(string $type, object $manager): void
	{
		$board = $this->required('board');
		$source = $this->required('source');
		if ($this->checked('copy') && $this->checked('recursive'))
		{
			throw new InvalidArgumentException('Recursive mount cannot be combined with copy mode.');
		}

		if ($this->checked('recursive'))
		{
			$mounted = 0;
			foreach ($manager->discover($source, $this->checked('allow_external')) as $path)
			{
				$manager->mount($board, $path, false, $this->checked('allow_external'));
				$mounted++;
			}
			if ($mounted > 0)
			{
				(new BoardRefreshService($this->project, $this->output))->refreshIfRunning($board);
			}
			$this->notice = "Mounted {$mounted} {$type}(s) on $board";
			return;
		}

		$mounted = $manager->mount($board, $source, $this->checked('copy'), $this->checked('allow_external'));
		(new BoardRefreshService($this->project, $this->output))->refreshIfRunning($board);
		$this->notice = "Mounted {$type}: {$mounted['name']}";
	}

	private function required(string $name): string
	{
		$value = trim((string) ($_POST[$name] ?? ''));
		if ($value === '')
		{
			throw new InvalidArgumentException("$name is required.");
		}

		return $value;
	}

	private function optional(string $name): ?string
	{
		$value = trim((string) ($_POST[$name] ?? ''));
		return $value === '' ? null : $value;
	}

	private function checked(string $name): bool
	{
		return !empty($_POST[$name]);
	}

	private function validateBoardCreateOptions(string $db, int $port, string $populate): void
	{
		if (!in_array($db, ['mariadb', 'mysql', 'postgres', 'sqlite'], true))
		{
			throw new InvalidArgumentException('DB must be one of: mariadb, mysql, postgres, sqlite.');
		}
		if ($port < 1 || $port > 65535)
		{
			throw new InvalidArgumentException('Port must be between 1 and 65535.');
		}
		if ($populate !== 'none')
		{
			$this->validatePreset($populate);
		}
		if ($db === 'sqlite' && $populate !== 'none')
		{
			throw new InvalidArgumentException('SQLite boards support populate none only.');
		}
	}

	private function validatePreset(string $preset): void
	{
		if (!in_array($preset, ['tiny', 'extension-dev', 'load-test', 'random'], true))
		{
			throw new InvalidArgumentException('Preset must be one of: tiny, extension-dev, load-test, random.');
		}
	}

	private function render(): void
	{
		if (!headers_sent())
		{
			header('Content-Type: text/html; charset=utf-8');
		}
		echo $this->renderTemplate('layout.php', [
			'dashboard' => $this->renderTemplate('dashboard.php', $this->viewData()),
		]);
	}

	private function renderJson(): void
	{
		if (!headers_sent())
		{
			header('Content-Type: application/json; charset=utf-8');
		}

		$html = $this->renderTemplate('dashboard.php', $this->viewData());

		echo json_encode([
			'ok' => $this->error === '',
			'notice' => $this->notice,
			'error' => $this->error,
			'output' => $this->output->all(),
			'html' => $html,
		], JSON_UNESCAPED_SLASHES);
	}

	private function viewData(): array
	{
		$boards = (new BoardService($this->project, $this->output))->list();
		$sources = (new SourceService($this->project))->list();
		$versions = (new SourceService($this->project))->supportedVersions();
		$extensions = new ExtensionManager($this->project);
		$styles = new StyleManager($this->project);

		$running = count(array_filter($boards, static function ($board) {
			return ($board['status'] ?? '') === 'running';
		}));
		$mountedExtensions = 0;
		$mountedStyles = 0;
		foreach ($boards as $board)
		{
			$mountedExtensions += count($board['extensions'] ?? []);
			$mountedStyles += count($board['styles'] ?? []);
		}

		$viewBoards = [];
		foreach ($boards as $board)
		{
			$name = (string) $board['name'];
			$board['mounted_extensions'] = $extensions->list($name);
			$board['mounted_styles'] = $styles->list($name);
			$viewBoards[] = $board;
		}

		return [
			'notice' => $this->notice,
			'error' => $this->error,
			'output' => $this->output->all(),
			'metrics' => [
				['label' => 'Boards', 'value' => (string) count($boards), 'detail' => $running . ' running', 'description' => 'Runtime definitions'],
				['label' => 'Sources', 'value' => (string) count($sources), 'detail' => count(array_filter($sources, static function ($source) {
					return !empty($source['downloaded']);
				})) . ' downloaded', 'description' => 'phpBB cache'],
				['label' => 'Extensions', 'value' => (string) $mountedExtensions, 'detail' => 'mounted', 'description' => 'Board mounts'],
				['label' => 'Styles', 'value' => (string) $mountedStyles, 'detail' => 'mounted', 'description' => 'Board mounts'],
			],
			'boards' => $viewBoards,
			'sources' => $sources,
			'versionOptions' => ['latest', '3.3', '3.3.x', '3.2', '3.2.x', '4.0.x', 'master'],
			'dbOptions' => ['mariadb', 'mysql', 'postgres', 'sqlite'],
			'populateOptions' => ['none', 'tiny', 'extension-dev', 'load-test', 'random'],
			'presetOptions' => ['tiny', 'extension-dev', 'load-test', 'random'],
			'seedActionOptions' => ['seed', 'replace', 'reset'],
		];
	}

	private function renderTemplate(string $template, array $data = []): string
	{
		$path = __DIR__ . '/templates/' . $template;
		if (!is_file($path))
		{
			throw new RuntimeException("Missing web template: $template");
		}

		extract($data, EXTR_SKIP);
		ob_start();
		require $path;
		return (string) ob_get_clean();
	}

	private function e($value): string
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function isAjax(): bool
	{
		return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
	}
}
