<?php
/**
 * @var \QuickInstall\Sandbox\Web\Application $this
 * @var string $dashboard
 * @var string $csrfToken
 * @var string $quickInstallVersion
 */
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>QuickInstall Dashboard</title>
	<link rel="stylesheet" href="/assets/sandbox-ui.css">
	<script src="/assets/sandbox-ui.js" defer></script>
</head>
<body>
	<div class="app">
		<aside class="sidebar">
			<div class="brand">
				<span class="brand-mark">QI</span>
				<div class="brand-lockup">
					<span class="brand-logo" role="img" aria-label="phpBB"></span>
					<small>QuickInstall + Docker</small>
				</div>
			</div>
			<nav aria-label="QuickInstall navigation">
				<a href="#boards">Boards</a>
				<a href="#create">Create board</a>
				<a href="#customisations">Customisations</a>
				<a href="#sources">Sources</a>
				<a href="#activity">Activity log</a>
			</nav>
		</aside>
		<main class="main">
			<header class="topbar">
				<div>
					<h1>QuickInstall Dashboard</h1>
					<p class="lede">Manage disposable phpBB boards backed by the same Docker services as the CLI.</p>
				</div>
				<div class="actions">
					<form method="post" data-ajax>
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="doctor">
						<button class="secondary">Run Doctor</button>
					</form>
					<form method="post" data-ajax>
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="init">
						<button class="primary">Init workspace</button>
					</form>
				</div>
			</header>
			<div id="dashboard" class="content">
				<?= $dashboard ?>
			</div>
			<footer class="footer">
				<a href="https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/" target="_blank" rel="noreferrer">phpBB<small><sup>&reg;</sup></small> QuickInstall <?= $this->escape($quickInstallVersion) ?></a>
				<a href="https://github.com/phpbb/quickinstall/blob/master/docs/sandbox-cli.md" target="_blank" rel="noreferrer">Docs</a>
				<a href="https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support" target="_blank" rel="noreferrer">Support</a>
				<a href="https://github.com/phpbb/quickinstall/issues" target="_blank" rel="noreferrer">Issues</a>
				<a href="https://www.phpbb.com/" target="_blank" rel="noreferrer">&copy; phpBB Limited</a>
			</footer>
		</main>
	</div>
	<div class="busy" id="busy" role="status" aria-live="polite">
		<div class="spinner"></div>
		<span>Processing QuickInstall action...</span>
	</div>
</body>
</html>
