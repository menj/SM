/**
 * Settings screen: tabs, colour pickers, scheme presets, sortable sections.
 */
(function ($) {
	'use strict';

	$(function () {
		var $tabs = $('.sm-tabs__tab');
		var $panels = $('.sm-panel');

		function activate(key, push) {
			$tabs.removeClass('is-active').attr('aria-selected', 'false').filter('[data-tab="' + key + '"]').addClass('is-active').attr('aria-selected', 'true');
			$panels.attr('hidden', '').removeClass('is-active').filter('#sm-panel-' + key).removeAttr('hidden').addClass('is-active');
			$('input[name="_wp_http_referer"]').val($('input[name="_wp_http_referer"]').val().replace(/tab=[a-z_]+/, 'tab=' + key));
			if (push && window.history && history.replaceState) {
				var url = new URL(window.location.href);
				url.searchParams.set('tab', key);
				history.replaceState(null, '', url.toString());
			}
		}

		$tabs.on('click', function () { activate($(this).data('tab'), true); });
		$tabs.on('keydown', function (e) {
			var i = $tabs.index(this);
			if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { e.preventDefault(); $tabs.eq((i + 1) % $tabs.length).trigger('click').focus(); }
			if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { e.preventDefault(); $tabs.eq((i - 1 + $tabs.length) % $tabs.length).trigger('click').focus(); }
		});

		// Colour pickers.
		$('.sm-color').wpColorPicker({
			change: function () { $('.sm-scheme-select').val('custom'); },
			clear: function () { $('.sm-scheme-select').val('custom'); }
		});

		// Scheme presets fill the pickers.
		function applyScheme(key) {
			var scheme = window.smAdmin && smAdmin.schemes && smAdmin.schemes[key];
			if (!scheme) { return; }
			Object.keys(scheme).forEach(function (k) {
				if (k === 'label') { return; }
				var $input = $('#sm-' + k);
				if ($input.length) { $input.wpColorPicker('color', scheme[k]); }
			});
			$('.sm-scheme-select').val(key);
		}
		$('.sm-scheme-select').on('change', function () { applyScheme(this.value); });
		$('.sm-swatch').on('click', function () { applyScheme($(this).data('scheme')); });

		// Sortable homepage sections.
		$('.sm-sortable').sortable({ handle: '.sm-sortable__handle', axis: 'y', placeholder: 'ui-sortable-placeholder' });

		// Hide the login slug fields until the feature is on.
		function syncLogin() {
			var on = $('#sm-login_enabled').is(':checked');
			$('[data-field="login_slug"], [data-field="login_redirect"]').toggle(on);
		}
		$('#sm-login_enabled').on('change', syncLogin);
		syncLogin();

		function syncProgress() {
			var on = $('#sm-rp_enabled').is(':checked');
			$('[data-field="rp_position"], [data-field="rp_height"], [data-field="rp_color"], [data-field="rp_post_types"]').toggle(on);
		}
		$('#sm-rp_enabled').on('change', syncProgress);
		syncProgress();

		function syncLightbox() {
			var on = $('#sm-lb_enabled').is(':checked');
			$('[data-field="lb_wrap"], [data-field="lb_captions"]').toggle(on);
		}
		$('#sm-lb_enabled').on('change', syncLightbox);
		syncLightbox();
	});
})(jQuery);
