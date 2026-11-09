/**
 * SM theme front-end behaviour. No dependencies.
 *
 * - Header search toggle
 * - Back-to-top button
 * - Reading progress bar
 * - AJAX newsletter and contact forms
 * - Donate form custom amount + hand-off
 * - Legal page section navigation highlighting
 */
(function () {
	'use strict';

	var cfg = window.smTheme || { ajaxUrl: '/wp-admin/admin-ajax.php', nonce: '', i18n: {} };

	function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
	function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

	/* Search toggle */
	qsa('.sm-search-toggle').forEach(function (btn) {
		var panelId = btn.getAttribute('aria-controls');
		var panel = panelId ? document.getElementById(panelId) : null;
		if (!panel) { return; }
		btn.addEventListener('click', function () {
			var open = panel.hasAttribute('hidden');
			if (open) {
				panel.removeAttribute('hidden');
				btn.setAttribute('aria-expanded', 'true');
				var input = qs('input[type="search"]', panel);
				if (input) { input.focus(); }
			} else {
				panel.setAttribute('hidden', '');
				btn.setAttribute('aria-expanded', 'false');
			}
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !panel.hasAttribute('hidden')) {
				panel.setAttribute('hidden', '');
				btn.setAttribute('aria-expanded', 'false');
				btn.focus();
			}
		});
	});

	/* Back to top */
	var toTop = qs('.sm-totop');
	if (toTop) {
		var onScroll = function () {
			toTop.classList.toggle('is-visible', window.scrollY > 600);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
		toTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	/* Reading progress */
	var progress = qs('.sm-progress');
	if (progress) {
		var bar = qs('.sm-progress__bar', progress);
		var update = function () {
			var doc = document.documentElement;
			var max = doc.scrollHeight - window.innerHeight;
			var pct = max > 0 ? Math.min(100, Math.max(0, (window.scrollY / max) * 100)) : 0;
			bar.style.width = pct + '%';
		};
		window.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		window.addEventListener('load', update);
		update();
	}

	/* AJAX forms (newsletter + contact) */
	qsa('.sm-ajax-form').forEach(function (form) {
		if (form.dataset.external === '1') { return; }
		var msg = qs('.sm-form__message', form);
		var setMsg = function (text, type) {
			if (!msg) { return; }
			msg.textContent = text || '';
			msg.classList.remove('is-error', 'is-success');
			if (type) { msg.classList.add('is-' + type); }
		};
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			qsa('.is-invalid', form).forEach(function (el) { el.classList.remove('is-invalid'); el.removeAttribute('aria-invalid'); });
			var firstInvalid = qsa('[required]', form).filter(function (el) { return !el.value.trim() || (el.type === 'email' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(el.value)); })[0];
			if (firstInvalid) {
				firstInvalid.classList.add('is-invalid');
				firstInvalid.setAttribute('aria-invalid', 'true');
				if (msg && msg.id) { firstInvalid.setAttribute('aria-describedby', msg.id); }
				firstInvalid.focus();
				setMsg(firstInvalid.type === 'email' ? 'Please enter a valid email address.' : 'Please fill in the required fields.', 'error');
				return;
			}
			form.classList.add('is-sending');
			setMsg(cfg.i18n.sending || 'Sending…');
			var data = new FormData(form);
			fetch(cfg.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (json) {
					form.classList.remove('is-sending');
					if (json && json.success) {
						setMsg(json.data.message, 'success');
						form.reset();
						form.dispatchEvent(new CustomEvent('sm:submitted', { bubbles: true, detail: json.data }));
					} else {
						setMsg((json && json.data && json.data.message) || cfg.i18n.error, 'error');
						if (json && json.data && json.data.errors) {
							Object.keys(json.data.errors).forEach(function (name) {
								var el = form.elements[name];
								if (el) { el.classList.add('is-invalid'); el.setAttribute('aria-invalid', 'true'); if (msg && msg.id) { el.setAttribute('aria-describedby', msg.id); } }
							});
						}
					}
				})
				.catch(function () {
					form.classList.remove('is-sending');
					setMsg(cfg.i18n.error || 'Something went wrong.', 'error');
				});
		});
	});

	/* Donate form */
	qsa('.sm-donate').forEach(function (form) {
		var custom = qs('.sm-donate__custom', form);
		var radios = qsa('input[name="amount"]', form);
		var sync = function () {
			var isCustom = radios.some(function (r) { return r.checked && r.value === 'custom'; });
			if (custom) {
				custom.hidden = !isCustom;
				custom.required = isCustom;
				if (isCustom) { custom.focus(); }
			}
		};
		radios.forEach(function (r) { r.addEventListener('change', sync); });
		sync();
		// Monthly gifts go to the subscription link, whose amount is set in PayPal, so hide the amounts.
		var freqs = qsa('input[name="frequency"]', form);
		var amountGroup = qs('.sm-donate__group--amount', form);
		var syncFreq = function () {
			if (!form.dataset.monthly || !amountGroup) { return; }
			var monthly = freqs.some(function (r) { return r.checked && r.value === 'monthly'; });
			amountGroup.hidden = monthly;
		};
		freqs.forEach(function (r) { r.addEventListener('change', syncFreq); });
		syncFreq();
		form.addEventListener('submit', function (e) {
			if (form.dataset.noCheckout === '1') { e.preventDefault(); return; }
			if (form.dataset.monthly && freqs.some(function (r) { return r.checked && r.value === 'monthly'; }) && /^https:\/\//.test(form.dataset.monthly)) {
				e.preventDefault();
				window.location.href = form.dataset.monthly;
				return;
			}
			var chosen = radios.filter(function (r) { return r.checked; })[0];
			var amount = chosen ? chosen.value : '';
			if (chosen && chosen.value === 'custom' && custom) {
				if (!custom.value || Number(custom.value) < 1) { e.preventDefault(); custom.focus(); return; }
				amount = custom.value;
				chosen.value = custom.value;
				custom.disabled = true;
			}
			// PayPal.Me takes the amount in the path: https://www.paypal.com/paypalme/name/25MYR
			var code = form.dataset.paypalme;
			if (code && /^\d+(\.\d{1,2})?$/.test(amount) && /^https:\/\/www\.paypal\.com\/paypalme\//.test(form.action)) {
				e.preventDefault();
				window.location.href = form.action.replace(/\/$/, '') + '/' + amount + code;
			}
		});
	});

	/* Copy link button */
	qsa('.sm-share__copy').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var status = qs('.sm-share__status', btn.closest('.sm-share'));
			var done = function () { if (status) { status.textContent = btn.dataset.done; setTimeout(function () { status.textContent = ''; }, 2000); } };
			if (navigator.clipboard) { navigator.clipboard.writeText(btn.dataset.url).then(done); }
			else { var t = document.createElement('textarea'); t.value = btn.dataset.url; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) {} document.body.removeChild(t); }
		});
	});

	/* Legal page nav: highlight the section in view */
	var legalNav = qs('.sm-legal__nav');
	if (legalNav && 'IntersectionObserver' in window) {
		var links = qsa('a[href^="#"]', legalNav);
		var targets = links.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); }).filter(Boolean);
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					links.forEach(function (a) { a.classList.toggle('is-current', a.getAttribute('href') === '#' + en.target.id); });
				}
			});
		}, { rootMargin: '-20% 0px -70% 0px' });
		targets.forEach(function (t) { io.observe(t); });
	}
	/* Skip link: move focus into the main content, not just the scroll position. */
	qsa('a.skip-link').forEach(function (link) {
		link.addEventListener('click', function () {
			var target = document.getElementById((link.getAttribute('href') || '').slice(1)) || qs('main');
			if (!target) { return; }
			if (!target.hasAttribute('tabindex')) { target.setAttribute('tabindex', '-1'); }
			target.focus({ preventScroll: false });
		});
	});

})();