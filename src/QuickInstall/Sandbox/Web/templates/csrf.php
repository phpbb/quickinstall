<?php if (($csrfToken ?? '') !== ''): ?>
	<input type="hidden" name="qi_token" value="<?= $this->e($csrfToken) ?>">
<?php endif; ?>
