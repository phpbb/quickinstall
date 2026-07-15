const dashboard = document.getElementById('dashboard');
const busy = document.getElementById('busy');

function setProcessing(active) {
	busy.classList.toggle('is-active', active);
	document.documentElement.classList.toggle('is-processing', active);
}

function bindAjax() {
	bindUpdateBanner();

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
			const original = submitter ? submitter.innerHTML : '';
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
				const responseText = await response.text();
				let data;
				try {
					data = JSON.parse(responseText);
				} catch (error) {
					const detail = responseText
						.replace(/<[^>]*>/g, ' ')
						.replace(/\s+/g, ' ')
						.trim()
						.slice(0, 500);
					throw new Error(detail || 'QuickInstall returned an invalid response. Check the UI error log.');
				}
				if (!data || typeof data !== 'object') {
					throw new Error('QuickInstall returned an invalid response object.');
				}
				if (typeof data.html === 'string' && data.html !== '') {
					dashboard.innerHTML = data.html;
					bindAjax();
				}
				showActionResult(data, context);
				scrollLog();
			} catch (error) {
				alert('Request failed: ' + error.message);
			} finally {
				setProcessing(false);
				if (submitter) {
					submitter.disabled = false;
					submitter.innerHTML = original;
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

function bindUpdateBanner() {
	const banner = dashboard.querySelector('[data-update-version]');
	if (!banner) {
		return;
	}

	const version = banner.dataset.updateVersion || '';
	const key = 'qi.dismissedUpdate.' + version;
	if (version && localStorage.getItem(key) === '1') {
		banner.remove();
		return;
	}

	const dismiss = banner.querySelector('[data-dismiss-update]');
	if (!dismiss || dismiss.dataset.bound) {
		return;
	}

	dismiss.dataset.bound = '1';
	dismiss.addEventListener('click', () => {
		if (version) {
			localStorage.setItem(key, '1');
		}
		banner.remove();
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
		target = boardHeader(context.board);
	}
	if (!target && context.section) {
		const section = document.getElementById(context.section);
		target = section ? section.querySelector('.section-head') : null;
	}
	if (!target) {
		target = dashboard.querySelector('.status-strip');
	}

	if (target) {
		if (target.classList.contains('status-strip')) {
			target.insertAdjacentElement('beforebegin', result);
		} else {
			target.insertAdjacentElement('afterend', result);
		}
		window.setTimeout(() => {
			result.classList.add('is-dismissing');
			window.setTimeout(() => result.remove(), 180);
		}, data.error ? 9000 : 5000);
	}
}

function boardHeader(name) {
	const boards = dashboard.querySelectorAll('[data-board]');
	for (const board of boards) {
		if (board.dataset.board === name) {
			return board.querySelector('.card-head');
		}
	}

	return null;
}

function scrollLog() {
	const log = document.getElementById('activity-log');
	if (log) {
		log.scrollTop = log.scrollHeight;
	}
}

bindAjax();
scrollLog();
