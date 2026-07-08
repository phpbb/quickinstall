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
		echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
		echo '<title>QuickInstall Sandbox</title>';
		echo '<style>' . $this->css() . '</style></head><body>';
		echo '<div class="app">';
		echo '<aside class="sidebar"><div class="brand"><span class="brand-mark">QI</span><div><strong>QuickInstall</strong><small>Docker sandbox</small></div></div><nav aria-label="Sandbox navigation"><a href="#boards">Boards</a><a href="#create">Create board</a><a href="#customisations">Customisations</a><a href="#sources">Sources</a><a href="#activity">Activity log</a></nav></aside>';
		echo '<main class="main">';
		echo '<header class="topbar"><div><p class="eyebrow">Local admin UI</p><h1>Sandbox workspace</h1><p class="lede">Manage disposable phpBB boards backed by the same Docker services as the CLI.</p></div><form method="post" data-ajax><input type="hidden" name="action" value="init"><button class="primary">Init workspace</button></form></header>';
		echo '<div id="dashboard" class="content">';
		$this->renderDashboard();
		echo '</div></main></div>';
		echo '<div class="busy" id="busy" role="status" aria-live="polite"><div class="spinner"></div><span>Processing sandbox action...</span></div>';
		echo '<script>' . $this->js() . '</script>';
		echo '</body></html>';
	}

	private function renderJson(): void
	{
		if (!headers_sent())
		{
			header('Content-Type: application/json; charset=utf-8');
		}

		ob_start();
		$this->renderDashboard();
		$html = (string) ob_get_clean();

		echo json_encode([
			'ok' => $this->error === '',
			'notice' => $this->notice,
			'error' => $this->error,
			'output' => $this->output->all(),
			'html' => $html,
		], JSON_UNESCAPED_SLASHES);
	}

	private function renderDashboard(): void
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

		echo '<section class="status-strip" aria-label="Workspace summary">';
		$this->metric('Boards', (string) count($boards), $running . ' running', 'Runtime definitions');
		$this->metric('Sources', (string) count($sources), count(array_filter($sources, static function ($source) { return !empty($source['downloaded']); })) . ' downloaded', 'phpBB cache');
		$this->metric('Extensions', (string) $mountedExtensions, 'mounted', 'Board mounts');
		$this->metric('Styles', (string) $mountedStyles, 'mounted', 'Board mounts');
		echo '</section>';
		if ($this->notice !== '' || $this->error !== '')
		{
			echo '<section class="toast-stack">';
			if ($this->notice !== '')
			{
				echo '<p class="notice">' . $this->e($this->notice) . '</p>';
			}
			if ($this->error !== '')
			{
				echo '<p class="error">' . $this->e($this->error) . '</p>';
			}
			echo '</section>';
		}
		$this->renderBoards($boards, $extensions, $styles);
		$this->renderCreateBoard($versions);
		$this->renderCustomisations($boards);
		$this->renderSources($sources);
		$this->renderActivity();
	}

	private function renderBoards(array $boards, ExtensionManager $extensions, StyleManager $styles): void
	{
		echo '<section class="section" id="boards"><div class="section-head"><div><p class="eyebrow">Runtime</p><h2>Boards</h2><p>Start, stop, seed, and inspect local Docker-backed phpBB installs.</p></div></div>';
		if (!$boards)
		{
			echo '<div class="empty"><strong>No boards created</strong><span>Create a board below to generate its compose runtime and workspace files.</span></div>';
		}
		echo '<div class="board-grid">';
		foreach ($boards as $board)
		{
			$name = (string) $board['name'];
			$status = (string) ($board['status'] ?? 'unknown');
			echo '<article class="board card"><div class="card-head"><div><h3>' . $this->e($name) . '</h3><p class="url">' . $this->e($board['url']) . '</p></div><span class="badge status-' . $this->e($status) . '">' . $this->e($status) . '</span></div>';
			echo '<dl class="facts"><div><dt>phpBB</dt><dd>' . $this->e($board['phpbb']) . '</dd></div><div><dt>PHP</dt><dd>' . $this->e($board['php']) . '</dd></div><div><dt>Database</dt><dd>' . $this->e($board['db']) . '</dd></div><div><dt>Populate</dt><dd>' . $this->e($board['populate'] ?? 'none') . '</dd></div></dl>';
			echo '<div class="actions compact">';
			echo '<a class="button secondary" href="' . $this->e($board['url']) . '" target="_blank" rel="noreferrer">Open</a>';
			$this->actionButton('board_start', $name, 'Start', 'primary');
			$this->actionButton('board_stop', $name, 'Stop', 'secondary');
			$this->actionButton('board_destroy', $name, 'Destroy', 'danger', 'Destroy board ' . $name . '? This removes its files, database, containers, and local image.');
			echo '</div>';
			echo '<div class="subsection-title">Seed content</div>';
			$this->renderSeedForm($name);
			echo '<div class="mounted-grid">';
			$this->renderMounted('Extensions', 'ext_unmount', $name, $extensions->list($name));
			$this->renderMounted('Styles', 'style_unmount', $name, $styles->list($name));
			echo '</div>';
			echo '</article>';
		}
		echo '</div>';
		echo '</section>';
	}

	private function renderCreateBoard(array $versions): void
	{
		echo '<section class="section" id="create"><div class="section-head"><div><p class="eyebrow">Provision</p><h2>Create board</h2><p>Choose the phpBB source, runtime, port, and optional seed preset.</p></div></div><form method="post" class="card settings-form" data-ajax>';
		echo '<input type="hidden" name="action" value="board_create">';
		$this->input('name', 'Name', 'demo');
		$this->input('phpbb', 'phpBB', 'latest', $this->versionOptions($versions));
		$this->select('db', 'DB', ['mariadb', 'mysql', 'postgres', 'sqlite']);
		$this->input('port', 'Port', '8080');
		$this->select('populate', 'Populate', ['none', 'tiny', 'extension-dev', 'load-test', 'random']);
		$this->checkbox('debug', 'Debug');
		$this->checkbox('replace', 'Replace existing');
		echo '<div class="form-actions"><button class="primary">Create board</button></div></form></section>';
	}

	private function renderSources(array $sources): void
	{
		echo '<section class="section" id="sources"><div class="section-head"><div><p class="eyebrow">Source cache</p><h2>Sources</h2><p>Fetch and inspect phpBB sources used by boards.</p></div></div><form method="post" class="card settings-form compact-form" data-ajax>';
		echo '<input type="hidden" name="action" value="source_fetch">';
		$this->input('version', 'Version or branch', 'latest');
		$this->input('url', 'Git URL');
		$this->checkbox('git', 'Git source');
		$this->checkbox('allow_external', 'Allow external URL');
		echo '<div class="form-actions"><button class="primary">Fetch source</button></div></form>';
		if (!$sources)
		{
			echo '<div class="empty"><strong>No sources registered</strong><span>Fetching a source will populate the local .qi source cache.</span></div>';
		}
		else
		{
			echo '<div class="table-wrap"><table><thead><tr><th>Source</th><th>Version</th><th>Status</th><th>Downloaded</th><th>Used by</th><th>Path</th><th class="actions-column">Actions</th></tr></thead><tbody>';
			foreach ($sources as $source)
			{
				$sourceKey = (string) ($source['source_key'] ?? '');
				$usedBy = $source['used_by'] ?? [];
				echo '<tr><td>' . $this->e($sourceKey) . '</td><td>' . $this->e($source['version'] ?? '') . '</td><td>' . $this->e($source['status'] ?? '-') . '</td><td>' . (!empty($source['downloaded']) ? 'yes' : 'no') . '</td><td>' . $this->e($usedBy ? implode(', ', $usedBy) : '-') . '</td><td>' . $this->e($source['path'] ?? '') . '</td><td>';
				$this->sourceRemoveForm($sourceKey, $usedBy);
				echo '</td></tr>';
			}
			echo '</tbody></table></div>';
		}
		echo '</section>';
	}

	private function sourceRemoveForm(string $sourceKey, array $usedBy): void
	{
		$confirm = $usedBy
			? 'Remove source ' . $sourceKey . '? It is referenced by: ' . implode(', ', $usedBy) . '. Boards may need another source before they can be recreated.'
			: 'Remove source ' . $sourceKey . '? This deletes the cached source files.';
		echo '<form method="post" class="source-remove" data-ajax data-confirm="' . $this->e($confirm) . '">';
		echo '<input type="hidden" name="action" value="source_remove"><input type="hidden" name="source" value="' . $this->e($sourceKey) . '">';
		if ($usedBy)
		{
			echo '<input type="hidden" name="force" value="1">';
		}
		echo '<button class="danger">Remove</button></form>';
	}

	private function renderCustomisations(array $boards): void
	{
		echo '<section class="section" id="customisations"><div class="section-head"><div><p class="eyebrow">Mounts</p><h2>Customisations</h2><p>Bind or copy local extension and style work into a board.</p></div></div><div class="split">';
		$this->renderMountForm('ext_mount', 'Mount extension', $boards);
		$this->renderMountForm('style_mount', 'Mount style', $boards);
		echo '</div></section>';
	}

	private function renderMountForm(string $action, string $title, array $boards): void
	{
		echo '<form method="post" class="card settings-form stack-form" data-ajax><h3>' . $this->e($title) . '</h3>';
		echo '<input type="hidden" name="action" value="' . $this->e($action) . '">';
		$this->boardSelect($boards);
		$this->input('source', 'Path under customisations/');
		$this->checkbox('copy', 'Copy');
		$this->checkbox('recursive', 'Recursive');
		$this->checkbox('allow_external', 'Allow external path');
		echo '<div class="form-actions"><button class="primary">Mount</button></div></form>';
	}

	private function renderSeedForm(string $name): void
	{
		echo '<form method="post" class="seed-form" data-ajax><input type="hidden" name="action" value="board_seed"><input type="hidden" name="name" value="' . $this->e($name) . '">';
		$this->select('preset', 'Preset', ['tiny', 'extension-dev', 'load-test', 'random']);
		$this->input('seed', 'Seed', '1');
		$this->select('seed_action', 'Action', ['seed', 'replace', 'reset']);
		echo '<button class="secondary">Run seed</button></form>';
	}

	private function renderMounted(string $title, string $action, string $board, array $items): void
	{
		echo '<div class="mounted"><h4>' . $this->e($title) . '</h4>';
		if (!$items)
		{
			echo '<p class="muted">None mounted</p></div>';
			return;
		}
		foreach ($items as $item)
		{
			echo '<form method="post" class="row" data-ajax><span><strong>' . $this->e($item['name']) . '</strong><small>' . $this->e($item['mode']) . '</small></span>';
			echo '<input type="hidden" name="action" value="' . $this->e($action) . '"><input type="hidden" name="board" value="' . $this->e($board) . '"><input type="hidden" name="name" value="' . $this->e($item['name']) . '">';
			echo '<button class="secondary">Unmount</button></form>';
		}
		echo '</div>';
	}

	private function actionButton(string $action, string $name, string $label, string $class = '', string $confirm = ''): void
	{
		$confirmAttr = $confirm === '' ? '' : ' data-confirm="' . $this->e($confirm) . '"';
		echo '<form method="post" data-ajax' . $confirmAttr . '><input type="hidden" name="action" value="' . $this->e($action) . '"><input type="hidden" name="name" value="' . $this->e($name) . '"><button class="' . $this->e($class) . '">' . $this->e($label) . '</button></form>';
	}

	private function renderActivity(): void
	{
		$output = $this->output->all();
		echo '<section class="section" id="activity"><div class="section-head"><div><p class="eyebrow">Command output</p><h2>Activity log</h2><p>Docker, Composer, and seed output from the latest action.</p></div><button type="button" class="secondary" data-clear-log>Clear</button></div>';
		echo '<div class="terminal"><div class="terminal-bar"><span></span><span></span><span></span><strong>quickinstall sandbox</strong></div><div class="progress" aria-hidden="true"><span></span></div><pre class="activity-log" id="activity-log" tabindex="0">' . ($output === '' ? 'No command output yet.' : $this->e($output)) . '</pre></div></section>';
	}

	private function metric(string $label, string $value, string $detail, string $description): void
	{
		echo '<article class="metric"><span>' . $this->e($label) . '</span><strong>' . $this->e($value) . '</strong><small>' . $this->e($detail) . '</small><em>' . $this->e($description) . '</em></article>';
	}

	private function boardSelect(array $boards): void
	{
		echo '<label class="field"><span>Board</span><select name="board">';
		foreach ($boards as $board)
		{
			echo '<option value="' . $this->e($board['name']) . '">' . $this->e($board['name']) . '</option>';
		}
		echo '</select></label>';
	}

	private function input(string $name, string $label, string $value = '', array $datalist = []): void
	{
		$list = $datalist ? ' list="' . $this->e($name) . '-list"' : '';
		echo '<label class="field"><span>' . $this->e($label) . '</span><input name="' . $this->e($name) . '" value="' . $this->e($value) . '"' . $list . '></label>';
		if ($datalist)
		{
			echo '<datalist id="' . $this->e($name) . '-list">';
			foreach ($datalist as $option)
			{
				echo '<option value="' . $this->e($option) . '">';
			}
			echo '</datalist>';
		}
	}

	private function select(string $name, string $label, array $options): void
	{
		echo '<label class="field"><span>' . $this->e($label) . '</span><select name="' . $this->e($name) . '">';
		foreach ($options as $option)
		{
			echo '<option value="' . $this->e($option) . '">' . $this->e($option) . '</option>';
		}
		echo '</select></label>';
	}

	private function checkbox(string $name, string $label): void
	{
		echo '<label class="toggle"><input type="checkbox" name="' . $this->e($name) . '" value="1"><span></span><strong>' . $this->e($label) . '</strong></label>';
	}

	private function versionOptions(array $versions): array
	{
		return ['latest', '3.3', '3.3.x', '3.2', '3.2.x', '4.0.x', 'master'];
	}

	private function e(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function css(): string
	{
		return ':root{--canvas:#f6f8fa;--panel:#fff;--text:#24292f;--muted:#57606a;--subtle:#6e7781;--border:#d0d7de;--border-muted:#d8dee4;--row:#f6f8fa;--row-hover:#f3f4f6;--accent:#0b76b7;--accent-hover:#075f94;--success:#1a7f37;--success-bg:#dafbe1;--warning:#9a6700;--warning-bg:#fff8c5;--danger:#cf222e;--danger-bg:#ffebe9;--terminal:#0d1117;--radius:6px;--shadow:0 1px 0 rgba(27,31,36,.04);--focus:0 0 0 3px rgba(11,118,183,.24)}*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;background:var(--canvas);color:var(--text)}a{color:var(--accent);text-decoration:none}a:hover{text-decoration:underline}h1,h2,h3,h4,p{margin:0}h1{font-size:26px;line-height:1.25;font-weight:650}h2{font-size:20px;line-height:1.3;font-weight:650}h3{font-size:16px;line-height:1.4;font-weight:650}button,.button{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:32px;padding:5px 12px;border:1px solid rgba(27,31,36,.15);border-radius:var(--radius);background:#f6f8fa;color:var(--text);font:600 14px/20px inherit;box-shadow:var(--shadow);cursor:pointer;text-decoration:none;white-space:nowrap}button:hover,.button:hover{background:#f3f4f6;text-decoration:none}button:focus-visible,.button:focus-visible,input:focus,select:focus,a:focus-visible{outline:none;box-shadow:var(--focus);border-color:var(--accent)}button:disabled{opacity:.65;cursor:wait}.primary{background:var(--accent);border-color:rgba(27,31,36,.15);color:#fff}.primary:hover{background:var(--accent-hover)}.secondary{background:#f6f8fa;color:var(--text)}.danger{background:#fff;color:var(--danger);border-color:rgba(207,34,46,.35)}.danger:hover{background:var(--danger-bg)}.app{display:grid;grid-template-columns:264px minmax(0,1fr);min-height:100vh}.sidebar{position:sticky;top:0;height:100vh;padding:24px 16px;background:#fff;border-right:1px solid var(--border-muted)}.brand{display:flex;align-items:center;gap:12px;padding:0 8px 20px;border-bottom:1px solid var(--border-muted);margin-bottom:16px}.brand-mark{display:grid;place-items:center;width:32px;height:32px;border-radius:var(--radius);background:#24292f;color:#fff;font-size:12px;font-weight:700;letter-spacing:.04em}.brand strong{display:block;font-size:15px}.brand small{display:block;color:var(--muted);font-size:12px}.sidebar nav{display:grid;gap:2px}.sidebar a{display:block;padding:7px 10px;border-radius:var(--radius);color:var(--text);font-weight:500}.sidebar a:hover{background:var(--row);text-decoration:none}.main{min-width:0}.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;padding:24px 32px;background:#fff;border-bottom:1px solid var(--border-muted)}.lede{margin-top:4px;color:var(--muted);max-width:680px}.content{max-width:1220px;margin:0 auto;padding-bottom:40px}.eyebrow{margin-bottom:3px;color:var(--subtle);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em}.status-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;padding:24px 32px 0}.metric,.card{background:var(--panel);border:1px solid var(--border-muted);border-radius:var(--radius);box-shadow:var(--shadow)}.metric{padding:14px 16px}.metric span{display:block;color:var(--muted);font-size:12px;font-weight:600}.metric strong{display:block;margin-top:4px;font-size:24px;line-height:1.2}.metric small{display:block;margin-top:2px;color:var(--text);font-weight:600}.metric em{display:block;margin-top:8px;color:var(--subtle);font-size:12px;font-style:normal}.toast-stack{display:grid;gap:8px;padding:16px 32px 0}.notice,.error{padding:10px 12px;border:1px solid;border-radius:var(--radius);font-weight:500}.notice{background:var(--success-bg);border-color:#aceebb;color:#116329}.error{background:var(--danger-bg);border-color:#ffcecb;color:#82071e}.section{padding:24px 32px 0}.section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding-bottom:8px;border-bottom:1px solid var(--border-muted);margin-bottom:16px}.section-head p:not(.eyebrow){margin-top:4px;color:var(--muted);max-width:760px}.board-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(360px,1fr));gap:16px}.board{padding:0}.card-head{display:flex;justify-content:space-between;gap:16px;padding:16px;border-bottom:1px solid var(--border-muted)}.card-head .url{margin-top:3px;color:var(--muted);font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px}.badge{display:inline-flex;align-items:center;height:22px;padding:0 8px;border-radius:999px;background:#eaeef2;color:var(--muted);font-size:12px;font-weight:600;text-transform:capitalize}.status-running{background:var(--success-bg);color:var(--success)}.status-error,.status-missing{background:var(--danger-bg);color:var(--danger)}.status-partial{background:var(--warning-bg);color:var(--warning)}.facts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));margin:0;padding:12px 16px;gap:0;border-bottom:1px solid var(--border-muted)}.facts div{min-width:0;padding-right:12px}.facts dt{color:var(--subtle);font-size:12px}.facts dd{margin:2px 0 0;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.compact{padding:12px 16px;border-bottom:1px solid var(--border-muted)}.subsection-title{padding:12px 16px 0;color:var(--muted);font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}.seed-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;align-items:end;padding:10px 16px 16px;border-bottom:1px solid var(--border-muted)}.mounted-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0}.mounted{padding:14px 16px}.mounted + .mounted{border-left:1px solid var(--border-muted)}.mounted h4{margin-bottom:8px;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.04em}.row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-top:1px solid var(--border-muted)}.row:first-of-type{border-top:0}.row span{min-width:0}.row strong,.row small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.row small{color:var(--subtle);font-size:12px}.settings-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;padding:16px}.settings-form h3{grid-column:1/-1}.compact-form{grid-template-columns:repeat(4,minmax(0,1fr))}.stack-form{grid-template-columns:1fr 1fr}.split{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.form-actions{grid-column:1/-1;display:flex;justify-content:flex-end;padding-top:4px}.field{display:grid;gap:6px;color:var(--text);font-weight:600}.field span{font-size:13px}.field input,.field select{width:100%;height:32px;border:1px solid var(--border);border-radius:var(--radius);background:#fff;color:var(--text);padding:5px 8px;font:14px/20px inherit}.field input[name=source],td:last-child{font-family:ui-monospace,SFMono-Regular,SFMono,Consolas,"Liberation Mono",Menlo,monospace;font-size:12px}.toggle{display:grid;grid-template-columns:36px minmax(0,1fr);gap:10px;align-items:center;align-self:end;min-height:32px;color:var(--text);font-weight:600}.toggle input{position:absolute;opacity:0;pointer-events:none}.toggle span{position:relative;width:36px;height:20px;border:1px solid var(--border);border-radius:999px;background:#eaeef2;transition:background .12s ease}.toggle span:before{content:"";position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(27,31,36,.25);transition:transform .12s ease}.toggle input:checked + span{background:var(--accent);border-color:var(--accent)}.toggle input:checked + span:before{transform:translateX(16px)}.toggle input:focus-visible + span{box-shadow:var(--focus)}.table-wrap{overflow:auto;border:1px solid var(--border-muted);border-radius:var(--radius);background:#fff}table{width:100%;min-width:920px;border-collapse:collapse}th,td{padding:9px 12px;border-bottom:1px solid var(--border-muted);text-align:left;vertical-align:top}th{background:var(--row);color:var(--muted);font-size:12px;font-weight:600}tr:hover td{background:#fbfcfd}tr:last-child td{border-bottom:0}.actions-column{width:1%;white-space:nowrap}.source-remove{display:flex;justify-content:flex-end}.empty{display:grid;gap:3px;padding:18px;border:1px dashed var(--border);border-radius:var(--radius);background:#fff;color:var(--muted)}.empty strong{color:var(--text)}.muted{color:var(--subtle)}.terminal{overflow:hidden;border:1px solid #30363d;border-radius:var(--radius);background:var(--terminal);box-shadow:var(--shadow)}.terminal-bar{display:flex;align-items:center;gap:7px;height:34px;padding:0 12px;border-bottom:1px solid #30363d;background:#161b22;color:#8b949e;font-size:12px}.terminal-bar span{width:10px;height:10px;border-radius:50%;background:#484f58}.terminal-bar span:first-child{background:#f85149}.terminal-bar span:nth-child(2){background:#d29922}.terminal-bar span:nth-child(3){background:#3fb950}.terminal-bar strong{margin-left:6px;font-weight:600}.progress{display:none;height:2px;background:#21262d;overflow:hidden}.progress span{display:block;width:38%;height:100%;background:var(--accent);animation:progress 1.1s ease-in-out infinite}.is-processing .progress{display:block}.activity-log{height:300px;overflow:auto;margin:0;padding:14px;color:#e6edf3;background:var(--terminal);font:12px/1.55 ui-monospace,SFMono-Regular,SFMono,Consolas,"Liberation Mono",Menlo,monospace;white-space:pre-wrap}.busy{position:fixed;right:20px;bottom:20px;display:none;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius);background:#fff;color:var(--text);box-shadow:0 8px 24px rgba(140,149,159,.25);z-index:20}.busy.is-active{display:flex}.spinner{width:16px;height:16px;border:2px solid #d0d7de;border-top-color:var(--accent);border-radius:50%;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}@keyframes progress{0%{transform:translateX(-110%)}100%{transform:translateX(285%)}}@media(max-width:1080px){.app{grid-template-columns:1fr}.sidebar{position:relative;height:auto;border-right:0;border-bottom:1px solid var(--border-muted)}.sidebar nav{display:flex;overflow:auto}.sidebar a{white-space:nowrap}.status-strip{grid-template-columns:repeat(2,minmax(0,1fr))}.split{grid-template-columns:1fr}}@media(max-width:760px){.topbar{flex-direction:column;padding:20px}.content{max-width:none}.section,.status-strip,.toast-stack{padding-left:20px;padding-right:20px}.board-grid{grid-template-columns:1fr}.settings-form,.compact-form,.stack-form,.seed-form{grid-template-columns:1fr}.facts{grid-template-columns:repeat(2,minmax(0,1fr));row-gap:10px}.mounted-grid{grid-template-columns:1fr}.mounted + .mounted{border-left:0;border-top:1px solid var(--border-muted)}}';
	}

	private function js(): string
	{
		return "const dashboard=document.getElementById('dashboard');const busy=document.getElementById('busy');function setProcessing(active){busy.classList.toggle('is-active',active);document.documentElement.classList.toggle('is-processing',active)}function bindAjax(){document.querySelectorAll('form[data-ajax]').forEach(form=>{if(form.dataset.bound)return;form.dataset.bound='1';form.addEventListener('submit',async event=>{event.preventDefault();if(form.dataset.confirm&&!window.confirm(form.dataset.confirm))return;const submitter=event.submitter;const original=submitter?submitter.textContent:'';if(submitter){submitter.disabled=true;submitter.textContent='Working...'}setProcessing(true);try{const response=await fetch(location.href,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});const data=await response.json();dashboard.innerHTML=data.html;bindAjax();scrollLog();}catch(error){alert('Request failed: '+error.message);}finally{setProcessing(false);if(submitter){submitter.disabled=false;submitter.textContent=original;}}});});document.querySelectorAll('[data-clear-log]').forEach(button=>{if(button.dataset.bound)return;button.dataset.bound='1';button.addEventListener('click',()=>{const log=document.getElementById('activity-log');if(log)log.textContent='No command output yet.';});});}function scrollLog(){const log=document.getElementById('activity-log');if(log)log.scrollTop=log.scrollHeight;}bindAjax();scrollLog();";
	}

	private function isAjax(): bool
	{
		return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
	}
}
