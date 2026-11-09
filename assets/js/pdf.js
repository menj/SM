/**
 * Save as PDF: loads html2pdf.js on the first click, then renders a clean copy of the article.
 * Configuration: window.smPdfCfg (inc/features/pdf.php).
 */
(function () {
	'use strict';

	var cfg = window.smPdfCfg;
	var btn = document.querySelector('.sm-share__pdf');
	if (!cfg || !btn) { return; }
	var status = btn.closest('.sm-share') ? btn.closest('.sm-share').querySelector('.sm-share__status') : null;
	var loading = null;

	function say(text) { if (status) { status.textContent = text || ''; } }

	function loadLib() {
		if (window.html2pdf) { return Promise.resolve(); }
		if (loading) { return loading; }
		loading = new Promise(function (resolve, reject) {
			var s = document.createElement('script');
			s.src = cfg.lib;
			s.onload = resolve;
			s.onerror = function () { loading = null; reject(new Error('load')); };
			document.head.appendChild(s);
		});
		return loading;
	}

	// A print-style copy: article only, no menus, forms, share bar, related stories or comments.
	function buildCopy() {
		var main = document.querySelector('main.sm-single') || document.querySelector('main');
		var copy = main.cloneNode(true);
		['.sm-share', '.sm-related', '.sm-comments', '.sm-newsletter', '.sm-post-footer', 'script', 'style', 'form', 'button', 'nav', '.sm-progress', '.sm-totop'].forEach(function (sel) {
			Array.prototype.forEach.call(copy.querySelectorAll(sel), function (n) { n.remove(); });
		});
		Array.prototype.forEach.call(copy.querySelectorAll('img'), function (img) { img.removeAttribute('loading'); img.removeAttribute('srcset'); img.removeAttribute('sizes'); });
		// html2pdf clones this node into its own off-screen container, so it needs no placement here.
		copy.classList.add('sm-pdf-copy');
		copy.style.cssText = 'width:760px;max-width:760px;margin:0;padding:0;background:#fff;color:#111;';
		var credit = document.createElement('p');
		credit.style.cssText = 'font:12px/1.4 sans-serif;color:#555;margin:24px 0 0;border-top:1px solid #ccc;padding-top:8px;';
		credit.textContent = document.title + ' \u2014 ' + cfg.url;
		copy.appendChild(credit);
		return copy;
	}

	btn.addEventListener('click', function () {
		if (btn.disabled) { return; }
		btn.disabled = true;
		btn.setAttribute('aria-busy', 'true');
		say(btn.dataset.busy);
		var wrap;
		loadLib().then(function () {
			wrap = buildCopy();
			return window.html2pdf().set({
				margin: [14, 12, 14, 12],
				filename: cfg.filename,
				image: { type: 'jpeg', quality: 0.9 },
				html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff', windowWidth: 760 },
				jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
				pagebreak: { mode: ['css', 'legacy'], avoid: ['img', 'blockquote', 'h2', 'h3'] }
			}).from(wrap).save();
		}).then(function () {
			say(btn.dataset.done);
		}).catch(function () {
			say(btn.dataset.fail);
		}).then(function () {
			btn.disabled = false;
			btn.removeAttribute('aria-busy');
			setTimeout(function () { say(''); }, 4000);
		});
	});
})();
