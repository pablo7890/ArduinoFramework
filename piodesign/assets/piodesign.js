/**
 * PioDesign – interactions. No dependencies.
 *  - gallery preview: hovering a story cycles through photos from its gallery
 *    (on phones: plays while the card sits in the middle of the screen);
 *  - category filter with View Transitions;
 *  - live countdowns;
 *  - timeline <-> agenda linking;
 *  - copy-link button.
 */
(function () {
	'use strict';

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var SLIDE_MS = 1100;

	/* ------------------------------------------------------------ gallery preview */

	function Gallery(frame) {
		this.frame = frame;
		this.srcs = [];
		try { this.srcs = JSON.parse(frame.getAttribute('data-gallery')) || []; } catch (e) {}
		this.layers = null;
		this.dots = frame.querySelectorAll('.pio-frame__dots i');
		this.index = 0;
		this.timer = null;
		frame.style.setProperty('--pio-slide', SLIDE_MS + 'ms');
	}
	Gallery.prototype.build = function () {
		if (this.layers) { return; }
		var main = this.frame.querySelector('.pio-frame__img');
		var ref = this.frame.querySelector('.pio-photos, .pio-frame__dots');
		this.layers = [main];
		for (var i = 0; i < this.srcs.length; i++) {
			var img = document.createElement('img');
			img.className = 'pio-frame__alt';
			img.alt = '';
			img.decoding = 'async';
			img.src = this.srcs[i];
			this.frame.insertBefore(img, ref);
			this.layers.push(img);
		}
	};
	Gallery.prototype.show = function (n) {
		var self = this;
		this.index = n;
		for (var i = 1; i < this.layers.length; i++) {
			this.layers[i].classList.toggle('is-on', i === n);
		}
		Array.prototype.forEach.call(this.dots, function (dot, i) {
			dot.classList.toggle('is-on', i === n);
			dot.classList.toggle('is-done', i < n);
			if (i === n) { // restart the fill animation
				dot.classList.remove('is-on');
				void dot.offsetWidth;
				dot.classList.add('is-on');
			}
		});
		void self;
	};
	Gallery.prototype.play = function () {
		if (reduced || this.timer || !this.srcs.length) { return; }
		var self = this;
		this.build();
		this.frame.classList.add('is-playing');
		this.show(0);
		this.timer = window.setInterval(function () {
			self.show((self.index + 1) % self.layers.length);
		}, SLIDE_MS);
	};
	Gallery.prototype.stop = function () {
		if (!this.timer) { return; }
		window.clearInterval(this.timer);
		this.timer = null;
		this.frame.classList.remove('is-playing');
		if (this.layers) { this.show(0); }
	};

	function initGalleries(root) {
		var frames = root.querySelectorAll('.pio-frame[data-gallery]');
		if (!frames.length) { return; }
		var galleries = Array.prototype.map.call(frames, function (f) { return new Gallery(f); });

		if (finePointer) {
			galleries.forEach(function (g) {
				var link = g.frame.closest('a') || g.frame;
				link.addEventListener('pointerenter', function () { g.play(); });
				link.addEventListener('pointerleave', function () { g.stop(); });
				link.addEventListener('focus', function () { g.play(); });
				link.addEventListener('blur', function () { g.stop(); });
			});
			return;
		}
		if (!('IntersectionObserver' in window)) { return; }
		// Touch screens: play the one card that sits in the middle of the screen.
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				var g = galleries[Array.prototype.indexOf.call(frames, en.target)];
				if (en.isIntersecting) { g.play(); } else { g.stop(); }
			});
		}, { rootMargin: '-35% 0px -35% 0px', threshold: 0 });
		Array.prototype.forEach.call(frames, function (f) { io.observe(f); });
	}

	/* ------------------------------------------------------------ category filter */

	function initFilter(section) {
		var chips = section.querySelectorAll('button.pio-chip');
		var grid = section.querySelector('.pio-newsroom');
		if (!chips.length || !grid) { return; }
		var stories = grid.querySelectorAll('.pio-story');
		Array.prototype.forEach.call(stories, function (s) {
			var m = s.className.match(/pio-story--(\w+)/);
			s.setAttribute('data-variant', m ? m[1] : 'card');
		});

		function apply(filter) {
			var all = filter === '*';
			grid.classList.toggle('is-filtered', !all);
			Array.prototype.forEach.call(stories, function (s) {
				var variant = s.getAttribute('data-variant');
				var match = all || s.getAttribute('data-cat') === filter;
				s.classList.toggle('is-hidden', !match);
				s.classList.remove('pio-story--lead', 'pio-story--side', 'pio-story--card', 'pio-story--brief');
				s.classList.add('pio-story--' + (all ? variant : 'card'));
			});
			Array.prototype.forEach.call(chips, function (c) {
				var on = c.getAttribute('data-filter') === filter;
				c.classList.toggle('is-active', on);
				c.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}

		Array.prototype.forEach.call(chips, function (chip) {
			chip.addEventListener('click', function () {
				var filter = chip.getAttribute('data-filter');
				if (document.startViewTransition && !reduced) {
					document.startViewTransition(function () { apply(filter); });
				} else {
					apply(filter);
				}
			});
		});
	}

	/* ------------------------------------------------------------ countdown */

	function plural(n, one, few, many) {
		if (n === 1) { return one; }
		var d = n % 10, t = n % 100;
		return d >= 2 && d <= 4 && (t < 12 || t > 14) ? few : many;
	}
	function pad(n) { return n < 10 ? '0' + n : String(n); }

	function initCountdowns(root) {
		var boxes = root.querySelectorAll('[data-countdown]');
		if (!boxes.length) { return; }
		function tick() {
			var now = Date.now() / 1000;
			Array.prototype.forEach.call(boxes, function (box) {
				var start = +box.getAttribute('data-countdown');
				var end = +box.getAttribute('data-end') || start;
				var left = Math.max(0, Math.floor(start - now));
				if (left <= 0) {
					box.classList.add('is-live');
					var live = box.querySelector('.pio-count__live');
					live.hidden = false;
					live.textContent = now < end ? 'Trwa teraz' : 'Wydarzenie się zakończyło';
					return;
				}
				var vals = {
					d: Math.floor(left / 86400),
					h: pad(Math.floor(left % 86400 / 3600)),
					m: pad(Math.floor(left % 3600 / 60)),
					s: pad(left % 60)
				};
				Object.keys(vals).forEach(function (u) {
					var el = box.querySelector('[data-u="' + u + '"]');
					var v = String(vals[u]);
					if (el && el.textContent !== v) {
						el.textContent = v;
						if (!reduced) {
							el.classList.remove('is-tick');
							void el.offsetWidth;
							el.classList.add('is-tick');
						}
					}
				});
				var dl = box.querySelector('[data-l="d"]');
				if (dl) { dl.textContent = plural(vals.d, 'dzień', 'dni', 'dni'); }
			});
		}
		tick();
		window.setInterval(tick, 1000);
	}

	/* ------------------------------------------------------------ timeline */

	function initTimelines(root) {
		Array.prototype.forEach.call(root.querySelectorAll('.pio-tl'), function (tl, n) {
			var scroller = tl.querySelector('.pio-tl__scroll');
			var today = tl.querySelector('.pio-tl__day.is-today');
			if (scroller && today && scroller.scrollWidth > scroller.clientWidth) {
				scroller.scrollLeft = Math.max(0, today.offsetLeft - 60);
			}
			Array.prototype.forEach.call(tl.querySelectorAll('.pio-tl__dot'), function (dot, i) {
				dot.style.setProperty('--d', i);
			});
			void n;
		});
	}

	function bindTimelineEvents(root) {
		root.addEventListener('click', function (ev) {
			var a = ev.target.closest && ev.target.closest('.pio-tl__bar, .pio-tl__dot');
			if (!a) { return; }
			var id = (a.getAttribute('href') || '').slice(1);
			var target = null;
			if (id) {
				Array.prototype.forEach.call(document.querySelectorAll('[id="' + id + '"]'), function (el) {
					if (!target && el.getClientRects().length) { target = el; }
				});
			}
			ev.preventDefault();
			if (!target) {
				window.location.href = a.getAttribute('data-href');
				return;
			}
			target.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' });
			target.classList.remove('is-flash');
			void target.offsetWidth;
			target.classList.add('is-flash');
		});

		// Hovering an agenda row lights up its day on the timeline.
		root.addEventListener('pointerover', function (ev) {
			var row = ev.target.closest && ev.target.closest('[data-day]');
			if (!row || row.classList.contains('pio-tl__day')) { return; }
			var day = row.getAttribute('data-day');
			Array.prototype.forEach.call(document.querySelectorAll('.pio-tl__day'), function (d) {
				d.classList.toggle('is-hover', d.getAttribute('data-day') === day);
			});
		});
		root.addEventListener('pointerout', function (ev) {
			var row = ev.target.closest && ev.target.closest('.pio-ev, .pio-next');
			if (row && !row.contains(ev.relatedTarget)) {
				Array.prototype.forEach.call(document.querySelectorAll('.pio-tl__day.is-hover'), function (d) {
					d.classList.remove('is-hover');
				});
			}
		});
	}

	/* ------------------------------------------------------------ copy (event link, phone, e-mail, account) */

	function copyText(text) {
		if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.setAttribute('readonly', '');
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			var ok = false;
			try { ok = document.execCommand('copy'); } catch (e) {}
			document.body.removeChild(ta);
			if (ok) { resolve(); } else { reject(); }
		});
	}

	function initCopy(root) {
		root.addEventListener('click', function (ev) {
			var btn = ev.target.closest && ev.target.closest('.pio-copy');
			if (!btn) { return; }
			var text = btn.getAttribute('data-copy');
			var label = btn.querySelector('span:not(.screen-reader-text)');
			var doneText = btn.getAttribute('data-done') || 'Skopiowano link';
			if (label && !btn.hasAttribute('data-label')) { btn.setAttribute('data-label', label.textContent); }
			copyText(text).then(function () {
				btn.classList.add('is-done');
				if (label) { label.textContent = doneText; }
				window.clearTimeout(btn._pioT);
				btn._pioT = window.setTimeout(function () {
					btn.classList.remove('is-done');
					if (label) { label.textContent = btn.getAttribute('data-label'); }
				}, 2200);
			}, function () {
				// Clipboard refused: select the text the button refers to, so it can be copied by hand.
				var sel = window.getSelection && window.getSelection();
				var target = btn.previousElementSibling || btn.parentNode;
				if (sel && target) { var r = document.createRange(); r.selectNodeContents(target); sel.removeAllRanges(); sel.addRange(r); }
			});
		});
	}

	/* ------------------------------------------------------------ carousels: sacraments, quotes */

	function Carousel(root) {
		this.root = root;
		this.slides = root.querySelectorAll('[data-car-slide]');
		this.tabs = root.querySelectorAll('[data-car-tab]');
		this.dots = root.querySelectorAll('[data-car-dot]');
		this.counter = root.querySelector('[data-car-current]');
		this.ms = (parseInt(root.getAttribute('data-autoplay'), 10) || 0) * 1000;
		this.index = 0;
		this.timer = null;
		this.left = this.ms;
		this.started = 0;
		this.hover = false;
		this.visible = false;
		var self = this;
		if (this.slides.length < 2) { return; }

		var prev = root.querySelector('[data-car-prev]');
		var next = root.querySelector('[data-car-next]');
		if (prev) { prev.addEventListener('click', function () { self.go(self.index - 1, true); }); }
		if (next) { next.addEventListener('click', function () { self.go(self.index + 1, true); }); }
		Array.prototype.forEach.call(this.tabs, function (tab, i) {
			tab.addEventListener('click', function () { self.go(i, true); });
			tab.addEventListener('keydown', function (e) {
				var k = e.key, to = null;
				if (k === 'ArrowRight' || k === 'ArrowDown') { to = i + 1; }
				if (k === 'ArrowLeft' || k === 'ArrowUp') { to = i - 1; }
				if (k === 'Home') { to = 0; }
				if (k === 'End') { to = self.slides.length - 1; }
				if (to === null) { return; }
				e.preventDefault();
				self.go(to, true);
				self.tabs[self.index].focus();
			});
		});
		Array.prototype.forEach.call(this.dots, function (dot, i) {
			dot.addEventListener('click', function () { self.go(i, true); });
		});

		// Swipe on touch screens.
		var stage = root.querySelector('[data-car-stage]') || root;
		var x0 = null, y0 = null;
		stage.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse') { x0 = e.clientX; y0 = e.clientY; } });
		stage.addEventListener('pointerup', function (e) {
			if (x0 === null) { return; }
			var dx = e.clientX - x0, dy = e.clientY - y0;
			x0 = null;
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.3) { self.go(self.index + (dx < 0 ? 1 : -1), true); }
		});
		stage.addEventListener('pointercancel', function () { x0 = null; });

		// Autoplay only while on screen, not hovered or focused, tab visible.
		root.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') { self.hover = true; self.sync(); } });
		root.addEventListener('pointerleave', function () { self.hover = false; self.sync(); });
		root.addEventListener('focusin', function () { self.hover = true; self.sync(); });
		root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) { self.hover = false; self.sync(); } });
		document.addEventListener('visibilitychange', function () { self.sync(); });
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (en) { self.visible = en[0].isIntersecting; self.sync(); }, { threshold: 0.35 }).observe(root);
		} else {
			this.visible = true;
		}
		this.sync();
	}
	Carousel.prototype.canPlay = function () {
		return this.ms > 0 && !reduced && this.visible && !this.hover && !document.hidden;
	};
	Carousel.prototype.sync = function () {
		var self = this;
		var root = this.root;
		if (this.canPlay()) {
			if (this.timer) { return; }
			if (!root.classList.contains('is-playing')) { this.restartProgress(); }
			root.classList.remove('is-paused');
			this.started = Date.now();
			this.timer = window.setTimeout(function () { self.timer = null; self.left = self.ms; self.go(self.index + 1, false); }, this.left);
		} else {
			if (this.timer) {
				window.clearTimeout(this.timer);
				this.timer = null;
				this.left = Math.max(300, this.left - (Date.now() - this.started));
			}
			if (root.classList.contains('is-playing')) { root.classList.add('is-paused'); }
		}
	};
	Carousel.prototype.restartProgress = function () {
		var root = this.root;
		root.classList.remove('is-playing', 'is-paused');
		void root.offsetWidth;
		if (this.ms > 0 && !reduced) { root.classList.add('is-playing'); }
	};
	Carousel.prototype.go = function (to, byUser) {
		var n = this.slides.length;
		var i = ((to % n) + n) % n;
		var self = this;
		this.index = i;
		Array.prototype.forEach.call(this.slides, function (sl, k) {
			var on = k === i;
			if (on) {
				// Re-run entrance animations.
				sl.classList.remove('is-active');
				void sl.offsetWidth;
			}
			sl.classList.toggle('is-active', on);
			if (on) { sl.removeAttribute('aria-hidden'); sl.removeAttribute('inert'); }
			else { sl.setAttribute('aria-hidden', 'true'); if (sl.getAttribute('role') === 'tabpanel') { sl.setAttribute('inert', ''); } }
		});
		Array.prototype.forEach.call(this.tabs, function (tab, k) {
			var on = k === i;
			tab.classList.toggle('is-active', on);
			tab.setAttribute('aria-selected', on ? 'true' : 'false');
			tab.setAttribute('tabindex', on ? '0' : '-1');
			if (on && tab.parentNode.scrollWidth > tab.parentNode.clientWidth) {
				tab.parentNode.scrollTo({ left: tab.offsetLeft - 16, behavior: reduced ? 'auto' : 'smooth' });
			}
		});
		Array.prototype.forEach.call(this.dots, function (dot, k) { dot.classList.toggle('is-active', k === i); });
		if (this.counter) { this.counter.textContent = String(i + 1); }
		if (this.timer) { window.clearTimeout(this.timer); this.timer = null; }
		this.left = this.ms;
		if (byUser) { this.hover = this.root.matches(':hover') || this.root.contains(document.activeElement); }
		this.restartProgress();
		if (!this.canPlay()) { this.root.classList.add('is-paused'); }
		self.sync();
	};

	function initCarousels(root) {
		Array.prototype.forEach.call(root.querySelectorAll('[data-pio-carousel]'), function (el) {
			if (!el._pioCar) { el._pioCar = new Carousel(el); }
		});
	}

	/* ------------------------------------------------------------ parish info: next Mass, office status, map */

	var WD = ['', 'poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota', 'niedziela'];
	var WD_ACC = ['', 'poniedziałek', 'wtorek', 'środę', 'czwartek', 'piątek', 'sobotę', 'niedzielę'];

	/** Wall-clock time in Poland, whatever the visitor's own time zone. */
	function warsawNow() {
		var d = new Date();
		try {
			var p = {};
			new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Warsaw', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false })
				.formatToParts(d).forEach(function (x) { p[x.type] = x.value; });
			return new Date(+p.year, +p.month - 1, +p.day, +p.hour % 24, +p.minute);
		} catch (e) { return d; }
	}
	function isoDay(d) { var n = d.getDay(); return n === 0 ? 7 : n; }
	function hm(t) { return parseInt(t.slice(0, 2), 10) * 60 + parseInt(t.slice(3, 5), 10); }
	function tl(t) { return String(parseInt(t.slice(0, 2), 10)) + ':' + t.slice(3, 5); }
	function inLabel(min) {
		if (min < 60) { return 'za ' + min + ' min'; }
		if (min < 1440) { var h = Math.floor(min / 60), m = min % 60; return 'za ' + h + ' godz.' + (m ? ' ' + m + ' min' : ''); }
		var dd = Math.round(min / 1440); return 'za ' + dd + ' ' + (dd === 1 ? 'dzień' : 'dni');
	}
	function massesFor(data, n) { return n === 7 ? data.masses.sun : (n === 6 ? data.masses.sat : data.masses.wk); }
	function massTime(data, m, n) { return data.advent && m.advent && n < 7 ? m.advent : m.time; }

	function updateInfo(el, data) {
		var now = warsawNow();
		var today = isoDay(now);
		var nowMin = now.getHours() * 60 + now.getMinutes();

		// Next Mass.
		var found = null;
		for (var add = 0; add < 8 && !found; add++) {
			var n = ((today - 1 + add) % 7) + 1;
			var times = massesFor(data, n).map(function (m) { return massTime(data, m, n); }).sort();
			for (var k = 0; k < times.length; k++) {
				var mins = add * 1440 + hm(times[k]) - nowMin;
				if (mins > 0) { found = { add: add, n: n, time: times[k], mins: mins }; break; }
			}
		}
		// Exact Mass times from the intentions plugin win over the weekly schedule.
		if (data.slots && data.slots.length) {
			var todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
			for (var si = 0; si < data.slots.length; si++) {
				var sl = data.slots[si];
				var at = new Date(+sl.slice(0, 4), +sl.slice(5, 7) - 1, +sl.slice(8, 10), +sl.slice(11, 13), +sl.slice(14, 16));
				var diff = Math.floor((at - now) / 60000);
				if (diff > 0) {
					var dAdd = Math.round((new Date(at.getFullYear(), at.getMonth(), at.getDate()) - todayStart) / 86400000);
					found = { add: dAdd, n: isoDay(at), time: sl.slice(11, 16), mins: diff };
					break;
				}
			}
		}
		var box = el.querySelector('[data-next-mass]');
		if (box && found) {
			box.querySelector('[data-nm-day]').textContent = found.add === 0 ? 'dziś' : (found.add === 1 ? 'jutro' : WD[found.n]);
			box.querySelector('[data-nm-time]').textContent = tl(found.time);
			box.querySelector('[data-nm-in]').textContent = inLabel(found.mins);
		}

		// Today's column, past and next Mass times.
		var key = today === 7 ? 'sun' : (today === 6 ? 'sat' : 'wk');
		Array.prototype.forEach.call(el.querySelectorAll('[data-mass-col]'), function (col) {
			var isToday = col.getAttribute('data-mass-col') === key;
			col.classList.toggle('is-today', isToday);
			Array.prototype.forEach.call(col.querySelectorAll('[data-mass-time]'), function (li) {
				var t = data.advent && li.getAttribute('data-mass-advent') && today < 7 ? li.getAttribute('data-mass-advent') : li.getAttribute('data-mass-time');
				li.classList.toggle('is-past', isToday && hm(t) <= nowMin);
				li.classList.toggle('is-next', isToday && !!found && found.add === 0 && found.time === t);
			});
		});

		// Parish office.
		var st = el.querySelector('[data-office-status]');
		if (st && data.office.length) {
			var firstFri = function (d) { return isoDay(d) === 5 && d.getDate() <= 7; };
			var open = null, nextOpen = null;
			data.office.forEach(function (h) {
				if (h.day === today && nowMin >= hm(h.from) && nowMin < hm(h.to) && !(data.firstFriday && firstFri(now))) { open = h; }
			});
			if (!open) {
				for (var a = 0; a < 15 && !nextOpen; a++) {
					var day = new Date(now.getFullYear(), now.getMonth(), now.getDate() + a);
					if (data.firstFriday && firstFri(day)) { continue; }
					for (var j = 0; j < data.office.length; j++) {
						var h = data.office[j];
						if (h.day === isoDay(day) && !(a === 0 && nowMin >= hm(h.from))) { nextOpen = { a: a, h: h }; break; }
					}
				}
			}
			st.classList.toggle('is-open', !!open);
			var txt = 'Zamknięte';
			if (open) { txt = 'Otwarte teraz · do ' + tl(open.to); }
			else if (nextOpen) {
				var when = nextOpen.a === 0 ? 'dziś' : (nextOpen.a === 1 ? 'jutro' : (nextOpen.h.day === 2 ? 'we ' : 'w ') + WD_ACC[nextOpen.h.day]);
				txt = 'Zamknięte · otwieramy ' + when + ' o ' + tl(nextOpen.h.from);
			}
			st.querySelector('span').textContent = txt;
			Array.prototype.forEach.call(el.querySelectorAll('[data-office-day]'), function (li) {
				li.classList.toggle('is-today', +li.getAttribute('data-office-day') === today);
			});
		}
	}

	function initInfo(root) {
		Array.prototype.forEach.call(root.querySelectorAll('[data-pio-info]'), function (el) {
			var data;
			try { data = JSON.parse(el.getAttribute('data-pio-info')); } catch (e) { return; }
			updateInfo(el, data);
			window.setInterval(function () { updateInfo(el, data); }, 30000);
		});
		root.addEventListener('click', function (ev) {
			var btn = ev.target.closest && ev.target.closest('[data-map-load]');
			if (!btn) { return; }
			var map = btn.closest('[data-map]');
			var frame = document.createElement('iframe');
			frame.src = map.getAttribute('data-map');
			frame.title = 'Mapa dojazdu';
			frame.loading = 'lazy';
			frame.referrerPolicy = 'no-referrer-when-downgrade';
			frame.setAttribute('allowfullscreen', '');
			map.appendChild(frame);
			map.classList.add('is-loaded');
		});
	}

	/* ------------------------------------------------------------ liturgy of the day: switch days */

	function initLiturgy(root) {
		Array.prototype.forEach.call(root.querySelectorAll('[data-pio-liturgy]'), function (sec) {
			var tabs = sec.querySelectorAll('[data-lday]');
			var panels = sec.querySelectorAll('.pio-liturgy__panel');
			function show(i, focus) {
				Array.prototype.forEach.call(tabs, function (t, k) {
					var on = k === i;
					t.classList.toggle('is-active', on);
					t.setAttribute('aria-selected', on ? 'true' : 'false');
					t.setAttribute('tabindex', on ? '0' : '-1');
					if (on && focus) { t.focus(); }
				});
				Array.prototype.forEach.call(panels, function (p, k) {
					p.hidden = k !== i;
					p.classList.toggle('is-active', k === i);
					if (k === i) { sec.style.setProperty('--lit', p.getAttribute('data-color')); }
				});
			}
			Array.prototype.forEach.call(tabs, function (t, i) {
				t.addEventListener('click', function () { show(i, false); });
				t.addEventListener('keydown', function (e) {
					var to = null;
					if (e.key === 'ArrowRight') { to = Math.min(tabs.length - 1, i + 1); }
					if (e.key === 'ArrowLeft') { to = Math.max(0, i - 1); }
					if (e.key === 'Home') { to = 0; }
					if (e.key === 'End') { to = tabs.length - 1; }
					if (to !== null) { e.preventDefault(); show(to, true); }
				});
			});
		});
	}

	/* ------------------------------------------------------------ boot */

	function boot() {
		initGalleries(document);
		Array.prototype.forEach.call(document.querySelectorAll('.pio-news'), initFilter);
		initCountdowns(document);
		initTimelines(document);
		bindTimelineEvents(document);
		initCopy(document);
		initCarousels(document);
		initInfo(document);
		initLiturgy(document);

		// TEC list view swaps its markup over AJAX (search, next page).
		if (window.jQuery) {
			window.jQuery(document).on('afterAjaxSuccess.tribeEvents', function (e) {
				initTimelines(e.target && e.target.querySelectorAll ? e.target : document);
			});
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
