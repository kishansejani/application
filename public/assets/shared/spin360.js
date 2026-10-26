/*!
 * Spin360 – lightweight 360° product viewer (no dependencies).
 *
 *   <div class="spin360" data-spin360
 *        data-frames='["a.jpg","b.jpg", ...]'
 *        data-autoplay="1" data-speed="5" data-sensitivity="5"
 *        data-wheel="1" data-hint="1" data-mode="full|hover" data-eager="0"></div>
 *
 * Interactions
 *   - drag / swipe left-right (with inertia)
 *   - click once, then scroll the mouse wheel (or hold Shift) to rotate
 *   - arrow keys, Home / End, Space = play / pause, + / - = zoom
 *   - range slider, zoom (double-click or button), full screen
 *   - mode="hover": moving the pointer across the image scrubs the rotation (used on cards)
 */
(function (global) {
    'use strict';

    var REDUCED = global.matchMedia && global.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var FINE = !!(global.matchMedia && global.matchMedia('(hover: hover) and (pointer: fine)').matches);
    var ICONS = {
        play: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5Z"/></svg>',
        pause: '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><rect x="6" y="5" width="4.2" height="14" rx="1.2"/><rect x="13.8" y="5" width="4.2" height="14" rx="1.2"/></svg>',
        zoomIn: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2M11 8.5v5M8.5 11h5"/></svg>',
        zoomOut: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2M8.5 11h5"/></svg>',
        full: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>',
        exit: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/></svg>',
        rotate: '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15.5-6.2L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"/><path d="M3 21v-5h5"/></svg>'
    };

    function clamp(v, a, b) { return Math.min(b, Math.max(a, v)); }
    function mod(n, m) { return ((n % m) + m) % m; }
    function num(v, d) { var n = parseFloat(v); return isNaN(n) ? d : n; }
    function flag(v, d) { if (v === undefined || v === null || v === '') return d; return v === '1' || v === 'true' || v === true; }

    function Spin360(el, opts) {
        if (el.__spin360) return el.__spin360;
        el.__spin360 = this;

        var d = el.dataset;
        var frames = opts && opts.frames;
        if (!frames) { try { frames = JSON.parse(d.frames || '[]'); } catch (e) { frames = []; } }

        this.el = el;
        this.frames = frames.filter(Boolean);
        this.n = this.frames.length;
        this.mode = d.mode === 'hover' ? 'hover' : 'full';
        // cards (mode=hover) scrub with the mouse; on touch screens they slowly auto-rotate instead
        this.passive = this.mode === 'hover' && !FINE;
        this.autoplay = !REDUCED && flag(d.autoplay, true) && (this.mode === 'full' || this.passive);
        this.speed = clamp(num(d.speed, 5), 1, 10);
        this.sens = clamp(num(d.sensitivity, 5), 1, 10);
        this.wheel = flag(d.wheel, true);
        this.hint = flag(d.hint, true);

        this.images = new Array(this.n);
        this.loaded = 0;
        this.ready = false;
        this.pos = clamp(num(d.start, 0), 0, Math.max(0, this.n - 1));
        this.vel = 0;          // frames per ms (inertia)
        this.playing = false;
        this.dragging = false;
        this.active = false;   // armed for mouse-wheel rotation
        this.zoom = 1;
        this.pan = { x: 0, y: 0 };
        this.idleTimer = null;
        this.raf = null;
        this.last = 0;

        if (this.n < 2) return;
        this.build();
        if (flag(d.eager, false) || this.mode === 'hover') {
            // hover cards load on first pointer contact; others as soon as they are near the viewport
            if (this.mode !== 'hover') this.load();
        }
        this.observe();
    }

    Spin360.prototype.build = function () {
        var el = this.el, self = this;
        el.classList.add('spin360', 'is-' + this.mode);
        el.setAttribute('tabindex', '0');
        el.setAttribute('role', 'application');
        if (!el.getAttribute('aria-label')) el.setAttribute('aria-label', '360° product view. Use left and right arrow keys or drag to rotate.');
        el.innerHTML = '';

        this.stage = document.createElement('div');
        this.stage.className = 'spin360-stage';
        this.canvas = document.createElement('canvas');
        this.canvas.className = 'spin360-canvas';
        this.stage.appendChild(this.canvas);
        el.appendChild(this.stage);
        this.ctx = this.canvas.getContext('2d');

        // Poster (first frame) shows instantly while the rest loads.
        this.poster = new Image();
        this.poster.decoding = 'async';
        this.poster.onload = function () { self.draw(); };
        this.poster.src = this.frames[0];

        // Loader ring
        this.loader = document.createElement('div');
        this.loader.className = 'spin360-loader';
        this.loader.innerHTML = '<svg viewBox="0 0 44 44"><circle class="trk" cx="22" cy="22" r="19"/><circle class="bar" cx="22" cy="22" r="19"/></svg><span class="pct">0%</span>';
        this.loader.hidden = this.mode === 'hover';
        el.appendChild(this.loader);
        this.bar = this.loader.querySelector('.bar');
        this.pct = this.loader.querySelector('.pct');

        // Badge
        var badge = document.createElement('div');
        badge.className = 'spin360-badge';
        badge.innerHTML = ICONS.rotate + '<b>360°</b>';
        el.appendChild(badge);

        if (this.mode === 'full') {
            // Hint
            if (this.hint) {
                this.hintEl = document.createElement('div');
                this.hintEl.className = 'spin360-hint';
                this.hintEl.innerHTML = '<span class="arrows"><i></i><i></i><i></i></span><span>' + (this.wheel ? 'Drag or click &amp; scroll to rotate' : 'Drag to rotate') + '</span>';
                el.appendChild(this.hintEl);
            }

            // Toolbar
            var ui = document.createElement('div');
            ui.className = 'spin360-ui';
            ui.innerHTML =
                '<button type="button" data-act="play" aria-label="Play or pause rotation">' + ICONS.play + '</button>' +
                '<input type="range" class="spin360-range" min="0" max="' + (this.n - 1) + '" step="1" value="0" aria-label="Rotation">' +
                '<button type="button" data-act="zoom" aria-label="Zoom">' + ICONS.zoomIn + '</button>' +
                '<button type="button" data-act="full" aria-label="Full screen">' + ICONS.full + '</button>';
            el.appendChild(ui);
            this.ui = ui;
            this.btnPlay = ui.querySelector('[data-act=play]');
            this.btnZoom = ui.querySelector('[data-act=zoom]');
            this.btnFull = ui.querySelector('[data-act=full]');
            this.range = ui.querySelector('.spin360-range');

            ui.addEventListener('pointerdown', function (e) { e.stopPropagation(); });
            ui.addEventListener('click', function (e) {
                var b = e.target.closest('button[data-act]');
                if (!b) return;
                e.stopPropagation();
                var act = b.getAttribute('data-act');
                if (act === 'play') self.toggle();
                else if (act === 'zoom') self.setZoom(self.zoom > 1 ? 1 : 2.2);
                else if (act === 'full') self.toggleFull();
            });
            this.range.addEventListener('input', function () {
                self.stop(true); self.pos = +self.range.value; self.vel = 0; self.draw();
            });
        }

        this.bind();
        this.resize();
        if (global.ResizeObserver) { new ResizeObserver(function () { self.resize(); }).observe(this.stage); }
        else { global.addEventListener('resize', function () { self.resize(); }); }
    };

    Spin360.prototype.observe = function () {
        var self = this;
        if (this.mode === 'hover' && !this.passive) return;
        if (!('IntersectionObserver' in global)) { this.load(); return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { self.load(); }
                self.visible = en.isIntersecting;
                if (en.isIntersecting && self.ready && self.autoplay && !self.userStopped) self.play();
                if (!en.isIntersecting) self.stop(false);
            });
        }, { rootMargin: '200px' });
        io.observe(this.el);
    };

    /* ----------------------------------------------------------- loading */
    Spin360.prototype.load = function () {
        if (this.loading || this.ready) return;
        this.loading = true;
        var self = this, next = 0, inflight = 0, MAX = 6;
        // Load order: 0, then every 4th frame (coarse rotation works early), then the rest.
        var order = [], seen = {};
        for (var i = 0; i < this.n; i += 4) { order.push(i); seen[i] = 1; }
        for (var j = 0; j < this.n; j++) if (!seen[j]) order.push(j);

        function pump() {
            while (inflight < MAX && next < order.length) {
                (function (idx) {
                    inflight++;
                    var img = new Image();
                    img.decoding = 'async';
                    img.onload = img.onerror = function (ev) {
                        if (ev && ev.type === 'load') self.images[idx] = img;
                        inflight--; self.loaded++;
                        self.progress();
                        if (self.loaded === self.n) self.done(); else pump();
                    };
                    img.src = self.frames[idx];
                })(order[next++]);
            }
        }
        pump();
    };

    Spin360.prototype.progress = function () {
        var p = this.loaded / this.n;
        if (this.bar) this.bar.style.strokeDashoffset = String(119.4 * (1 - p));
        if (this.pct) this.pct.textContent = Math.round(p * 100) + '%';
        if (this.pos > 0 || this.loaded % 4 === 0) this.draw();
    };

    Spin360.prototype.done = function () {
        this.ready = true;
        this.el.classList.add('is-ready');
        if (this.loader) this.loader.hidden = true;
        this.draw();
        if (this.autoplay && this.visible !== false) this.play();
        this.el.dispatchEvent(new CustomEvent('spin:ready', { bubbles: true }));
    };

    /* ----------------------------------------------------------- drawing */
    Spin360.prototype.resize = function () {
        var r = this.stage.getBoundingClientRect();
        var dpr = Math.min(global.devicePixelRatio || 1, 2);
        this.cw = Math.max(1, Math.round(r.width));
        this.ch = Math.max(1, Math.round(r.height));
        this.canvas.width = Math.round(this.cw * dpr);
        this.canvas.height = Math.round(this.ch * dpr);
        this.dpr = dpr;
        this.draw();
    };

    Spin360.prototype.frameImage = function (idx) {
        var img = this.images[idx];
        if (img && img.naturalWidth) return img;
        for (var k = 1; k < this.n; k++) {          // nearest loaded frame
            var a = this.images[mod(idx - k, this.n)], b = this.images[mod(idx + k, this.n)];
            if (a && a.naturalWidth) return a;
            if (b && b.naturalWidth) return b;
        }
        return this.poster && this.poster.naturalWidth ? this.poster : null;
    };

    Spin360.prototype.draw = function () {
        if (!this.ctx) return;
        var idx = mod(Math.round(this.pos), this.n);
        this.index = idx;
        var img = this.frameImage(idx), c = this.ctx, dpr = this.dpr || 1;
        c.setTransform(1, 0, 0, 1, 0, 0);
        c.clearRect(0, 0, this.canvas.width, this.canvas.height);
        if (this.range && !this.draggingRange) this.range.value = idx;
        if (!img) return;
        var iw = img.naturalWidth, ih = img.naturalHeight;
        var s = Math.min(this.cw / iw, this.ch / ih) * this.zoom;
        var w = iw * s, h = ih * s;
        var x = (this.cw - w) / 2 + this.pan.x, y = (this.ch - h) / 2 + this.pan.y;
        c.setTransform(dpr, 0, 0, dpr, 0, 0);
        c.imageSmoothingQuality = 'high';
        c.drawImage(img, x, y, w, h);
    };

    /* ----------------------------------------------------------- motion */
    Spin360.prototype.tick = function (t) {
        var self = this;
        var dt = this.last ? Math.min(64, t - this.last) : 16;
        this.last = t;
        var moved = false;

        if (!this.dragging && Math.abs(this.vel) > 0.0004) {      // inertia
            this.pos += this.vel * dt;
            this.vel *= Math.pow(0.94, dt / 16);
            moved = true;
        } else if (this.playing && !this.dragging) {
            this.pos += (this.n * 0.04 * this.speed / 1000) * dt;
            moved = true;
        }
        if (moved) { this.pos = mod(this.pos, this.n); this.draw(); }

        if (this.playing || Math.abs(this.vel) > 0.0004 || this.dragging) {
            this.raf = requestAnimationFrame(function (tt) { self.tick(tt); });
        } else {
            this.raf = null; this.last = 0;
        }
    };

    Spin360.prototype.kick = function () {
        var self = this;
        if (this.raf) return;
        this.last = 0;
        this.raf = requestAnimationFrame(function (t) { self.tick(t); });
    };

    Spin360.prototype.play = function () {
        if (this.n < 2 || !this.ready) return;
        this.playing = true; this.userStopped = false;
        if (this.btnPlay) { this.btnPlay.innerHTML = ICONS.pause; this.btnPlay.classList.add('on'); }
        this.kick();
    };

    Spin360.prototype.stop = function (byUser) {
        this.playing = false;
        if (byUser) this.userStopped = true;
        if (this.btnPlay) { this.btnPlay.innerHTML = ICONS.play; this.btnPlay.classList.remove('on'); }
    };

    Spin360.prototype.toggle = function () { if (this.playing) this.stop(true); else this.play(); };

    Spin360.prototype.scheduleResume = function () {
        var self = this;
        clearTimeout(this.idleTimer);
        if (!this.autoplay || this.userStopped) return;
        this.idleTimer = setTimeout(function () { if (!self.dragging && self.zoom === 1) self.play(); }, 3500);
    };

    Spin360.prototype.setZoom = function (z) {
        this.zoom = clamp(z, 1, 4);
        if (this.zoom === 1) this.pan = { x: 0, y: 0 };
        this.el.classList.toggle('is-zoomed', this.zoom > 1);
        if (this.btnZoom) this.btnZoom.innerHTML = this.zoom > 1 ? ICONS.zoomOut : ICONS.zoomIn;
        if (this.zoom > 1) this.stop(true);
        this.draw();
    };

    Spin360.prototype.toggleFull = function () {
        var el = this.el, self = this;
        var inFs = document.fullscreenElement === el || el.classList.contains('is-fake-fs');
        if (!inFs) {
            if (el.requestFullscreen) { el.requestFullscreen().catch(function () { el.classList.add('is-fake-fs'); }); }
            else { el.classList.add('is-fake-fs'); }
        } else {
            if (document.fullscreenElement === el && document.exitFullscreen) document.exitFullscreen();
            el.classList.remove('is-fake-fs');
        }
        setTimeout(function () { self.afterFs(); }, 120);
    };

    Spin360.prototype.afterFs = function () {
        var on = document.fullscreenElement === this.el || this.el.classList.contains('is-fake-fs');
        this.el.classList.toggle('is-fs', on);
        if (this.btnFull) this.btnFull.innerHTML = on ? ICONS.exit : ICONS.full;
        this.resize();
    };

    /* ----------------------------------------------------------- input */
    Spin360.prototype.bind = function () {
        var self = this, el = this.el;
        var startX = 0, startPos = 0, lastX = 0, lastT = 0, panStart = null, pointers = {};

        function pxPerFrame() {
            var rev = 0.55 + self.sens * 0.1;                 // revolutions per element width
            return Math.max(1.2, self.cw / (self.n * rev));
        }

        function down(e) {
            if (e.button !== undefined && e.button !== 0) return;
            if (self.mode === 'hover') return;               // cards: hover only, taps open the product
            pointers[e.pointerId] = true;
            self.dragging = true; self.vel = 0;
            self.active = true; el.classList.add('is-active', 'is-dragging');
            if (self.hintEl) self.hintEl.classList.add('gone');
            clearTimeout(self.idleTimer);
            if (self.playing) self.stop(false);
            startX = lastX = e.clientX; startPos = self.pos; lastT = performance.now();
            if (self.zoom > 1) panStart = { x: e.clientX, y: e.clientY, px: self.pan.x, py: self.pan.y };
            try { el.setPointerCapture(e.pointerId); } catch (err) {}
            self.load();
            self.kick();
        }
        function move(e) {
            if (!self.dragging) return;
            if (panStart) {                                    // zoomed: pan the image
                var lim = Math.max(self.cw, self.ch) * (self.zoom - 1) / 2;
                self.pan.x = clamp(panStart.px + e.clientX - panStart.x, -lim, lim);
                self.pan.y = clamp(panStart.py + e.clientY - panStart.y, -lim, lim);
                self.draw(); return;
            }
            var now = performance.now(), dx = e.clientX - startX;
            self.pos = startPos - dx / pxPerFrame();
            var dtt = Math.max(1, now - lastT);
            var inst = -(e.clientX - lastX) / pxPerFrame() / dtt;
            self.vel = self.vel * 0.6 + inst * 0.4;
            lastX = e.clientX; lastT = now;
            self.draw();
        }
        function up(e) {
            if (!pointers[e.pointerId]) return;
            delete pointers[e.pointerId];
            self.dragging = false; panStart = null;
            el.classList.remove('is-dragging');
            if (performance.now() - lastT > 90) self.vel = 0;  // finger rested before release
            self.vel = clamp(self.vel, -0.06, 0.06);
            self.kick();
            self.scheduleResume();
        }

        el.addEventListener('pointerdown', down);
        el.addEventListener('pointermove', move);
        el.addEventListener('pointerup', up);
        el.addEventListener('pointercancel', up);

        // Mouse hover (cards): pointer X scrubs the rotation.
        if (this.mode === 'hover') {
            el.addEventListener('pointerenter', function (e) {
                if (e.pointerType !== 'mouse') return;
                self.load(); el.classList.add('is-hovering');
            });
            el.addEventListener('pointermove', function (e) {
                if (e.pointerType !== 'mouse' || self.dragging) return;
                var r = el.getBoundingClientRect();
                self.pos = clamp((e.clientX - r.left) / r.width, 0, 0.999) * self.n;
                self.draw();
            });
            el.addEventListener('pointerleave', function () { el.classList.remove('is-hovering'); self.pos = 0; self.draw(); });
        }

        // Click activates the wheel; leaving deactivates it.
        el.addEventListener('mouseleave', function () { self.active = false; el.classList.remove('is-active'); });
        el.addEventListener('focus', function () { self.active = true; el.classList.add('is-active'); self.load(); });
        el.addEventListener('blur', function () { self.active = false; el.classList.remove('is-active'); });

        el.addEventListener('wheel', function (e) {
            if (self.mode !== 'full') return;
            if (self.zoom > 1 && !e.shiftKey) { return; }
            if (!(self.wheel && (self.active || e.shiftKey))) return;   // otherwise let the page scroll
            e.preventDefault();
            if (self.hintEl) self.hintEl.classList.add('gone');
            self.stop(false); clearTimeout(self.idleTimer);
            var delta = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
            self.pos = mod(self.pos + clamp(delta, -120, 120) / 18 * (0.5 + self.sens * 0.1), self.n);
            self.vel = 0; self.draw(); self.scheduleResume();
        }, { passive: false });

        el.addEventListener('dblclick', function (e) {
            if (self.mode !== 'full' || (self.ui && self.ui.contains(e.target))) return;
            self.setZoom(self.zoom > 1 ? 1 : 2.2);
        });

        el.addEventListener('keydown', function (e) {
            if (self.mode !== 'full') return;
            var k = e.key, handled = true;
            if (k === 'ArrowLeft') { self.stop(true); self.pos = mod(self.pos - 1, self.n); self.draw(); }
            else if (k === 'ArrowRight') { self.stop(true); self.pos = mod(self.pos + 1, self.n); self.draw(); }
            else if (k === 'Home') { self.pos = 0; self.draw(); }
            else if (k === 'End') { self.pos = self.n - 1; self.draw(); }
            else if (k === ' ') { self.toggle(); }
            else if (k === '+' || k === '=') { self.setZoom(self.zoom + 0.6); }
            else if (k === '-') { self.setZoom(self.zoom - 0.6); }
            else if (k === 'Escape' && el.classList.contains('is-fake-fs')) { el.classList.remove('is-fake-fs'); self.afterFs(); }
            else handled = false;
            if (handled) e.preventDefault();
        });

        document.addEventListener('fullscreenchange', function () { self.afterFs(); });
        this.range && this.range.addEventListener('pointerdown', function () { self.draggingRange = true; });
        this.range && this.range.addEventListener('pointerup', function () { self.draggingRange = false; });
    };

    /* ----------------------------------------------------------- static helpers */
    Spin360.initAll = function (root) {
        (root || document).querySelectorAll('[data-spin360]').forEach(function (el) { new Spin360(el); });
    };
    /** Re-measure viewers that were hidden when created (e.g. inside a tab). */
    Spin360.refresh = function (root) {
        (root || document).querySelectorAll('[data-spin360]').forEach(function (el) {
            if (el.__spin360) { el.__spin360.resize(); el.__spin360.load(); }
        });
    };

    global.Spin360 = Spin360;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { Spin360.initAll(); });
    else Spin360.initAll();
})(window);
