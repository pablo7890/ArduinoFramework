/**
 * Pio Gazeta – interactions. No dependencies.
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
		var chips = section.querySelectorAll('.pio-chip');
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

	/* ------------------------------------------------------------ copy link */

	function initCopy(root) {
		root.addEventListener('click', function (ev) {
			var btn = ev.target.closest && ev.target.closest('.pio-copy');
			if (!btn) { return; }
			var url = btn.getAttribute('data-copy');
			var label = btn.querySelector('span');
			var done = function () {
				btn.classList.add('is-done');
				label.textContent = 'Skopiowano link';
				window.setTimeout(function () {
					btn.classList.remove('is-done');
					label.textContent = 'Kopiuj link do wydarzenia';
				}, 2200);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done, function () { window.prompt('Skopiuj link:', url); });
			} else {
				window.prompt('Skopiuj link:', url);
			}
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
