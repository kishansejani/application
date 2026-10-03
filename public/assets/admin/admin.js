/* ==========================================================================
   Fresh Express – Admin Console runtime (v2.0)
   Sidebar · theme · full screen · command palette · toasts · DataTables
   export toolbar (Excel / CSV / PDF portrait-landscape / Print / Columns).
   ========================================================================== */
(function ($, window, document) {
    'use strict';

    var cfg = window.AdminConfig || {};
    var I18N = cfg.i18n || {};
    var t = function (str) { return (I18N && I18N[str]) || str; };
    window.__ = t;
    var html = document.documentElement;
    var store = {
        get: function (k, d) { try { var v = localStorage.getItem(k); return v === null ? d : v; } catch (e) { return d; } },
        set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) { /* ignore */ } }
    };
    var isDesktop = function () { return window.matchMedia('(min-width: 1024px)').matches; };
    var escapeHtml = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; }); };
    var cssVar = function (name, fallback) { var v = getComputedStyle(html).getPropertyValue(name).trim(); return v || fallback; };

    if ($) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-Requested-With': 'XMLHttpRequest' } });
    }

    /* ------------------------------------------------------------------ Toasts */
    var Toast = {
        icons: { success: 'ph-check-circle', error: 'ph-x-circle', warning: 'ph-warning', info: 'ph-info' },
        show: function (type, message, title, timeout) {
            if (!message) return;
            type = this.icons[type] ? type : 'info';
            timeout = timeout || (type === 'error' ? 6000 : 4000);
            var stack = document.getElementById('toastStack');
            if (!stack) return;
            var el = document.createElement('div');
            el.className = 'toast toast-' + type;
            el.setAttribute('role', type === 'error' ? 'alert' : 'status');
            el.innerHTML =
                '<span class="toast-icon"><i class="ph-fill ' + this.icons[type] + '"></i></span>' +
                '<div class="toast-body">' + (title ? '<strong class="block">' + escapeHtml(title) + '</strong>' : '') + escapeHtml(message) + '</div>' +
                '<button type="button" class="toast-close" aria-label="Dismiss"><i class="ph ph-x"></i></button>' +
                '<span class="toast-bar" style="animation-duration:' + timeout + 'ms"></span>';
            stack.appendChild(el);
            var remove = function () {
                if (!el.parentNode) return;
                el.classList.add('is-leaving');
                setTimeout(function () { el.remove(); }, 200);
            };
            var timer = setTimeout(remove, timeout);
            el.addEventListener('mouseenter', function () { clearTimeout(timer); el.querySelector('.toast-bar').style.animationPlayState = 'paused'; });
            el.addEventListener('mouseleave', function () { timer = setTimeout(remove, 1500); el.querySelector('.toast-bar').style.animationPlayState = 'running'; });
            el.querySelector('.toast-close').addEventListener('click', remove);
            while (stack.children.length > 4) stack.firstElementChild.remove();
        }
    };
    window.AdminToast = Toast;
    // toastr-compatible shim (existing pages call toastr.success(...))
    window.toastr = {
        success: function (m, t) { Toast.show('success', m, t); },
        error: function (m, t) { Toast.show('error', m, t); },
        warning: function (m, t) { Toast.show('warning', m, t); },
        info: function (m, t) { Toast.show('info', m, t); },
        options: {}
    };

    /* ------------------------------------------------------------------ SweetAlert defaults */
    if (window.Swal) {
        var baseSwal = window.Swal;
        window.Swal = baseSwal.mixin({ reverseButtons: true });
    }

    /* ------------------------------------------------------------------ Theme */
    var Theme = {
        icons: { light: 'ph-sun', dark: 'ph-moon-stars', system: 'ph-desktop' },
        current: function () { return store.get('admin_theme_mode', cfg.defaultTheme || 'system'); },
        apply: function (mode) {
            var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            html.classList.toggle('dark', dark);
            var icon = document.getElementById('themeCurrentIcon');
            if (icon) icon.className = 'ph ' + (this.icons[mode] || 'ph-desktop') + ' text-lg';
            document.querySelectorAll('[data-theme-set]').forEach(function (b) { b.classList.toggle('is-selected', b.getAttribute('data-theme-set') === mode); });
            document.dispatchEvent(new CustomEvent('admin:theme', { detail: { mode: mode, dark: dark } }));
        },
        set: function (mode) { store.set('admin_theme_mode', mode); this.apply(mode); },
        toggle: function () { this.set(html.classList.contains('dark') ? 'light' : 'dark'); }
    };
    window.setThemeMode = function (m) { Theme.set(m); }; // legacy API (settings page)
    window.AdminTheme = Theme;

    /* ------------------------------------------------------------------ Sidebar */
    var Sidebar = {
        toggle: function () {
            if (isDesktop()) {
                var mini = !html.classList.contains('sidebar-mini');
                html.classList.toggle('sidebar-mini', mini);
                store.set('admin_sidebar_collapsed', mini ? 'true' : 'false');
                setTimeout(function () { Sidebar.adjustTables(); }, 280);
            } else {
                html.classList.toggle('sidebar-open');
            }
        },
        close: function () { html.classList.remove('sidebar-open'); },
        adjustTables: function () {
            if ($ && $.fn.dataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                if ($.fn.dataTable.Responsive) $.fn.dataTable.tables({ visible: true, api: true }).responsive.recalc();
            }
            window.dispatchEvent(new Event('resize'));
        },
        initGroups: function () {
            var collapsed = [];
            try { collapsed = JSON.parse(store.get('admin_sidebar_groups', '[]')) || []; } catch (e) { collapsed = []; }
            document.querySelectorAll('.sb-group').forEach(function (g) {
                var key = g.getAttribute('data-group');
                if (collapsed.indexOf(key) !== -1 && !g.querySelector('.sb-item.is-active')) g.classList.add('is-collapsed');
            });
            document.querySelectorAll('[data-group-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var g = btn.closest('.sb-group');
                    g.classList.toggle('is-collapsed');
                    var keys = Array.prototype.map.call(document.querySelectorAll('.sb-group.is-collapsed'), function (x) { return x.getAttribute('data-group'); });
                    store.set('admin_sidebar_groups', JSON.stringify(keys));
                });
            });
        },
        initFilter: function () {
            var input = document.getElementById('sidebarFilter');
            if (!input) return;
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase(), any = false;
                document.querySelectorAll('.sb-group').forEach(function (g) {
                    var groupHit = false;
                    g.querySelectorAll('.sb-item').forEach(function (a) {
                        var hit = !q || a.getAttribute('data-label').indexOf(q) !== -1;
                        a.parentElement.style.display = hit ? '' : 'none';
                        if (hit) groupHit = true;
                    });
                    g.style.display = groupHit ? '' : 'none';
                    if (q && groupHit) g.classList.remove('is-collapsed');
                    if (groupHit) any = true;
                });
                document.getElementById('sidebarEmpty').classList.toggle('hidden', any);
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    var first = Array.prototype.find.call(document.querySelectorAll('.sb-item'), function (a) { return a.parentElement.style.display !== 'none'; });
                    if (first) window.location.href = first.href;
                }
                if (e.key === 'Escape') { input.value = ''; input.dispatchEvent(new Event('input')); input.blur(); }
            });
        },
        scrollActiveIntoView: function () {
            var active = document.querySelector('.sb-item.is-active');
            var nav = document.getElementById('sidebarNav');
            if (active && nav && active.offsetTop > nav.clientHeight - 80) nav.scrollTop = active.offsetTop - nav.clientHeight / 2;
        }
    };
    window.AdminSidebar = Sidebar;

    /* ------------------------------------------------------------------ Full screen */
    var Fullscreen = {
        supported: function () { return !!(document.fullscreenEnabled || document.webkitFullscreenEnabled); },
        active: function () { return !!(document.fullscreenElement || document.webkitFullscreenElement); },
        toggle: function () {
            if (!this.supported()) { Toast.show('info', 'Full screen is not supported by this browser.'); return; }
            if (this.active()) {
                (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            } else {
                var el = document.documentElement;
                var req = el.requestFullscreen || el.webkitRequestFullscreen;
                var p = req.call(el);
                if (p && p.catch) p.catch(function () { Toast.show('warning', 'The browser blocked full screen mode.'); });
            }
        },
        sync: function () {
            var btn = document.getElementById('fullscreenToggle');
            if (!btn) return;
            var on = Fullscreen.active();
            btn.innerHTML = '<i class="ph ' + (on ? 'ph-corners-in' : 'ph-corners-out') + ' text-lg"></i>';
            btn.setAttribute('title', on ? 'Exit full screen (Esc)' : 'Full screen (Ctrl+Shift+F)');
            html.classList.toggle('is-fullscreen', on);
            setTimeout(Sidebar.adjustTables, 150);
        }
    };
    window.AdminFullscreen = Fullscreen;

    /* ------------------------------------------------------------------ Dropdowns */
    function closeDropdowns(except) {
        document.querySelectorAll('[data-dropdown]').forEach(function (d) {
            if (d === except) return;
            var m = d.querySelector('[data-dropdown-menu]');
            var t = d.querySelector('[data-dropdown-toggle]');
            if (m) m.classList.remove('is-open');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    }

    /* ------------------------------------------------------------------ Command palette */
    var Palette = {
        el: null, input: null, list: null, items: [], filtered: [], index: 0,
        init: function () {
            this.el = document.getElementById('commandPalette');
            this.input = document.getElementById('cpInput');
            this.list = document.getElementById('cpList');
            if (!this.el) return;
            this.items = (cfg.palette || []).concat([
                { label: t('Toggle dark / light mode'), icon: 'moon-stars', action: 'theme', hint: 'Appearance' },
                { label: t('Toggle full screen'), icon: 'corners-out', action: 'fullscreen', hint: 'View' },
                { label: t('Collapse / expand sidebar'), icon: 'sidebar-simple', action: 'sidebar', hint: 'View' },
                { label: t('Keyboard shortcuts'), icon: 'keyboard', action: 'shortcuts', hint: 'Help' }
            ]);
            var self = this;
            this.input.addEventListener('input', function () { self.index = 0; self.render(); });
            this.input.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); self.move(1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); self.move(-1); }
                else if (e.key === 'Enter') { e.preventDefault(); self.run(self.filtered[self.index]); }
            });
            this.el.addEventListener('mousedown', function (e) { if (e.target === self.el) self.close(); });
            this.list.addEventListener('click', function (e) {
                var li = e.target.closest('[data-idx]');
                if (li) self.run(self.filtered[+li.getAttribute('data-idx')]);
            });
            this.list.addEventListener('mousemove', function (e) {
                var li = e.target.closest('[data-idx]');
                if (li && +li.getAttribute('data-idx') !== self.index) { self.index = +li.getAttribute('data-idx'); self.highlight(); }
            });
        },
        score: function (label, q) {
            label = label.toLowerCase();
            if (!q) return 1;
            if (label.indexOf(q) === 0) return 3;
            if (label.indexOf(q) !== -1) return 2;
            // fuzzy: all characters in order
            var i = 0;
            for (var c = 0; c < label.length && i < q.length; c++) if (label[c] === q[i]) i++;
            return i === q.length ? 1 : 0;
        },
        render: function () {
            var q = this.input.value.trim().toLowerCase(), self = this;
            this.filtered = this.items
                .map(function (it) { return { it: it, s: self.score(it.label + ' ' + (it.hint || ''), q) }; })
                .filter(function (x) { return x.s > 0; })
                .sort(function (a, b) { return b.s - a.s; })
                .map(function (x) { return x.it; });
            if (!this.filtered.length) {
                this.list.innerHTML = '<li class="cp-empty"><i class="ph-duotone ph-magnifying-glass text-3xl block mb-2"></i>No results for “' + escapeHtml(q) + '”</li>';
                return;
            }
            var out = '';
            this.filtered.forEach(function (it, i) {
                var label = escapeHtml(it.label);
                if (q) {
                    var pos = it.label.toLowerCase().indexOf(q);
                    if (pos !== -1) label = escapeHtml(it.label.slice(0, pos)) + '<mark>' + escapeHtml(it.label.slice(pos, pos + q.length)) + '</mark>' + escapeHtml(it.label.slice(pos + q.length));
                }
                out += '<li class="cp-item" role="option" data-idx="' + i + '"><i class="cp-ic ph ph-' + escapeHtml(it.icon || 'arrow-right') + '"></i><span>' + label + '</span><span class="cp-hint">' + escapeHtml(it.hint || '') + '</span></li>';
            });
            this.list.innerHTML = out;
            this.highlight();
        },
        highlight: function () {
            var self = this;
            this.list.querySelectorAll('.cp-item').forEach(function (li, i) {
                li.classList.toggle('is-active', i === self.index);
                li.setAttribute('aria-selected', i === self.index ? 'true' : 'false');
                if (i === self.index) li.scrollIntoView({ block: 'nearest' });
            });
        },
        move: function (d) { if (!this.filtered.length) return; this.index = (this.index + d + this.filtered.length) % this.filtered.length; this.highlight(); },
        run: function (it) {
            if (!it) return;
            this.close();
            if (it.action === 'theme') return Theme.toggle();
            if (it.action === 'fullscreen') return Fullscreen.toggle();
            if (it.action === 'sidebar') return Sidebar.toggle();
            if (it.action === 'shortcuts') return Modal.open('shortcutsModal');
            if (it.external) window.open(it.url, '_blank'); else { Progress.start(); window.location.href = it.url; }
        },
        open: function () { if (!this.el) return; closeDropdowns(); this.el.classList.remove('hidden'); this.input.value = ''; this.index = 0; this.render(); setTimeout(function () { Palette.input.focus(); }, 10); },
        close: function () { if (this.el) this.el.classList.add('hidden'); },
        isOpen: function () { return this.el && !this.el.classList.contains('hidden'); }
    };
    window.AdminPalette = Palette;

    var Modal = {
        open: function (id) { closeDropdowns(); var m = document.getElementById(id); if (m) m.classList.remove('hidden'); },
        closeAll: function () { document.querySelectorAll('.cp-overlay').forEach(function (m) { m.classList.add('hidden'); }); }
    };

    /* ------------------------------------------------------------------ Progress bar */
    var Progress = {
        el: function () { return document.getElementById('pageProgress'); },
        start: function () { var e = this.el(); if (!e) return; e.classList.remove('is-done'); void e.offsetWidth; e.classList.add('is-loading'); },
        done: function () { var e = this.el(); if (!e) return; e.classList.add('is-done'); setTimeout(function () { e.classList.remove('is-loading', 'is-done'); }, 400); }
    };
    window.AdminProgress = Progress;

    /* ------------------------------------------------------------------ Gujarati UI layer
       Server-side __() covers the layout & components; this translates the remaining static
       labels (table headers, buttons, card titles, placeholders…) using lang/gu.json. */
    var I18nDom = {
        skip: { SCRIPT: 1, STYLE: 1, TEXTAREA: 1, CODE: 1, PRE: 1, OPTION: 0 },
        translateText: function (node) {
            var raw = node.nodeValue, key = raw.replace(/\s+/g, ' ').trim();
            if (!key || key.length > 120) return;
            var tr = I18N[key] || I18N[key.replace(/&/g, '&amp;')];
            if (tr && tr !== key) node.nodeValue = raw.replace(raw.trim(), tr);
        },
        run: function (root) {
            if (!I18N || cfg.locale !== 'gu' || !root) return;
            var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
                acceptNode: function (n) {
                    var el = n.parentElement;
                    if (!el || I18nDom.skip[el.tagName] || el.closest('[data-no-i18n], .no-i18n, [contenteditable]')) return NodeFilter.FILTER_REJECT;
                    return n.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            });
            var nodes = []; while (walker.nextNode()) nodes.push(walker.currentNode);
            nodes.forEach(I18nDom.translateText);
            (root.querySelectorAll ? root.querySelectorAll('[placeholder],[title],[aria-label],[data-confirm-title]') : []).forEach(function (el) {
                if (el.closest('[data-no-i18n]')) return;
                ['placeholder', 'title', 'aria-label', 'data-confirm-title'].forEach(function (a) {
                    var v = el.getAttribute(a); if (v && I18N[v.trim()]) el.setAttribute(a, I18N[v.trim()]);
                });
            });
        }
    };
    window.AdminI18n = I18nDom;

    /* ================================================================== DOM READY */
    document.addEventListener('DOMContentLoaded', function () {
        I18nDom.run(document.body);
        if (cfg.locale === 'gu') {
            new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    if (m.type === 'characterData') { var pe = m.target.parentElement; if (pe && !pe.closest('[data-no-i18n], input, textarea')) I18nDom.translateText(m.target); return; }
                    m.addedNodes.forEach(function (n) {
                        if (n.nodeType === 1 && !n.closest('.fx-sel-panel')) I18nDom.run(n);
                        else if (n.nodeType === 3 && n.parentElement && !n.parentElement.closest('[data-no-i18n], script, style, textarea')) I18nDom.translateText(n);
                    });
                });
            }).observe(document.body, { childList: true, subtree: true, characterData: true });
        }
        Theme.apply(Theme.current());
        Sidebar.initGroups();
        Sidebar.initFilter();
        Sidebar.scrollActiveIntoView();
        Palette.init();

        // Flash messages → toasts
        var f = cfg.flash || {};
        ['success', 'error', 'warning', 'info'].forEach(function (t) { if (f[t]) Toast.show(t, f[t]); });

        // Sidebar controls
        var toggle = document.getElementById('sidebarToggle');
        if (toggle) toggle.addEventListener('click', Sidebar.toggle);
        document.querySelectorAll('[data-sidebar-close]').forEach(function (el) { el.addEventListener('click', Sidebar.close); });
        document.querySelectorAll('.sb-item').forEach(function (a) { a.addEventListener('click', function () { if (!isDesktop()) Sidebar.close(); }); });

        // Theme menu
        document.querySelectorAll('[data-theme-set]').forEach(function (b) {
            b.addEventListener('click', function () { Theme.set(b.getAttribute('data-theme-set')); closeDropdowns(); });
        });
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () { if (Theme.current() === 'system') Theme.apply('system'); });

        // Full screen
        var fsBtn = document.getElementById('fullscreenToggle');
        if (fsBtn) {
            if (!Fullscreen.supported()) fsBtn.style.display = 'none';
            fsBtn.addEventListener('click', function () { Fullscreen.toggle(); });
        }
        document.addEventListener('fullscreenchange', Fullscreen.sync);
        document.addEventListener('webkitfullscreenchange', Fullscreen.sync);

        // Dropdowns
        document.querySelectorAll('[data-dropdown]').forEach(function (d) {
            var t = d.querySelector('[data-dropdown-toggle]'), m = d.querySelector('[data-dropdown-menu]');
            if (!t || !m) return;
            t.setAttribute('aria-expanded', 'false');
            t.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = !m.classList.contains('is-open');
                closeDropdowns(d);
                m.classList.toggle('is-open', open);
                t.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
        document.addEventListener('click', function (e) { if (!e.target.closest('[data-dropdown-menu]')) closeDropdowns(); });

        // Palette / modals
        document.querySelectorAll('[data-palette-open]').forEach(function (b) { b.addEventListener('click', function () { Palette.open(); }); });
        document.querySelectorAll('[data-shortcuts-open]').forEach(function (b) { b.addEventListener('click', function () { Modal.open('shortcutsModal'); }); });
        document.querySelectorAll('[data-modal-close]').forEach(function (b) { b.addEventListener('click', Modal.closeAll); });
        document.querySelectorAll('.cp-overlay').forEach(function (o) { o.addEventListener('mousedown', function (e) { if (e.target === o) Modal.closeAll(); }); });

        // Back to top
        var btt = document.getElementById('backToTop');
        if (btt) {
            var onScroll = function () { btt.classList.toggle('is-visible', window.scrollY > 600); };
            window.addEventListener('scroll', onScroll, { passive: true });
            btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
            onScroll();
        }

        // Navigation progress bar
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a[href]');
            if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || a.target === '_blank' || a.hasAttribute('download') || /^(javascript|mailto|tel):/i.test(href)) return;
            if (a.host !== window.location.host) return;
            Progress.start();
        });
        window.addEventListener('pageshow', function () { Progress.done(); });

        // Prevent double submits + loading state on submit buttons
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (e.defaultPrevented || form.hasAttribute('data-no-loading')) return;
            var btn = e.submitter || form.querySelector('button[type=submit]:not([data-no-loading]), button:not([type]):not([data-no-loading])');
            if (!btn || btn.hasAttribute('data-no-loading')) { Progress.start(); return; }
            if (form.dataset.submitting === '1') { e.preventDefault(); return; }
            form.dataset.submitting = '1';
            Progress.start();
            setTimeout(function () {
                btn.disabled = true;
                btn.classList.add('is-loading');
                btn.style.opacity = '.75';
            }, 0);
            // Safety: re-enable after 12s (e.g. file downloads that do not navigate)
            setTimeout(function () { form.dataset.submitting = ''; btn.disabled = false; btn.classList.remove('is-loading'); btn.style.opacity = ''; Progress.done(); }, 12000);
        }, false);

        // Offline / online indicator
        window.addEventListener('offline', function () { Toast.show('warning', 'You are offline. Changes will not be saved until the connection is back.'); });
        window.addEventListener('online', function () { Toast.show('success', 'Back online.'); });

        // Resize: close mobile drawer when switching to desktop
        var lastDesktop = isDesktop();
        window.addEventListener('resize', function () {
            var d = isDesktop();
            if (d !== lastDesktop) { Sidebar.close(); lastDesktop = d; }
        });
    });

    /* ------------------------------------------------------------------ Keyboard shortcuts */
    document.addEventListener('keydown', function (e) {
        var tag = (e.target.tagName || '').toLowerCase();
        var typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
        var k = (e.key || '').toLowerCase();

        if ((e.ctrlKey || e.metaKey) && (k === 'k' || k === '/')) { e.preventDefault(); Palette.isOpen() ? Palette.close() : Palette.open(); return; }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && k === 'f') { e.preventDefault(); Fullscreen.toggle(); return; }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && k === 'l') { e.preventDefault(); Theme.toggle(); return; }
        if (k === 'escape') {
            Modal.closeAll(); closeDropdowns(); Sidebar.close();
            var fsCard = document.querySelector('.table-card.is-fullscreen');
            if (fsCard) toggleCardFullscreen(fsCard);
            return;
        }
        if (typing || e.ctrlKey || e.metaKey || e.altKey) return;
        if (e.key === '[') { e.preventDefault(); Sidebar.toggle(); }
        else if (e.key === '?') { e.preventDefault(); Modal.open('shortcutsModal'); }
        else if (e.key === '/') {
            var s = document.querySelector('.dataTables_filter input');
            if (s) { e.preventDefault(); s.focus(); s.select(); }
        }
    });

    /* ------------------------------------------------------------------ Delete confirmation */
    if ($) {
        $(document).on('click', '.confirm-delete-btn, [data-confirm]', function (e) {
            var $btn = $(this);
            if ($btn.data('confirmed')) return;
            e.preventDefault();
            var form = $btn.closest('form');
            var isDelete = $btn.hasClass('confirm-delete-btn') || $btn.data('confirm-danger') !== undefined;
            Swal.fire({
                title: $btn.data('confirm-title') || t(isDelete ? 'Delete this record?' : 'Are you sure?'),
                text: $btn.data('confirm') || $btn.data('confirm-text') || (isDelete ? t('This action cannot be undone.') : ''),
                icon: isDelete ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: $btn.data('confirm-button') || t(isDelete ? 'Yes, delete' : 'Continue'),
                cancelButtonText: t('Cancel'),
                customClass: { confirmButton: isDelete ? 'is-danger' : '' },
                focusCancel: isDelete
            }).then(function (r) {
                if (!r.isConfirmed) return;
                if (form.length && ($btn.is('button') || $btn.is('input'))) {
                    Progress.start();
                    $btn.prop('disabled', true).addClass('is-loading');
                    HTMLFormElement.prototype.submit.call(form[0]);
                } else if ($btn.is('a')) {
                    Progress.start();
                    window.location.href = $btn.attr('href');
                }
            });
        });
    }

    /* ------------------------------------------------------------------ Card full screen (tables) */
    function toggleCardFullscreen(target) {
        var card = target.nodeType ? (target.classList.contains('table-card') ? target : target.closest('.table-card, .card, [class*="rounded-"]')) : null;
        if (!card) return;
        if (!card.classList.contains('table-card')) card.classList.add('table-card');
        var on = card.classList.toggle('is-fullscreen');
        document.body.classList.toggle('has-fs-card', on);
        var btn = card.querySelector('.btn-fs');
        if (btn) {
            btn.innerHTML = '<i class="ph ' + (on ? 'ph-corners-in' : 'ph-corners-out') + '"></i>';
            btn.setAttribute('title', on ? 'Exit expanded view (Esc)' : 'Expand table');
        }
        setTimeout(Sidebar.adjustTables, 50);
    }
    window.toggleCardFullscreen = toggleCardFullscreen;

    /* ================================================================== DATATABLES */
    if ($ && $.fn.dataTable) {
        var DT = $.fn.dataTable;
        DT.ext.errMode = 'none';

        var today = function () { var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); };
        var nowLabel = function () { return new Date().toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); };
        var slug = function (s) { return String(s || 'export').replace(/[઀-૿]/g, '').replace(/[^a-z0-9]+/gi, '_').replace(/^_+|_+$/g, '').toLowerCase() || 'export'; };
        var tableTitle = function (dt) { var n = $(dt.table().node()); return n.attr('data-export-title') || cfg.pageTitle || document.title; };

        var cellText = function (data, node) {
            if (node && node.nodeType === 1) {
                var $n = $(node);
                var attr = $n.attr('data-export');
                if (attr === undefined) attr = $n.find('[data-export]').first().attr('data-export');
                if (attr !== undefined) return String(attr).trim();
                var $field = $n.find('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select');
                if ($field.length === 1) return $field.is('select') ? $field.find('option:selected').text().trim() : String($field.val()).trim();
                var $c = $n.clone();
                $c.find('.no-export, .sr-only, script, style, form button.confirm-delete-btn, [data-no-export]').remove();
                $c.find('div, p, li, br, small, .block').before(' ');
                return $c.text().replace(/\s+/g, ' ').trim();
            }
            return $('<div>').html(data == null ? '' : String(data)).text().replace(/\s+/g, ' ').trim();
        };
        var headerText = function (data, col, node) { return cellText(data, node).replace(/\s*[↑↓⇅]\s*$/, ''); };
        var exportColumns = function (idx, data, node) {
            var $th = $(node);
            if ($th.hasClass('no-export')) return false;
            if ($th.hasClass('export-only')) return true; // hidden in the UI, always exported
            try { return $th.closest('table').DataTable().column(idx).visible(); } catch (e) { return true; }
        };
        var isMoney = /^-?\s*₹\s*-?[\d,]+(\.\d+)?$/;
        var isNumberish = /^-?[\d,]+(\.\d+)?$/;
        var excelBody = function (data, row, col, node) {
            var t = cellText(data, node);
            if (isMoney.test(t)) return t.replace(/[₹,\s]/g, '');
            if (isNumberish.test(t) && t.indexOf(',') !== -1) return t.replace(/,/g, '');
            return t;
        };
        var stripIndic = function (t) {
            return String(t)
                .replace(/[઀-૿ऀ-ॿ]+/g, '')
                .replace(/₹\s*/g, 'Rs. ')
                .replace(/\(\s*\)/g, '')
                .replace(/\s+/g, ' ')
                .replace(/(\s*[\/|·›-]\s*)+$/g, '')
                .replace(/^(\s*[\/|·›-]\s*)+/g, '')
                .replace(/\s([,.)])/g, '$1')
                .trim();
        };
        var pdfBody = function (data, row, col, node) { return stripIndic(cellText(data, node)); };
        var pdfHeader = function (data, col, node) { return stripIndic(headerText(data, col, node)); };

        var setNames = function (dt, node, conf) {
            var title = tableTitle(dt);
            conf.title = title;
            conf.filename = slug(cfg.storeName) + '_' + slug(title) + '_' + today();
        };

        var pdfCustomize = function (doc, config, dt) {
            var headBg = cssVar('--btn-primary-bg', '#0f172a');
            var headFg = cssVar('--btn-primary-text', '#ffffff');
            var tIdx = -1;
            doc.content.forEach(function (c, i) { if (c && c.table) tIdx = i; });
            if (tIdx === -1) return;
            var tbl = doc.content[tIdx];
            var cols = tbl.table.body[0].length;
            var rows = tbl.table.body.length - 1;
            tbl.table.widths = new Array(cols).fill('*');
            tbl.table.headerRows = 1;
            tbl.layout = {
                hLineWidth: function (i, n) { return (i === 0 || i === n.table.body.length) ? 0 : 0.5; },
                vLineWidth: function () { return 0; },
                hLineColor: function () { return '#e2e8f0'; },
                paddingLeft: function () { return 6; }, paddingRight: function () { return 6; },
                paddingTop: function () { return 5; }, paddingBottom: function () { return 5; },
                fillColor: function (r) { return r === 0 ? headBg : (r % 2 === 0 ? '#f8fafc' : null); }
            };
            doc.styles.tableHeader = { bold: true, fontSize: cols > 8 ? 7.5 : 8.5, color: headFg, alignment: 'left' };
            doc.styles.tableBodyEven = {}; doc.styles.tableBodyOdd = {};
            doc.defaultStyle.fontSize = cols > 9 ? 7 : (cols > 6 ? 8 : 9);
            doc.defaultStyle.color = '#1e293b';
            doc.pageMargins = [28, 30, 28, 36];
            doc.content.splice(0, tIdx, {
                columns: [
                    { stack: [
                        { text: stripIndic(cfg.storeName || 'Admin').toUpperCase(), fontSize: 8, bold: true, color: '#64748b', characterSpacing: 1 },
                        { text: stripIndic(config.title || 'Report'), fontSize: 16, bold: true, color: '#0f172a', margin: [0, 3, 0, 0] }
                    ] },
                    { width: 'auto', alignment: 'right', fontSize: 8, color: '#64748b', stack: [
                        { text: 'Generated: ' + nowLabel() },
                        { text: 'Records: ' + rows, margin: [0, 2, 0, 0] },
                        { text: 'By: ' + stripIndic(cfg.userName || ''), margin: [0, 2, 0, 0] }
                    ] }
                ],
                margin: [0, 0, 0, 10]
            }, { canvas: [{ type: 'line', x1: 0, y1: 0, x2: (config.orientation === 'landscape' ? 786 : 539), y2: 0, lineWidth: 1, lineColor: '#e2e8f0' }], margin: [0, 0, 0, 10] });
            doc.footer = function (page, pages) {
                return { columns: [
                    { text: stripIndic(cfg.storeName || '') + ' · ' + stripIndic(config.title || ''), fontSize: 7.5, color: '#94a3b8', margin: [28, 8, 0, 0] },
                    { text: 'Page ' + page + ' of ' + pages, alignment: 'right', fontSize: 7.5, color: '#94a3b8', margin: [0, 8, 28, 0] }
                ] };
            };
        };

        var pdfAction = function (e, dt, button, config) {
            var self = this;
            var colCount = dt.columns(exportColumns).indexes().length;
            var saved = store.get('admin_pdf_orientation', '');
            var orient = saved || (colCount > 6 ? 'landscape' : 'portrait');
            var size = store.get('admin_pdf_pagesize', 'A4');
            var rowCount = dt.rows({ search: 'applied' }).count();
            var html =
                '<div class="pdf-dialog">' +
                '  <div><div class="pd-label">Orientation</div><div class="pd-orient">' +
                '    <label class="pd-opt"><input type="radio" name="pd-orient" value="portrait"' + (orient === 'portrait' ? ' checked' : '') + '><span class="pd-card"><span class="pd-sheet portrait"></span><span class="pd-name">Portrait</span><span class="pd-desc">Best for few columns</span></span></label>' +
                '    <label class="pd-opt"><input type="radio" name="pd-orient" value="landscape"' + (orient === 'landscape' ? ' checked' : '') + '><span class="pd-card"><span class="pd-sheet landscape"></span><span class="pd-name">Landscape</span><span class="pd-desc">Best for wide tables</span></span></label>' +
                '  </div></div>' +
                '  <div class="pd-row">' +
                '    <div><div class="pd-label">Paper size</div><select id="pdPage">' +
                ['A4', 'A3', 'LETTER', 'LEGAL'].map(function (s) { return '<option value="' + s + '"' + (s === size ? ' selected' : '') + '>' + (s === 'LETTER' ? 'Letter' : s === 'LEGAL' ? 'Legal' : s) + '</option>'; }).join('') +
                '    </select></div>' +
                '    <div><div class="pd-label">Rows</div><div class="pd-check" style="height:40px"><i class="ph ph-rows"></i> ' + rowCount + ' (current filter)</div></div>' +
                '  </div>' +
                '  <div><div class="pd-label">Document title</div><input type="text" id="pdTitle" value="' + escapeHtml(config.title || '') + '" maxlength="80"></div>' +
                '  <p style="font-size:11.5px;color:var(--muted);margin:0"><i class="ph ph-info"></i> ' + colCount + ' columns will be exported. Use <b>Columns</b> to hide any you do not need. Gujarati text is best exported with <b>Excel</b> or <b>Print</b>.</p>' +
                '</div>';
            Swal.fire({
                title: t('Export to PDF'),
                html: html,
                width: 480,
                showCancelButton: true,
                confirmButtonText: t('Download PDF'),
                cancelButtonText: t('Cancel'),
                focusConfirm: false,
                preConfirm: function () {
                    var p = Swal.getPopup();
                    return {
                        orientation: p.querySelector('input[name="pd-orient"]:checked').value,
                        pageSize: p.querySelector('#pdPage').value,
                        title: (p.querySelector('#pdTitle').value || '').trim()
                    };
                }
            }).then(function (r) {
                if (!r.isConfirmed) return;
                store.set('admin_pdf_orientation', r.value.orientation);
                store.set('admin_pdf_pagesize', r.value.pageSize);
                var conf = $.extend({}, config, {
                    orientation: r.value.orientation,
                    pageSize: r.value.pageSize,
                    title: r.value.title || config.title,
                    filename: r.value.title ? slug(cfg.storeName) + '_' + slug(r.value.title) + '_' + today() : config.filename
                });
                try {
                    DT.ext.buttons.pdfHtml5.action.call(self, e, dt, button, conf);
                    Toast.show('success', 'PDF (' + conf.orientation + ', ' + conf.pageSize + ') is downloading.');
                } catch (err) {
                    console.error(err);
                    Toast.show('error', 'Could not generate the PDF. Please try Excel or Print instead.');
                }
            });
        };

        var printCustomize = function (win) {
            var d = win.document;
            var style = d.createElement('style');
            style.textContent =
                '@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Hind+Vadodara:wght@400;600&display=swap");' +
                'body{font-family:"Plus Jakarta Sans","Hind Vadodara",sans-serif;color:#0f172a;padding:24px;margin:0}' +
                'h1{font-size:20px;font-weight:800;margin:0 0 4px}' +
                '.print-meta{font-size:11px;color:#64748b;margin-bottom:16px}' +
                'table{width:100%;border-collapse:collapse;font-size:11px}' +
                'th{background:#0f172a;color:#fff;text-align:left;padding:7px 8px;font-size:10px;text-transform:uppercase;letter-spacing:.05em}' +
                'td{padding:7px 8px;border-bottom:1px solid #e2e8f0;vertical-align:top}' +
                'tr:nth-child(even) td{background:#f8fafc}' +
                '@page{margin:12mm}';
            d.head.appendChild(style);
            var h1 = d.querySelector('h1');
            var meta = d.createElement('div');
            meta.className = 'print-meta';
            meta.textContent = (cfg.storeName || '') + ' · Generated ' + nowLabel() + ' by ' + (cfg.userName || '');
            if (h1) h1.parentNode.insertBefore(meta, h1.nextSibling);
        };

        var exportOpts = function (body, header) { return { columns: exportColumns, format: { body: body, header: header || headerText } }; };
        var plainBody = function (data, row, col, node) { return cellText(data, node); };

        DT.AdminButtons = function () {
            return [
                { extend: 'excelHtml5', className: 'btn-excel', text: '<i class="ph ph-microsoft-excel-logo"></i><span class="btn-text">' + t('Excel') + '</span>', titleAttr: 'Download as Excel (.xlsx)', init: setNames, autoFilter: true, sheetName: 'Export', exportOptions: exportOpts(excelBody) },
                { extend: 'pdfHtml5', className: 'btn-pdf', text: '<i class="ph ph-file-pdf"></i><span class="btn-text">' + t('PDF') + '</span>', titleAttr: 'Download as PDF (choose portrait / landscape)', init: setNames, action: pdfAction, customize: pdfCustomize, download: 'download', exportOptions: exportOpts(pdfBody, pdfHeader) },
                { extend: 'print', className: 'btn-print', text: '<i class="ph ph-printer"></i><span class="btn-text">' + t('Print') + '</span>', titleAttr: 'Print table', init: setNames, customize: printCustomize, exportOptions: exportOpts(plainBody) },
                {
                    extend: 'collection', className: 'btn-more', text: '<i class="ph ph-dots-three-circle"></i><span class="btn-text">' + t('More') + '</span>', titleAttr: 'More export options', autoClose: true,
                    buttons: [
                        { extend: 'csvHtml5', text: '<i class="ph ph-file-csv"></i> ' + t('Download CSV'), init: setNames, bom: true, exportOptions: exportOpts(plainBody) },
                        { extend: 'copyHtml5', text: '<i class="ph ph-copy"></i> ' + t('Copy to clipboard'), init: setNames, exportOptions: exportOpts(plainBody) }
                    ]
                },
                { extend: 'colvis', className: 'btn-colvis', text: '<i class="ph ph-columns"></i><span class="btn-text">' + t('Columns') + '</span>', titleAttr: 'Show / hide columns', columns: ':not(.no-export)', collectionTitle: 'Visible columns' },
                { text: '<i class="ph ph-corners-out"></i>', className: 'btn-icon-only btn-fs', titleAttr: 'Expand table', action: function (e, dt) { toggleCardFullscreen(dt.table().container()); } }
            ];
        };

        $.extend(true, DT.defaults, {
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            autoWidth: false,
            language: {
                search: '',
                searchPlaceholder: t('Search records…'),
                lengthMenu: '_MENU_ <span class="hidden sm:inline">' + t('per page') + '</span>',
                info: t('Showing') + ' <b>_START_–_END_</b> ' + t('of') + ' <b>_TOTAL_</b>',
                infoEmpty: t('No records'),
                infoFiltered: t('(filtered from _MAX_)'),
                aria: { sortAscending: t(': activate to sort column ascending'), sortDescending: t(': activate to sort column descending') },
                emptyTable: '<div class="empty-state"><i class="ph-duotone ph-tray"></i><h4>' + t('Nothing here yet') + '</h4><p>' + t('Records you add will appear in this table.') + '</p></div>',
                zeroRecords: '<div class="empty-state"><i class="ph-duotone ph-magnifying-glass"></i><h4>' + t('No matching records') + '</h4><p>' + t('Try a different search or clear the filters.') + '</p></div>',
                paginate: { first: '<i class="ph ph-caret-double-left"></i>', previous: '<i class="ph ph-caret-left"></i>', next: '<i class="ph ph-caret-right"></i>', last: '<i class="ph ph-caret-double-right"></i>' },
                buttons: { copyTitle: 'Copied to clipboard', copySuccess: { _: '%d rows copied', 1: '1 row copied' } }
            },
            dom: "<'dt-top'<'dt-top-left'lB><'dt-top-right'f>><'dt-table-wrap'tr><'dt-bottom'ip>",
            buttons: DT.AdminButtons()
        });

        // Prepare tables before any page script initialises them: mark action / image columns
        $(function () {
            // Uses HTML5 data-* attributes so page-level columnDefs never clash with these defaults
            $('table').each(function () {
                var $ths = $(this).find('thead tr:last th');
                $ths.each(function () {
                    var $th = $(this);
                    var t = $th.text().replace(/\s+/g, ' ').trim().toLowerCase();
                    if (t === 'actions' || t === 'action' || t === '' || $th.find('input[type=checkbox]').length) {
                        $th.addClass('no-export no-sort');
                    }
                    if ($th.hasClass('no-sort') && $th.attr('data-orderable') === undefined) $th.attr('data-orderable', 'false');
                });
                if ($ths.length && $ths.first().attr('data-priority') === undefined) $ths.first().attr('data-priority', 1);
                if ($ths.length > 1 && $ths.last().attr('data-priority') === undefined) $ths.last().attr('data-priority', 2);
            });
        });

        // Wrap legacy padded cards so the toolbar sits flush; focus search on '/'
        $(document).on('init.dt', function (e, settings) {
            var api = new DT.Api(settings);
            var $wrap = $(api.table().container());
            var $card = $wrap.closest('.card, [class*="rounded-3xl"], [class*="rounded-2xl"]');
            if ($card.length) {
                $card.addClass('table-card');
                if (!$card.hasClass('card') && parseInt($card.css('padding-left'), 10) > 0) $card.addClass('dt-legacy-card');
            }
            $wrap.find('.dataTables_filter input').attr({ 'aria-label': 'Search table', autocomplete: 'off' });
        });

        // Keep responsive layouts correct after the sidebar animates / theme changes
        document.addEventListener('admin:theme', function () { setTimeout(Sidebar.adjustTables, 50); });
    }

    /* ------------------------------------------------------------------ Chart.js theming */
    if (window.Chart) {
        var applyChartTheme = function () {
            var dark = html.classList.contains('dark');
            Chart.defaults.font.family = '"Plus Jakarta Sans", "Hind Vadodara", sans-serif';
            Chart.defaults.font.size = 11;
            Chart.defaults.color = dark ? '#94a3b8' : '#64748b';
            Chart.defaults.borderColor = dark ? 'rgba(148,163,184,.12)' : 'rgba(15,23,42,.06)';
            Chart.defaults.plugins.tooltip.backgroundColor = dark ? '#1e293b' : '#0f172a';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 10;
            Chart.defaults.plugins.tooltip.titleFont = { weight: '700' };
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.boxWidth = 8;
        };
        applyChartTheme();
        document.addEventListener('admin:theme', function () {
            applyChartTheme();
            Object.values(Chart.instances || {}).forEach(function (c) {
                if (c.options.scales) Object.values(c.options.scales).forEach(function (s) {
                    if (s.grid) s.grid.color = Chart.defaults.borderColor;
                    if (s.ticks) s.ticks.color = Chart.defaults.color;
                });
                if (c.options.plugins && c.options.plugins.legend && c.options.plugins.legend.labels) c.options.plugins.legend.labels.color = Chart.defaults.color;
                c.update('none');
            });
        });
    }

    /* ------------------------------------------------------------------ Utilities (global) */
    window.copyToClipboard = function (text, msg) {
        var done = function () { Toast.show('success', msg || 'Copied to clipboard'); };
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done);
        else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) { /* noop */ } t.remove(); }
    };

    // Drag & drop uploader (used by product / slider / category forms)
    window.initDragAndDropUploader = function (dropAreaId, inputId, previewId) {
        var dropArea = document.getElementById(dropAreaId);
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);
        if (!dropArea || !input) return;
        ['dragenter', 'dragover'].forEach(function (ev) { dropArea.addEventListener(ev, function (e) { e.preventDefault(); e.stopPropagation(); dropArea.classList.add('dragover'); }, false); });
        ['dragleave', 'drop'].forEach(function (ev) { dropArea.addEventListener(ev, function (e) { e.preventDefault(); e.stopPropagation(); dropArea.classList.remove('dragover'); }, false); });
        dropArea.addEventListener('drop', function (e) {
            var files = e.dataTransfer.files;
            if (files.length) {
                try { input.files = files; } catch (err) { /* older browsers */ }
                showPreview(files);
            }
        });
        input.addEventListener('change', function () { if (input.files.length) showPreview(input.files); });
        function showPreview(files) {
            if (!preview) return;
            preview.innerHTML = '';
            Array.prototype.forEach.call(files, function (file) {
                if (!file.type || file.type.indexOf('image/') !== 0) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    var box = document.createElement('div');
                    box.className = 'relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm';
                    box.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover" alt="">' +
                        '<div class="absolute inset-x-0 bottom-0 bg-black/55 text-white text-[10px] font-semibold px-1.5 py-1 truncate">' + escapeHtml(file.name) + '</div>';
                    preview.appendChild(box);
                };
                reader.readAsDataURL(file);
            });
        }
    };
})(window.jQuery, window, document);
