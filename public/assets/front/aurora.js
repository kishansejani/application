/*!
 * Aurora – storefront motion layer. Reads <html data-fx="…"> (set from Admin → Settings → Design & Experience)
 * and enables: scroll progress, scroll reveal, 3D tilt, cursor spotlight.
 * Everything degrades silently: with JS off or reduced-motion nothing is hidden.
 */
(function () {
    'use strict';
    var html = document.documentElement;
    var fx = (html.getAttribute('data-fx') || '').split(/\s+/);
    var on = function (n) { return fx.indexOf(n) > -1; };
    var reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;
    var raf = window.requestAnimationFrame || function (f) { return setTimeout(f, 16); };

    /* ---------------------------------------------------- scroll progress */
    function progress() {
        if (!on('progress')) return;
        var bar = document.createElement('div');
        bar.id = 'fxProgress'; bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);
        var ticking = false;
        function update() {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, window.scrollY / h) : 0) + ')';
            ticking = false;
        }
        window.addEventListener('scroll', function () { if (!ticking) { ticking = true; raf(update); } }, { passive: true });
        update();
    }

    /* ---------------------------------------------------------- reveal */
    function reveal() {
        if (!on('reveal') || reduced || !('IntersectionObserver' in window)) return;
        var scope = document.getElementById('main') || document.body;
        var nodes = scope.querySelectorAll(
            ':scope > section, :scope > div > section, :scope > div > div > section, [data-reveal-group] > *, .fx-rail > *, .fx-spin-rail > *, .grid > article, .grid > .fx-card, .mk-grid > *, .mk-cats > *, .mk-rail > *'
        );
        var list = [];
        nodes.forEach(function (el) {
            if (el.closest('[data-slider], [data-no-reveal], header, footer, .fx-overlay') || el.hasAttribute('data-reveal')) return;
            var r = el.getBoundingClientRect();
            if (r.top < window.innerHeight * 0.92 && r.bottom > 0 && !el.parentElement.classList.contains('grid')) {
                // first-screen sections are visible immediately: no flash of hidden content
                if (el.tagName === 'SECTION') return;
            }
            list.push(el);
        });
        if (!list.length) return;

        var io = new IntersectionObserver(function (entries) {
            var batch = entries.filter(function (e) { return e.isIntersecting; });
            batch.forEach(function (e, i) {
                var el = e.target;
                el.style.setProperty('--rv-d', Math.min(i, 6) * 70 + 'ms');
                el.classList.add('is-in');
                io.unobserve(el);
                setTimeout(function () {                       // hand control back to the element's own transitions
                    el.removeAttribute('data-reveal'); el.classList.remove('is-in'); el.style.removeProperty('--rv-d');
                }, 1000 + Math.min(i, 6) * 70);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -6% 0px' });

        list.forEach(function (el) { el.setAttribute('data-reveal', ''); io.observe(el); });
    }

    /* ------------------------------------------------------ tilt + glow */
    function pointerFx() {
        if (!fine || reduced) return;
        var wantTilt = on('tilt'), wantGlow = on('glow');
        if (!wantTilt && !wantGlow) return;

        if (wantTilt) {   // tag product cards and spotlight cards
            document.querySelectorAll('.fx-spin-card').forEach(function (c) { c.setAttribute('data-tilt', ''); });
        }

        var pending = null, scheduled = false;
        document.addEventListener('pointermove', function (e) {
            if (e.pointerType !== 'mouse') return;
            var t = e.target.closest && e.target.closest('.fx-card, [data-tilt]');
            if (!t) return;
            pending = { t: t, x: e.clientX, y: e.clientY };
            if (scheduled) return;
            scheduled = true;
            raf(function () {
                scheduled = false;
                if (!pending) return;
                var p = pending; pending = null;
                var r = p.t.getBoundingClientRect(), px = (p.x - r.left) / r.width, py = (p.y - r.top) / r.height;
                if (wantGlow) { p.t.style.setProperty('--mx', (p.x - r.left) + 'px'); p.t.style.setProperty('--my', (p.y - r.top) + 'px'); }
                if (wantTilt && p.t.hasAttribute('data-tilt')) {
                    var max = 7;
                    p.t.classList.add('is-tilting');
                    p.t.style.setProperty('--ry', ((px - 0.5) * max * 2).toFixed(2) + 'deg');
                    p.t.style.setProperty('--rx', ((0.5 - py) * max * 2).toFixed(2) + 'deg');
                    p.t.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
                    p.t.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
                }
            });
        }, { passive: true });

        document.addEventListener('pointerout', function (e) {
            var t = e.target.closest && e.target.closest('[data-tilt]');
            if (!t || (e.relatedTarget && t.contains(e.relatedTarget))) return;
            t.classList.remove('is-tilting');
            t.style.setProperty('--rx', '0deg'); t.style.setProperty('--ry', '0deg');
        }, { passive: true });
    }

    function init() { progress(); reveal(); pointerFx(); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
