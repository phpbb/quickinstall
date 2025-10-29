(function(document, window) {
	'use strict';

	const ready = fn => {
		if (document.readyState === 'complete') {
			return fn();
		}

		document.addEventListener('DOMContentLoaded', fn, false);
	};

	ready(() => {
		// Create form validation and submit
		const $form = $('.needs-validation');
		if ($form) {
			$('button[type="submit"]', $form).addEventListener('click', event => {
				event.preventDefault();
				let validated = true;
				for (const $input of $$('input[required]')) {
					const empty = $input.value === '';
					if (empty) {
						validated = false;
					}

					if (!empty && $input.validity.valid) {
						$input.classList.add('is-valid');
					} else {
						$input.classList.add('is-invalid');
					}
				}

				if (validated) {
					if ($form.getAttribute('data-qi-submit-ajax') === undefined) {
						$form.submit();
					} else {
						ajaxSubmit($form);
					}
				}
			});
		}

		// Submit form via ajax
		const ajaxSubmit = $form => {
			const modal = $('[data-qi-submit-modal]');
			const $modal = new bootstrap.Modal(modal, {
				keyboard: false,
				backdrop: 'static',
			});
			modal.addEventListener('shown.bs.modal', () => {
				const xhr = new XMLHttpRequest();

				xhr.responseType = 'json';
				xhr.addEventListener('loadend', () => {
					if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
						if (typeof xhr.response.redirect !== 'undefined' && xhr.response.redirect) {
							window.location.replace(xhr.response.redirect);
						} else if (typeof xhr.response.responseText !== 'undefined' && xhr.response.responseText) {
							mainAlert(xhr.response.responseText);
						}
					} else if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 500) {
						mainAlert(xhr.statusText);
					}

					$modal.hide();
					window.scrollTo(0, 0);
				});

				xhr.open('POST', $form.getAttribute('action').replace('&amp;', '&'));
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
				xhr.send(new FormData($form));
			});
			$modal.show();
		};

		// Submit forms on change
		for (const $select of $$('[data-qi-form-submit=true]')) {
			$select.addEventListener('change', event => {
				event.target.closest('form').submit();
			});
		}

		// Toggle all checkboxes
		const $toggleAll = $('[data-qi-mark-list]');
		if ($toggleAll) {
			const $targetForm = $toggleAll.closest('form');
			const $checkboxes = $$('[data-qi-mark]', $targetForm);
			$toggleAll.addEventListener('change', () => {
				for (const box of $checkboxes) {
					box.checked = $toggleAll.checked;
				}
			});
			for (const box of $checkboxes) {
				box.addEventListener('change', event => {
					const $check = event.target;
					if ($check.checked === false) {
						$toggleAll.checked = false;
						return;
					}

					if ($$('[data-qi-mark]:checked', $targetForm).length === $checkboxes.length) {
						$toggleAll.checked = true;
					}
				});
			}
		}

		// Confirm alert dialog
		for (const $confirmDelete of $$('[data-qi-confirm]')) {
			$confirmDelete.addEventListener('click', event => {
				const message = $confirmDelete.getAttribute('data-qi-confirm');
				if (!confirm(message)) {
					event.preventDefault();
				}
			});
		}

		// Load new page from menu selection
		const $loadSelection = $('[data-qi-load-selection]');
		if ($loadSelection) {
			$loadSelection.addEventListener('change', () => {
				const url = $loadSelection.getAttribute('data-qi-load-selection');
				const iso = $loadSelection.querySelector(':checked').value;
				window.location.href = url + iso;
			});
		}

		// Show config
		const $config = $('#config_text_button');
		if ($config) {
			$config.addEventListener('click', () => {
				$('#config_text_alert').style.display = 'none';
				$('#config_text_container').classList.remove('d-none');
			});
		}

		// Copy data from a textarea field
		const $configField = $('[data-qi-copy]');
		if ($configField) {
			$configField.addEventListener('click', event => {
				const target = '#' + event.target.getAttribute('data-qi-copy');
				$(target).select();
				document.execCommand('copy');
			});
		}

		// Search filter for PHP info table
		const $phpinfo = $('#phpinfo-filter');
		if ($phpinfo) {
			$phpinfo.addEventListener('keyup', event => {
				const regex = new RegExp(event.target.value, 'i');
				for (const row of $$('.searchable tr')) {
					row.style.display = 'none';
				}

				const filtered = Array.prototype.filter.call($$('.searchable tr'), node => {
					const text = node.textContent || node.innerText;
					return regex.test(text);
				});
				for (const row of filtered) {
					row.style.display = 'table-row';
				}
			});
		}

		// Database connection test
		const $testDbBtn = $('#test-db-connection');
		if ($testDbBtn) {
			$testDbBtn.addEventListener('click', () => {
				const $result = $('#db-test-result');
				$testDbBtn.disabled = true;
				$testDbBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Testing...';

				const formData = new FormData();
				formData.append('dbms', $('#dbms').value);
				formData.append('dbhost', $('#dbhost').value);
				formData.append('dbport', $('#dbport').value);
				formData.append('dbuser', $('#dbuser').value);
				formData.append('dbpasswd', $('#dbpasswd').value);

				const xhr = new XMLHttpRequest();
				xhr.responseType = 'json';
				xhr.addEventListener('loadend', () => {
					$testDbBtn.disabled = false;
					$testDbBtn.innerHTML = '<svg class="bi" width="16" height="16" fill="currentColor"><use xlink:href="style/assets/img/bootstrap-icons.svg#database-check"/></svg> Test Database Connection';

					if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
						const response = xhr.response;
						if (response.success) {
							$result.className = 'mt-2 alert alert-success';
							$result.innerHTML = '<svg class="bi text-success" width="16" height="16" fill="currentColor"><use xlink:href="style/assets/img/bootstrap-icons.svg#check-circle-fill"/></svg> ' + response.message;
						} else {
							$result.className = 'mt-2 alert alert-danger';
							$result.innerHTML = '<svg class="bi text-danger" width="16" height="16" fill="currentColor"><use xlink:href="style/assets/img/bootstrap-icons.svg#exclamation-triangle-fill"/></svg> ' + response.message;
						}
					} else {
						$result.className = 'mt-2 alert alert-danger';
						$result.innerHTML = '<svg class="bi text-danger" width="16" height="16" fill="currentColor"><use xlink:href="style/assets/img/bootstrap-icons.svg#exclamation-triangle-fill"/></svg> Connection test failed';
					}
				});

				xhr.open('POST', 'index.php?page=settings&mode=test_db_connection');
				xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
				xhr.send(formData);
			});
		}

		// Notification of QI update (use sessionStorage for dismissed notification)
		if (sessionStorage.getItem('qiupdate') === null) {
			const qiUpdateToast = $('#qiUpdateToast');
			if (qiUpdateToast) {
				const toast = new bootstrap.Toast(qiUpdateToast, {
					autohide: false,
				});
				toast.show();
				qiUpdateToast.addEventListener('hidden.bs.toast', () => {
					sessionStorage.setItem('qiupdate', '1');
				});
			}
		}
	});

	const mainAlert = text => {
		$('#main-alert > p').innerHTML = text;
		$('#main-alert').classList.remove('d-none');
	};

	// Select a list of matching elements, context is optional
	function $$(selector, context) {
		return (context || document).querySelectorAll(selector);
	}

	// Select the first matching element, context is optional
	function $(selector, context) {
		return (context || document).querySelector(selector);
	}
})(document, window);
