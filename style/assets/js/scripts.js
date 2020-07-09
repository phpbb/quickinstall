(function ($) {

	'use strict';

	$(document).ready(function () {

		// create form validation and submit
		$(".needs-validation").on("click", "button[type='submit']", function (e) {
			e.preventDefault();
			var validated = true;
			$("input[required]").each(function () {
				var $this = $(this);
				var empty = $this.val() === "";
				if (empty) validated = false;
				if (!empty && $this.prop("validity").valid) {
					$this.addClass("is-valid");
				} else {
					$this.addClass("is-invalid");
				}
			});
			if (validated) {
				var $form = $(this).parents("form");
				if ($form.attr('data-submit-ajax') !== undefined) {
					ajaxSubmit($form);
				} else {
					$form.submit();
				}
			}
		});

		// submit form via ajax
		var ajaxSubmit = function ($form) {
			var $modal = $('[data-submit-modal]');
			$modal.modal({
				show: true,
				keyboard: false,
				backdrop: 'static'
			});
			$.ajax({
				type: "POST",
				url: $form.attr("action").replace("&amp;", "&"),
				data: $form.serialize(),
				dataType: "json",
				success: function (res) {
					if (typeof res.redirect !== "undefined" && res.redirect) {
						window.location.replace(res.redirect);
					} else {
						$modal.modal("hide");
					}
				},
				error: function (res) {
					if (typeof res.responseText !== "undefined" && res.responseText) {
						$("body").html(res.responseText);
					} else {
						$modal.modal("hide");
					}
				}
			});
		};

		// submit forms on change
		$("[data-form-submit=true]").on("change", function () {
			$(this).closest("form").submit();
		});

		// Toggle all checkboxes
		var $toggleAll = $("[data-mark-list]");
		var $targetForm = $toggleAll.closest("form");
		var $checkboxes = $targetForm.find("[data-mark]");
		$toggleAll.on("change", function () {
			$checkboxes.prop("checked", $(this).prop("checked"));
		});
		$checkboxes.on("change", function () {
			if ($(this).prop("checked") === false) {
				$toggleAll.prop("checked", false);
				return;
			}
			if ($targetForm.find("[data-mark]:checked").length === $checkboxes.length) {
				$toggleAll.prop("checked", true);
			}
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
			$("#config_text_container").removeClass('d-none');
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
