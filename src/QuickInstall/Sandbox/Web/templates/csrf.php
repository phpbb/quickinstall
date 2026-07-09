<?php if (($csrfToken ?? '') !== ''): ?>
	<input type="hidden" name="qi_csrf_token" value="<?= $this->e($csrfToken) ?>">
<?php endif; ?>
