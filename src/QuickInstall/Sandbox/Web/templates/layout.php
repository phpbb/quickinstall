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
	<svg class="icon-sprite" aria-hidden="true">
		<symbol id="icon-boards" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></symbol>
		<symbol id="icon-create" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M8 12h8M12 8v8"></path></symbol>
		<symbol id="icon-customisations" viewBox="0 0 24 24"><path d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.611 1.611c-.47.47-1.087.706-1.704.706s-1.233-.235-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02.047-.317-.063-.64-.29-.867l-1.567-1.567A2.407 2.407 0 0 1 2 12c0-.617.235-1.234.704-1.704L4.29 8.71a.996.996 0 0 1 .877-.29c.493.073.84.504 1.02.968a2.5 2.5 0 1 0 3.237-3.237c-.464-.18-.894-.527-.967-1.02a.996.996 0 0 1 .29-.877l1.55-1.55A2.407 2.407 0 0 1 12 2c.617 0 1.234.235 1.704.704l1.567 1.567c.228.228.55.337.868.29.493-.073.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02z"></path></symbol>
		<symbol id="icon-sources" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><path d="m3.3 7 8.7 5 8.7-5M12 22V12"></path></symbol>
		<symbol id="icon-activity" viewBox="0 0 24 24"><path d="m4 17 6-6-6-6M12 19h8"></path></symbol>
		<symbol id="icon-doctor" viewBox="0 0 24 24"><path d="M11 2v2M5 2v2M5 3H4a2 2 0 0 0-2 2v3a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1M8 14a6 6 0 0 0 12 0v-3"></path><circle cx="20" cy="10" r="2"></circle></symbol>
		<symbol id="icon-init" viewBox="0 0 24 24"><path d="M12 10v6M9 13h6"></path><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2z"></path></symbol>
	</svg>
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
				<a href="#boards"><svg class="icon" aria-hidden="true"><use href="#icon-boards"></use></svg>Boards</a>
				<a href="#create"><svg class="icon" aria-hidden="true"><use href="#icon-create"></use></svg>Create board</a>
				<a href="#customisations"><svg class="icon" aria-hidden="true"><use href="#icon-customisations"></use></svg>Customisations</a>
				<a href="#sources"><svg class="icon" aria-hidden="true"><use href="#icon-sources"></use></svg>Sources</a>
				<a href="#activity"><svg class="icon" aria-hidden="true"><use href="#icon-activity"></use></svg>Activity log</a>
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
						<button class="secondary"><svg class="icon" aria-hidden="true"><use href="#icon-doctor"></use></svg>Run Doctor</button>
					</form>
					<form method="post" data-ajax>
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="init">
						<button class="primary"><svg class="icon" aria-hidden="true"><use href="#icon-init"></use></svg>Init workspace</button>
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
