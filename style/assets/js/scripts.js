(function ($) {

	'use strict';

	$(document).ready(function () {

		// form validation
		$('form[data-validate]').on("click", "input[type='submit']", function (e) {
			e.preventDefault();
			var validated = true;
			$('[data-validate-required]').each(function () {
				var $this = $(this);
				var empty = $this.val() === "";
				if (empty) validated = false;
				$this.closest(".has-feedback").toggleClass("has-error", empty);
			});
			if (validated) {
				$(this).prop("disabled", true).parents("form").submit();
			}
		});

		// submit forms on change
		$("[data-form-submit=true]").on("change", function () {
			$(this).closest("form").submit();
		});

		// toggle visibility
		$("[data-toggle-view]").on("click", function (e) {
			e.preventDefault();
			var target = "#" + $(this).attr("data-toggle-view");
			$(target).toggleClass('hidden');
		});

		// mark all / unmark all checkboxes
		$("[data-mark-list]").on("click", function (e) {
			e.preventDefault();
			var target = $(this).attr("data-mark-target");
			var checks = $(target).find("input[type=checkbox]");
			checks.prop("checked", $(this).data("mark-list") === "markall");
		});

		// confirm alert dialog
		$("[data-confirm]").on("click", function (e) {
			var message = $(this).attr("data-confirm");
			if (!confirm(message)) {
				e.preventDefault();
			}
		});

		// load new page from menu selection
		$("[data-load-selection]").on("change", function () {
			window.location.href = $(this).attr("data-load-selection") + $(this).find(":selected").val();
		});

		// show config
		$("#config_text_button").on("click", function () {
			$("#config_text_alert").hide();
			$("#config_text_container").removeClass('hidden');
		});

		// search filter for PHP info table
		$('#phpinfo-filter').on("keyup", function () {
			var $rows = $('.searchable tr');
			var regex = new RegExp($(this).val(), "i");
			$rows.hide();
			$rows.filter(function () {
				return regex.test($(this).text());
			}).show();
		});

		// Copy data from a textarea field
		$("[data-copy]").on("click", function () {
			var target = "#" + $(this).attr("data-copy");
			$(target).select();
			document.execCommand('copy');
		});

	});

}(jQuery));
