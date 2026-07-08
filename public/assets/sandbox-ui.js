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
		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
				return;
			}

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

function scrollLog() {
	const log = document.getElementById('activity-log');
	if (log) {
		log.scrollTop = log.scrollHeight;
	}
}

bindAjax();
scrollLog();
