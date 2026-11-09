/**
 * Accessibility additions for the bundled Lightbox2 (the vendor files stay untouched).
 * Names the icon-only controls and moves focus to the close button when the lightbox opens,
 * so keyboard and screen reader users land inside it. Labels come from window.smLightboxA11y.
 */
(function () {
	'use strict';

	var t = window.smLightboxA11y || {};

	function name(sel, text) {
		var el = document.querySelector(sel);
		if (el && text) {
			el.setAttribute('aria-label', text);
			el.setAttribute('role', 'button');
			if (!el.hasAttribute('href')) { el.setAttribute('tabindex', '0'); }
		}
	}

	function ready() {
		name('#lightbox .lb-close', t.close);
		name('#lightbox .lb-prev', t.prev);
		name('#lightbox .lb-next', t.next);
	}

	// Lightbox2 builds its markup in a jQuery ready handler registered before this script,
	// so a handler added now runs after it. Also re-applied on every open (below).
	if (window.jQuery) { window.jQuery(ready); } else { document.addEventListener('DOMContentLoaded', ready); }

	// Keyboard activation for the named controls (they are anchors without href).
	document.addEventListener('keydown', function (e) {
		var el = e.target;
		if ((e.key === 'Enter' || e.key === ' ') && el && el.closest && el.closest('#lightbox') && el.matches('.lb-close, .lb-prev, .lb-next')) {
			e.preventDefault();
			el.click();
		}
	});

	function box() { return document.getElementById('lightbox'); }
	function isOpen() { var b = box(); return !!b && b.offsetParent !== null && window.getComputedStyle(b).display !== 'none'; }

	function toClose() {
		var b = box();
		var close = b && b.querySelector('.lb-close');
		if (close) { ready(); close.focus(); }
	}

	// Lightbox2 puts focus on its backdrop while it animates in. Whenever focus lands on the
	// backdrop or the lightbox container itself, send it to the close button instead.
	document.addEventListener('focusin', function (e) {
		var t = e.target;
		if (isOpen() && t && (t.id === 'lightboxOverlay' || t.id === 'lightbox')) { toClose(); }
	});

	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[data-lightbox]') : null;
		if (!a) { return; }
		// The close button is only focusable once Lightbox2 has finished loading the image.
		var tries = 0;
		var timer = setInterval(function () {
			tries++;
			toClose();
			var b = box();
			if (tries > 25 || (b && document.activeElement === b.querySelector('.lb-close'))) { clearInterval(timer); }
		}, 120);
	}, true); // capture phase: Lightbox2's own click handler stops propagation.

	// Keep Tab inside the open lightbox.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Tab' || !isOpen()) { return; }
		var b = box();
		var items = Array.prototype.slice.call(b.querySelectorAll('.lb-prev, .lb-next, .lb-close')).filter(function (n) { return n.offsetParent !== null && window.getComputedStyle(n).display !== 'none'; });
		if (!items.length) { return; }
		var i = items.indexOf(document.activeElement);
		e.preventDefault();
		var n = e.shiftKey ? (i <= 0 ? items.length - 1 : i - 1) : (i === -1 || i === items.length - 1 ? 0 : i + 1);
		items[n].focus();
	});
})();
