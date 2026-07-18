const dashboard = document.getElementById('dashboard');
const busy = document.getElementById('busy');
const pendingActions = new Map();
const activityEntries = [];
const maxActivityEntries = 50;
let nextActionId = 1;

function updateProcessingState() {
	const active = pendingActions.size > 0;
	busy.classList.toggle('is-active', active);
	document.documentElement.classList.toggle('is-processing', active);
}

function sameAction(left, right) {
	return left.action === right.action
		&& left.board === right.board
		&& left.section === right.section
		&& left.name === right.name
		&& left.source === right.source;
}

/** Reapplies pending state after AJAX replaces dashboard markup. */
function syncPendingActions() {
	pendingActions.forEach((pending) => {
		const form = Array.from(dashboard.querySelectorAll('form[data-ajax]'))
			.find((candidate) => sameAction(actionContext(candidate), pending.context));
		const submitter = form ? form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]') : null;
		if (!submitter) {
			return;
		}

		pending.submitter = submitter;
		submitter.disabled = true;
		submitter.textContent = 'Working...';
	});

	updateProcessingState();
	updateActivityStates();
}

function actionLabel(context) {
	const action = context.action.replaceAll('_', ' ').replace(/^./, (character) => character.toUpperCase());
	const target = context.board || context.name || context.source;
	return target ? action + ' — ' + target : action;
}

function addActivityEntry(actionId, context) {
	activityEntries.push({
		id: actionId,
		context,
		status: 'queued',
		submittedAt: new Date(),
		completedAt: null,
		message: '',
		output: '',
	});
	trimActivityEntries();
}

function completeActivityEntry(actionId, data) {
	const entry = activityEntries.find((candidate) => candidate.id === actionId);
	if (!entry) {
		return;
	}

	entry.status = data.error ? 'error' : 'success';
	entry.completedAt = new Date();
	entry.message = data.error || data.notice || '';
	entry.output = data.output || '';
	trimActivityEntries();
	renderActivityLog();
}

function updateActivityStates() {
	let runningAssigned = false;
	activityEntries.forEach((entry) => {
		if (!pendingActions.has(entry.id) || entry.status === 'success' || entry.status === 'error') {
			return;
		}

		entry.status = runningAssigned ? 'queued' : 'running';
		runningAssigned = true;
	});
	renderActivityLog();
}

function trimActivityEntries() {
	while (activityEntries.length > maxActivityEntries) {
		const completedIndex = activityEntries.findIndex((entry) => !pendingActions.has(entry.id));
		if (completedIndex === -1) {
			return;
		}
		activityEntries.splice(completedIndex, 1);
	}
}

function renderActivityLog() {
	const log = document.getElementById('activity-log');
	if (!log) {
		return;
	}

	if (activityEntries.length === 0) {
		log.textContent = 'No actions recorded yet.';
		return;
	}

	log.textContent = activityEntries.map((entry) => {
		const timestamp = (entry.completedAt || entry.submittedAt).toLocaleTimeString();
		const lines = [ '[' + timestamp + '] ' + entry.status.toUpperCase() + '  ' + actionLabel(entry.context) ];
		if (entry.message) {
			lines.push(entry.message);
		}
		if (entry.output.trim()) {
			lines.push(entry.output.trimEnd());
		}
		return lines.join('\n');
	}).join('\n\n');
	log.scrollTop = log.scrollHeight;
}

function bindAjax() {
	bindUpdateBanner();
	bindMountedLists();

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
			const actionId = nextActionId++;
			pendingActions.set(actionId, { context, submitter, original });
			addActivityEntry(actionId, context);

			syncPendingActions();
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
				completeActivityEntry(actionId, data);
				if (typeof data.html === 'string' && data.html !== '') {
					dashboard.innerHTML = data.html;
					bindAjax();
					syncPendingActions();
				}
				showActionResult(data, context);
				scrollLog();
			} catch (error) {
				completeActivityEntry(actionId, { error: 'Request failed: ' + error.message, output: '' });
				alert('Request failed: ' + error.message);
			} finally {
				const pending = pendingActions.get(actionId);
				pendingActions.delete(actionId);
				if (pending && pending.submitter) {
					pending.submitter.disabled = false;
					pending.submitter.innerHTML = pending.original;
				}
				syncPendingActions();
			}
		});
	});

	document.querySelectorAll('[data-clear-log]').forEach((button) => {
		if (button.dataset.bound) {
			return;
		}

		button.dataset.bound = '1';
		button.addEventListener('click', () => {
			for (let index = activityEntries.length - 1; index >= 0; index--) {
				if (!pendingActions.has(activityEntries[index].id)) {
					activityEntries.splice(index, 1);
				}
			}
			renderActivityLog();
		});
	});

	renderActivityLog();
}

function bindMountedLists() {
	document.querySelectorAll('[data-mounted-toggle]').forEach((button) => {
		if (button.dataset.bound) {
			return;
		}

		button.dataset.bound = '1';
		button.addEventListener('click', () => {
			const mounted = button.closest('.mounted');
			const extras = mounted ? mounted.querySelectorAll('[data-mounted-extra]') : [];
			const expanded = button.getAttribute('aria-expanded') === 'true';
			extras.forEach((item) => {
				item.hidden = expanded;
			});
			button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
			button.textContent = expanded ? button.dataset.moreLabel : 'Show less';
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
		name: formData.get('name') || '',
		section: section ? section.id : '',
		source: formData.get('source') || '',
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
