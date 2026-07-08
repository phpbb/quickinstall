<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>QuickInstall Sandbox</title>
	<link rel="stylesheet" href="/assets/sandbox-ui.css">
	<script src="/assets/sandbox-ui.js" defer></script>
</head>
<body>
	<div class="app">
		<aside class="sidebar">
			<div class="brand">
				<span class="brand-mark">QI</span>
				<div>
					<strong>QuickInstall</strong>
					<small>Docker sandbox</small>
				</div>
			</div>
			<nav aria-label="Sandbox navigation">
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
					<p class="eyebrow">Local admin UI</p>
					<h1>Sandbox workspace</h1>
					<p class="lede">Manage disposable phpBB boards backed by the same Docker services as the CLI.</p>
				</div>
				<form method="post" data-ajax>
					<?php require __DIR__ . '/csrf.php'; ?>
					<input type="hidden" name="action" value="init">
					<button class="primary">Init workspace</button>
				</form>
			</header>
			<div id="dashboard" class="content">
				<?= $dashboard ?>
			</div>
		</main>
	</div>
	<div class="busy" id="busy" role="status" aria-live="polite">
		<div class="spinner"></div>
		<span>Processing sandbox action...</span>
	</div>
</body>
</html>
