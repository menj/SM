/**
 * Settings screen: tabs, colour pickers, scheme presets, sortable sections.
 */
(function ($) {
	'use strict';

	$(function () {
		var $tabs = $('.sm-tabs__tab');
		var $panels = $('.sm-panel');

		function activate(key, push) {
			$tabs.attr('tabindex', '-1').removeClass('is-active').attr('aria-selected', 'false').filter('[data-tab="' + key + '"]').attr('tabindex', '0').addClass('is-active').attr('aria-selected', 'true');
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
			var i = $tabs.index(this), n = null;
			if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { n = (i + 1) % $tabs.length; }
			if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { n = (i - 1 + $tabs.length) % $tabs.length; }
			if (e.key === 'Home') { n = 0; }
			if (e.key === 'End') { n = $tabs.length - 1; }
			if (n !== null) { e.preventDefault(); $tabs.eq(n).trigger('click').trigger('focus'); }
		});

		// Colour pickers: editing one switches the scheme to Custom.
		function setScheme(key) { $('input[name="sm_settings[scheme]"][value="' + key + '"]').prop('checked', true); }
		$('.sm-color').wpColorPicker({
			change: function () { setScheme('custom'); },
			clear: function () { setScheme('custom'); }
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
			setScheme(key);
		}
		$('input[name="sm_settings[scheme]"]').on('change', function () { if (this.checked && this.value !== 'custom') { applyScheme(this.value); } });

		// Range sliders show their value live; toggles show On/Off.
		$('.sm-range input[type="range"]').on('input change', function () { $(this).siblings('.sm-range__value').text(this.value); });
		$('.sm-toggle input').on('change', function () { $(this).siblings('.sm-toggle__text').text(this.checked ? smAdmin.on : smAdmin.off); });

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
		function syncDropcap() {
			var on = $('#sm-dc_enabled').is(':checked');
			$('[data-field="dc_variant"], [data-field="dc_lines"]').toggle(on);
		}
		$('#sm-dc_enabled').on('change', syncDropcap);
		syncDropcap();
		$('#sm-lb_enabled').on('change', syncLightbox);
		syncLightbox();
	});
})(jQuery);
