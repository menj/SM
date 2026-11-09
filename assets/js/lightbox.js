/**
 * Image lightbox for linked images and galleries (no dependencies).
 *
 * Any <a data-lightbox="group"> that wraps an image opens in a native <dialog>:
 * focus is trapped, Escape closes, focus returns to the link. Arrow keys and
 * on-screen buttons move through the group. Configuration comes from
 * window.smLightboxCfg (see inc/features/lightbox.php).
 */
(function () {
	'use strict';

	if (typeof HTMLDialogElement === 'undefined') { return; }

	var cfg = window.smLightboxCfg || { wrap: true, captions: true, i18n: {} };
	var t = cfg.i18n || {};
	var dlg, img, cap, count, prev, next, close, album = [], index = 0, opener = null;

	function build() {
		dlg = document.createElement('dialog');
		dlg.className = 'sm-lb';
		dlg.setAttribute('aria-label', t.label || 'Image viewer');
		dlg.innerHTML =
			'<div class="sm-lb__stage">' +
			'<button type="button" class="sm-lb__btn sm-lb__close" aria-label="' + (t.close || 'Close') + '"><span aria-hidden="true">&times;</span></button>' +
			'<button type="button" class="sm-lb__btn sm-lb__prev" aria-label="' + (t.prev || 'Previous image') + '"><span aria-hidden="true">&#8249;</span></button>' +
			'<button type="button" class="sm-lb__btn sm-lb__next" aria-label="' + (t.next || 'Next image') + '"><span aria-hidden="true">&#8250;</span></button>' +
			'<figure class="sm-lb__figure"><img class="sm-lb__img" alt="" /><figcaption class="sm-lb__caption"></figcaption></figure>' +
			'<p class="sm-lb__count" role="status" aria-live="polite"></p>' +
			'</div>';
		document.body.appendChild(dlg);
		img = dlg.querySelector('.sm-lb__img');
		cap = dlg.querySelector('.sm-lb__caption');
		count = dlg.querySelector('.sm-lb__count');
		prev = dlg.querySelector('.sm-lb__prev');
		next = dlg.querySelector('.sm-lb__next');
		close = dlg.querySelector('.sm-lb__close');

		close.addEventListener('click', function () { dlg.close(); });
		prev.addEventListener('click', function () { go(-1); });
		next.addEventListener('click', function () { go(1); });
		// A click on the dark backdrop (the dialog element itself or the empty stage) closes it.
		dlg.addEventListener('click', function (e) {
			if (e.target === dlg || e.target.classList.contains('sm-lb__stage')) { dlg.close(); }
		});
		dlg.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
			if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
		});
		dlg.addEventListener('close', function () {
			document.documentElement.classList.remove('sm-lb-open');
			img.removeAttribute('src');
			if (opener && document.contains(opener)) { opener.focus(); }
		});
	}

	function show() {
		var a = album[index];
		var thumb = a.querySelector('img');
		var title = a.getAttribute('data-title') || '';
		dlg.classList.add('is-loading');
		img.onload = function () { dlg.classList.remove('is-loading'); };
		img.src = a.getAttribute('href');
		img.alt = title || (thumb ? thumb.getAttribute('alt') || '' : '');
		cap.textContent = cfg.captions ? title : '';
		cap.hidden = !(cfg.captions && title);
		var multi = album.length > 1;
		prev.hidden = next.hidden = !multi;
		if (multi && !cfg.wrap) {
			prev.disabled = index === 0;
			next.disabled = index === album.length - 1;
		} else {
			prev.disabled = next.disabled = false;
		}
		count.textContent = multi ? (t.count || 'Image %1 of %2').replace('%1', index + 1).replace('%2', album.length) : '';
		// Preload the neighbours so arrow-key browsing feels instant.
		[index - 1, index + 1].forEach(function (i) {
			if (album[i]) { new Image().src = album[i].getAttribute('href'); }
		});
	}

	function go(step) {
		var n = index + step;
		if (n < 0 || n >= album.length) {
			if (!cfg.wrap) { return; }
			n = (n + album.length) % album.length;
		}
		index = n;
		show();
	}

	document.addEventListener('click', function (e) {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
		var a = e.target.closest ? e.target.closest('a[data-lightbox]') : null;
		if (!a || !a.querySelector('img')) { return; }
		e.preventDefault();
		if (!dlg) { build(); }
		var group = a.getAttribute('data-lightbox');
		album = Array.prototype.slice.call(document.querySelectorAll('a[data-lightbox]')).filter(function (x) {
			return x.getAttribute('data-lightbox') === group;
		});
		index = Math.max(0, album.indexOf(a));
		opener = a;
		show();
		document.documentElement.classList.add('sm-lb-open');
		dlg.showModal();
		close.focus();
	});
})();
