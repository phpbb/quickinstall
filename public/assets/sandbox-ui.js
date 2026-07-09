const dashboard = document.getElementById('dashboard');
const busy = document.getElementById('busy');

function setProcessing(active) {
	busy.classList.toggle('is-active', active);
	document.documentElement.classList.toggle('is-processing', active);
}

function bindAjax() {
	document.querySelectorAll('form[data-ajax]').forEach((form) => {
		if (form.dataset.bound) {
			return;
		}

		form.dataset.bound = '1';
		form.addEventListener('submit', async(event) => {
			event.preventDefault();
			if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
				return;
			}

			const context = actionContext(form);
			const submitter = event.submitter;
			const original = submitter ? submitter.textContent : '';
			if (submitter) {
				submitter.disabled = true;
				submitter.textContent = 'Working...';
			}

			setProcessing(true);
			try {
				const response = await fetch(location.href, {
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
					},
					body: new FormData(form),
				});
				const data = await response.json();
				dashboard.innerHTML = data.html;
				bindAjax();
				showActionResult(data, context);
				scrollLog();
			} catch (error) {
				alert('Request failed: ' + error.message);
			} finally {
				setProcessing(false);
				if (submitter) {
					submitter.disabled = false;
					submitter.textContent = original;
				}
			}
		});
	});

	document.querySelectorAll('[data-clear-log]').forEach((button) => {
		if (button.dataset.bound) {
			return;
		}

		button.dataset.bound = '1';
		button.addEventListener('click', () => {
			const log = document.getElementById('activity-log');
			if (log) {
				log.textContent = 'No command output yet.';
			}
		});
	});
}

function actionContext(form) {
	const formData = new FormData(form);
	const section = form.closest('section');
	const board = form.closest('[data-board]');

	return {
		action: formData.get('action') || '',
		board: board ? board.dataset.board : '',
		section: section ? section.id : '',
	};
}

function showActionResult(data, context) {
	const globalToast = dashboard.querySelector('.toast-stack');
	if (globalToast) {
		globalToast.remove();
	}

	const message = data.error || data.notice || '';
	if (!message) {
		return;
	}

	const result = document.createElement('div');
	result.className = 'action-result ' + (data.error ? 'error' : 'notice');
	result.setAttribute('role', data.error ? 'alert' : 'status');
	result.textContent = message;

	let target = null;
	if (context.board) {
		target = dashboard.querySelector('[data-board="' + cssEscape(context.board) + '"] .card-head');
	}
	if (!target && context.section) {
		target = dashboard.querySelector('#' + cssEscape(context.section) + ' .section-head');
	}
	if (!target) {
		target = dashboard.querySelector('.status-strip');
	}

	if (target) {
		target.insertAdjacentElement('afterend', result);
		window.setTimeout(() => {
			result.classList.add('is-dismissing');
			window.setTimeout(() => result.remove(), 180);
		}, data.error ? 9000 : 5000);
	}
}

function cssEscape(value) {
	if (window.CSS && CSS.escape) {
		return CSS.escape(value);
	}

	return String(value).replace(/["\\]/g, '\\$&');
}

function scrollLog() {
	const log = document.getElementById('activity-log');
	if (log) {
		log.scrollTop = log.scrollHeight;
	}
}

bindAjax();
scrollLog();
