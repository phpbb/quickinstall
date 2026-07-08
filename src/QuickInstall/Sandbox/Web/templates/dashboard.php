<section class="status-strip" aria-label="Workspace summary">
	<?php foreach ($metrics as $metric): ?>
		<article class="metric">
			<span><?= $this->e($metric['label']) ?></span>
			<strong><?= $this->e($metric['value']) ?></strong>
			<small><?= $this->e($metric['detail']) ?></small>
			<em><?= $this->e($metric['description']) ?></em>
		</article>
	<?php endforeach; ?>
</section>

<?php if ($notice !== '' || $error !== ''): ?>
	<section class="toast-stack">
		<?php if ($notice !== ''): ?>
			<p class="notice"><?= $this->e($notice) ?></p>
		<?php endif; ?>
		<?php if ($error !== ''): ?>
			<p class="error"><?= $this->e($error) ?></p>
		<?php endif; ?>
	</section>
<?php endif; ?>

<section class="section" id="boards">
	<div class="section-head">
		<div>
			<p class="eyebrow">Runtime</p>
			<h2>Boards</h2>
			<p>Start, stop, seed, and inspect local Docker-backed phpBB installs.</p>
		</div>
	</div>
	<?php if (!$boards): ?>
		<div class="empty">
			<strong>No boards created</strong>
			<span>Create a board below to generate its compose runtime and workspace files.</span>
		</div>
	<?php endif; ?>
	<div class="board-grid">
		<?php foreach ($boards as $board): ?>
			<?php
			$name = (string) $board['name'];
			$status = (string) ($board['status'] ?? 'unknown');
			?>
			<article class="board card">
				<div class="card-head">
					<div>
						<h3><?= $this->e($name) ?></h3>
						<p class="url"><?= $this->e($board['url'] ?? '') ?></p>
					</div>
					<span class="badge status-<?= $this->e($status) ?>"><?= $this->e($status) ?></span>
				</div>
				<dl class="facts">
					<div><dt>phpBB</dt><dd><?= $this->e($board['phpbb'] ?? '') ?></dd></div>
					<div><dt>PHP</dt><dd><?= $this->e($board['php'] ?? '') ?></dd></div>
					<div><dt>Database</dt><dd><?= $this->e($board['db'] ?? '') ?></dd></div>
					<div><dt>Populate</dt><dd><?= $this->e($board['populate'] ?? 'none') ?></dd></div>
				</dl>
				<div class="actions compact">
					<a class="button secondary" href="<?= $this->e($board['url'] ?? '') ?>" target="_blank" rel="noreferrer">Open</a>
					<form method="post" data-ajax>
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="board_start">
						<input type="hidden" name="name" value="<?= $this->e($name) ?>">
						<button class="primary">Start</button>
					</form>
					<form method="post" data-ajax>
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="board_stop">
						<input type="hidden" name="name" value="<?= $this->e($name) ?>">
						<button class="secondary">Stop</button>
					</form>
					<form method="post" data-ajax data-confirm="<?= $this->e('Destroy board ' . $name . '? This removes its files, database, containers, and local image.') ?>">
						<?php require __DIR__ . '/csrf.php'; ?>
						<input type="hidden" name="action" value="board_destroy">
						<input type="hidden" name="name" value="<?= $this->e($name) ?>">
						<button class="danger">Destroy</button>
					</form>
				</div>
				<div class="subsection-title">Seed content</div>
				<form method="post" class="seed-form" data-ajax>
					<?php require __DIR__ . '/csrf.php'; ?>
					<input type="hidden" name="action" value="board_seed">
					<input type="hidden" name="name" value="<?= $this->e($name) ?>">
					<label class="field"><span>Preset</span><select name="preset"><?php foreach ($presetOptions as $option): ?><option value="<?= $this->e($option) ?>"><?= $this->e($option) ?></option><?php endforeach; ?></select></label>
					<label class="field"><span>Seed</span><input name="seed" value="1"></label>
					<label class="field"><span>Action</span><select name="seed_action"><?php foreach ($seedActionOptions as $option): ?><option value="<?= $this->e($option) ?>"><?= $this->e($option) ?></option><?php endforeach; ?></select></label>
					<button class="secondary">Run seed</button>
				</form>
				<div class="mounted-grid">
					<?php foreach ([['Extensions', 'ext_unmount', $board['mounted_extensions']], ['Styles', 'style_unmount', $board['mounted_styles']]] as $mountGroup): ?>
						<div class="mounted">
							<h4><?= $this->e($mountGroup[0]) ?></h4>
							<?php if (!$mountGroup[2]): ?>
								<p class="muted">None mounted</p>
							<?php endif; ?>
							<?php foreach ($mountGroup[2] as $item): ?>
								<form method="post" class="row" data-ajax>
									<?php require __DIR__ . '/csrf.php'; ?>
									<span><strong><?= $this->e($item['name']) ?></strong><small><?= $this->e($item['mode']) ?></small></span>
									<input type="hidden" name="action" value="<?= $this->e($mountGroup[1]) ?>">
									<input type="hidden" name="board" value="<?= $this->e($name) ?>">
									<input type="hidden" name="name" value="<?= $this->e($item['name']) ?>">
									<button class="secondary">Unmount</button>
								</form>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<section class="section" id="create">
	<div class="section-head">
		<div>
			<p class="eyebrow">Provision</p>
			<h2>Create board</h2>
			<p>Choose the phpBB source, runtime, port, and optional seed preset.</p>
		</div>
	</div>
	<form method="post" class="card settings-form" data-ajax>
		<?php require __DIR__ . '/csrf.php'; ?>
		<input type="hidden" name="action" value="board_create">
		<label class="field"><span>Name</span><input name="name" value="demo"></label>
		<label class="field"><span>phpBB</span><input name="phpbb" value="latest" list="phpbb-list"></label>
		<datalist id="phpbb-list"><?php foreach ($versionOptions as $option): ?><option value="<?= $this->e($option) ?>"><?php endforeach; ?></datalist>
		<label class="field"><span>DB</span><select name="db"><?php foreach ($dbOptions as $option): ?><option value="<?= $this->e($option) ?>"><?= $this->e($option) ?></option><?php endforeach; ?></select></label>
		<label class="field"><span>Port</span><input name="port" value="8080"></label>
		<label class="field"><span>Populate</span><select name="populate"><?php foreach ($populateOptions as $option): ?><option value="<?= $this->e($option) ?>"><?= $this->e($option) ?></option><?php endforeach; ?></select></label>
		<label class="toggle"><input type="checkbox" name="debug" value="1"><span></span><strong>Debug</strong></label>
		<label class="toggle"><input type="checkbox" name="replace" value="1"><span></span><strong>Replace existing</strong></label>
		<div class="form-actions"><button class="primary">Create board</button></div>
	</form>
</section>

<section class="section" id="customisations">
	<div class="section-head">
		<div>
			<p class="eyebrow">Mounts</p>
			<h2>Customisations</h2>
			<p>Bind or copy local extension and style work into a board.</p>
		</div>
	</div>
	<div class="split">
		<?php foreach ([['ext_mount', 'Mount extension'], ['style_mount', 'Mount style']] as $mountForm): ?>
			<form method="post" class="card settings-form stack-form" data-ajax>
				<?php require __DIR__ . '/csrf.php'; ?>
				<h3><?= $this->e($mountForm[1]) ?></h3>
				<input type="hidden" name="action" value="<?= $this->e($mountForm[0]) ?>">
				<label class="field"><span>Board</span><select name="board"><?php foreach ($boards as $board): ?><option value="<?= $this->e($board['name']) ?>"><?= $this->e($board['name']) ?></option><?php endforeach; ?></select></label>
				<label class="field"><span>Path under customisations/</span><input name="source" value=""></label>
				<label class="toggle"><input type="checkbox" name="copy" value="1"><span></span><strong>Copy</strong></label>
				<label class="toggle"><input type="checkbox" name="recursive" value="1"><span></span><strong>Recursive</strong></label>
				<label class="toggle"><input type="checkbox" name="allow_external" value="1"><span></span><strong>Allow external path</strong></label>
				<div class="form-actions"><button class="primary">Mount</button></div>
			</form>
		<?php endforeach; ?>
	</div>
</section>

<section class="section" id="sources">
	<div class="section-head">
		<div>
			<p class="eyebrow">Source cache</p>
			<h2>Sources</h2>
			<p>Fetch and inspect phpBB sources used by boards.</p>
		</div>
	</div>
	<form method="post" class="card settings-form compact-form" data-ajax>
		<?php require __DIR__ . '/csrf.php'; ?>
		<input type="hidden" name="action" value="source_fetch">
		<label class="field"><span>Version or branch</span><input name="version" value="latest"></label>
		<label class="field"><span>Git URL</span><input name="url" value=""></label>
		<label class="toggle"><input type="checkbox" name="git" value="1"><span></span><strong>Git source</strong></label>
		<label class="toggle"><input type="checkbox" name="allow_external" value="1"><span></span><strong>Allow external URL</strong></label>
		<div class="form-actions"><button class="primary">Fetch source</button></div>
	</form>
	<?php if (!$sources): ?>
		<div class="empty"><strong>No sources registered</strong><span>Fetching a source will populate the local .qi source cache.</span></div>
	<?php else: ?>
		<div class="table-wrap">
			<table>
				<thead><tr><th>Source</th><th>Version</th><th>Status</th><th>Downloaded</th><th>Used by</th><th>Path</th><th class="actions-column">Actions</th></tr></thead>
				<tbody>
					<?php foreach ($sources as $source): ?>
						<?php
						$sourceKey = (string) ($source['source_key'] ?? '');
						$usedBy = $source['used_by'] ?? [];
						$confirm = $usedBy
							? 'Remove source ' . $sourceKey . '? It is referenced by: ' . implode(', ', $usedBy) . '. Boards may need another source before they can be recreated.'
							: 'Remove source ' . $sourceKey . '? This deletes the cached source files.';
						?>
						<tr>
							<td><?= $this->e($sourceKey) ?></td>
							<td><?= $this->e($source['version'] ?? '') ?></td>
							<td><?= $this->e($source['status'] ?? '-') ?></td>
							<td><?= !empty($source['downloaded']) ? 'yes' : 'no' ?></td>
							<td><?= $this->e($usedBy ? implode(', ', $usedBy) : '-') ?></td>
							<td><?= $this->e($source['path'] ?? '') ?></td>
							<td>
								<form method="post" class="source-remove" data-ajax data-confirm="<?= $this->e($confirm) ?>">
									<?php require __DIR__ . '/csrf.php'; ?>
									<input type="hidden" name="action" value="source_remove">
									<input type="hidden" name="source" value="<?= $this->e($sourceKey) ?>">
									<?php if ($usedBy): ?><input type="hidden" name="force" value="1"><?php endif; ?>
									<button class="danger">Remove</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>

<section class="section" id="activity">
	<div class="section-head">
		<div>
			<p class="eyebrow">Command output</p>
			<h2>Activity log</h2>
			<p>Docker, Composer, and seed output from the latest action.</p>
		</div>
		<button type="button" class="secondary" data-clear-log>Clear</button>
	</div>
	<div class="terminal">
		<div class="terminal-bar"><span></span><span></span><span></span><strong>quickinstall sandbox</strong></div>
		<div class="progress" aria-hidden="true"><span></span></div>
		<pre class="activity-log" id="activity-log" tabindex="0"><?= $output === '' ? 'No command output yet.' : $this->e($output) ?></pre>
	</div>
</section>
