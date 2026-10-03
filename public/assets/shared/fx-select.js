/* ==========================================================================
   FxSelect – themed dropdown that progressively enhances every native <select>
   (admin panel + storefront). The native <select> stays in the DOM, so forms,
   validation, inline onchange="", jQuery .val() and change handlers keep working.

   Opt out per element: <select data-native> (or class "fx-native").
   Public API: FxSelect.enhance(root), FxSelect.refresh(selectEl), FxSelect.destroy(selectEl)
   ========================================================================== */
(function (window, document) {
    'use strict';
    if (window.FxSelect) return;

    var SEARCH_THRESHOLD = 8;
    var CHECK = '<svg viewBox="0 0 256 256" aria-hidden="true"><path fill="currentColor" d="M232.49 80.49l-128 128a12 12 0 0 1-17 0l-56-56a12 12 0 1 1 17-17L96 183 215.51 63.51a12 12 0 0 1 17 17Z"/></svg>';
    var CARET = '<svg viewBox="0 0 256 256" aria-hidden="true"><path fill="currentColor" d="M213.66 101.66l-80 80a8 8 0 0 1-11.32 0l-80-80a8 8 0 0 1 11.32-11.32L128 164.69l74.34-74.35a8 8 0 0 1 11.32 11.32Z"/></svg>';
    var SEARCH = '<svg viewBox="0 0 256 256" aria-hidden="true"><path fill="currentColor" d="M229.66 218.34l-50.07-50.06a88.11 88.11 0 1 0-11.31 11.31l50.06 50.07a8 8 0 0 0 11.32-11.32ZM40 112a72 72 0 1 1 72 72 72.08 72.08 0 0 1-72-72Z"/></svg>';
    var GU = { 'Search…': 'શોધો…', 'No matches': 'કોઈ પરિણામ નથી' };
    function tr(str) {
        if (window.__ && window.__ !== tr) { var v = window.__(str); if (v && v !== str) return v; }
        return (document.documentElement.lang || '').indexOf('gu') === 0 && GU[str] ? GU[str] : str;
    }
    var uid = 0;
    var instances = new WeakMap();
    var openInst = null;

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function skip(sel) {
        return sel.multiple || sel.size > 1 || sel.hasAttribute('data-native') || sel.classList.contains('fx-native') ||
            sel.closest('[data-native-selects]') || sel.closest('.dt-button-collection');
    }

    function Inst(sel) {
        this.sel = sel;
        this.id = 'fxs' + (++uid);
        this.active = -1;
        this.query = '';
        this.build();
    }

    Inst.prototype.build = function () {
        var sel = this.sel, self = this;
        var btn = document.createElement('button');
        btn.type = 'button';
        // Inherit the select's own classes so width / size utilities keep applying
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-controls', this.id);
        if (sel.id) {
            btn.id = sel.id + '__fx';
            var lbl = document.querySelector('label[for="' + sel.id + '"]');
            if (lbl) lbl.setAttribute('for', btn.id);
        }
        if (sel.getAttribute('aria-label')) btn.setAttribute('aria-label', sel.getAttribute('aria-label'));
        btn.innerHTML = '<span class="fx-sel-label"></span><span class="fx-sel-caret">' + CARET + '</span>';
        if (sel.getAttribute('style')) btn.setAttribute('style', sel.getAttribute('style'));
        sel.classList.add('fx-sel-native');
        sel.setAttribute('tabindex', '-1');
        sel.setAttribute('aria-hidden', 'true');
        sel.parentNode.insertBefore(btn, sel.nextSibling);
        this.btn = btn;

        btn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); self.isOpen() ? self.close() : self.open(); });
        btn.addEventListener('keydown', function (e) { self.onTriggerKey(e); });
        sel.addEventListener('change', function () { self.render(); });
        sel.addEventListener('invalid', function () { btn.classList.add('is-invalid'); });

        this.mo = new MutationObserver(function () { self.render(); if (self.isOpen()) self.renderList(); });
        this.mo.observe(sel, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected', 'class', 'label', 'style', 'hidden'] });
        this.render();
    };

    Inst.prototype.options = function () {
        var out = [];
        Array.prototype.forEach.call(this.sel.children, function (node) {
            if (node.tagName === 'OPTGROUP') {
                out.push({ group: node.label });
                Array.prototype.forEach.call(node.children, function (o) { out.push({ opt: o, grouped: true, disabled: o.disabled || node.disabled }); });
            } else if (node.tagName === 'OPTION') {
                out.push({ opt: node, disabled: node.disabled });
            }
        });
        return out;
    };

    Inst.prototype.render = function () {
        var sel = this.sel, o = sel.options[sel.selectedIndex];
        var text = o ? (o.getAttribute('data-label') || o.text) : '';
        var isPlaceholder = !o || o.value === '';
        var lab = this.btn.querySelector('.fx-sel-label');
        lab.textContent = text || ' ';
        lab.classList.toggle('is-placeholder', isPlaceholder);
        this.btn.disabled = sel.disabled;
        this.btn.classList.toggle('is-disabled', sel.disabled);
        if (sel.checkValidity && sel.checkValidity()) this.btn.classList.remove('is-invalid');
        // keep classes in sync if the page toggles them on the select (minus our own)
        var cls = (sel.getAttribute('class') || '').replace(/\bfx-sel-native\b/g, '').trim();
        var styled = /(^|\s)(form-select|form-control|fx-input|border|p[xy]?-|h-)/.test(cls);
        var want = cls + ' fx-sel' + (styled ? '' : ' fx-sel-default') + (this.isOpen() ? ' is-open' : '') + (sel.disabled ? ' is-disabled' : '');
        if (this._lastCls !== cls) { this.btn.className = want; this._lastCls = cls; }
        var st = sel.getAttribute('style') || '';
        if (this._lastStyle !== st) { this.btn.setAttribute('style', st); this._lastStyle = st; }
        this.btn.hidden = sel.hidden;
    };

    Inst.prototype.isOpen = function () { return !!this.panel; };

    Inst.prototype.open = function () {
        if (this.sel.disabled) return;
        if (openInst && openInst !== this) openInst.close();
        openInst = this;
        var self = this, many = this.sel.options.length > SEARCH_THRESHOLD || this.sel.hasAttribute('data-search');
        var p = document.createElement('div');
        p.className = 'fx-sel-panel';
        if (document.documentElement.classList.contains('dark')) p.classList.add('is-dark');
        p.innerHTML = (many ? '<div class="fx-sel-search">' + SEARCH + '<input type="text" placeholder="' + esc(this.sel.getAttribute('data-search-placeholder') || tr('Search…')) + '" autocomplete="off" spellcheck="false"></div>' : '') +
            '<ul class="fx-sel-list" role="listbox" id="' + this.id + '"></ul>';
        document.body.appendChild(p);
        this.panel = p;
        this.list = p.querySelector('.fx-sel-list');
        this.query = '';
        this.btn.classList.add('is-open');
        this.btn.setAttribute('aria-expanded', 'true');
        this.renderList();
        this.position();
        requestAnimationFrame(function () { p.classList.add('is-visible'); });

        var input = p.querySelector('.fx-sel-search input');
        if (input) {
            input.addEventListener('input', function () { self.query = input.value.trim().toLowerCase(); self.renderList(); });
            input.addEventListener('keydown', function (e) { self.onListKey(e); });
            setTimeout(function () { input.focus(); }, 10);
        } else {
            this.list.setAttribute('tabindex', '-1');
            this.list.addEventListener('keydown', function (e) { self.onListKey(e); });
            setTimeout(function () { self.list.focus({ preventScroll: true }); }, 10);
        }
        this.list.addEventListener('mousedown', function (e) { e.preventDefault(); });
        this.list.addEventListener('click', function (e) {
            var li = e.target.closest('[data-i]');
            if (li && !li.classList.contains('is-disabled')) self.pick(+li.getAttribute('data-i'));
        });
        this.list.addEventListener('mousemove', function (e) {
            var li = e.target.closest('[data-i]');
            if (li && !li.classList.contains('is-disabled')) self.setActive(+li.getAttribute('data-pos'), false);
        });
        this._reposition = function () { self.position(); };
        window.addEventListener('resize', this._reposition);
        window.addEventListener('scroll', this._reposition, true);
    };

    Inst.prototype.close = function (focusBtn) {
        if (!this.panel) return;
        var p = this.panel;
        this.panel = null;
        p.classList.remove('is-visible');
        setTimeout(function () { p.remove(); }, 120);
        this.btn.classList.remove('is-open');
        this.btn.setAttribute('aria-expanded', 'false');
        window.removeEventListener('resize', this._reposition);
        window.removeEventListener('scroll', this._reposition, true);
        if (openInst === this) openInst = null;
        if (focusBtn) this.btn.focus();
    };

    Inst.prototype.renderList = function () {
        if (!this.list) return;
        var q = this.query, html = '', pos = 0, sel = this.sel, self = this;
        this.visible = [];
        var pendingGroup = null;
        this.options().forEach(function (item) {
            if (item.group !== undefined) { pendingGroup = item.group; return; }
            var o = item.opt, text = o.getAttribute('data-label') || o.text;
            if (q && text.toLowerCase().indexOf(q) === -1) return;
            if (pendingGroup !== null) { html += '<li class="fx-sel-group" role="presentation">' + esc(pendingGroup) + '</li>'; pendingGroup = null; }
            var selected = o.index === sel.selectedIndex;
            var label = esc(text);
            if (q) {
                var i = text.toLowerCase().indexOf(q);
                label = esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length));
            }
            var hint = o.getAttribute('data-hint');
            html += '<li role="option" data-i="' + o.index + '" data-pos="' + pos + '" class="fx-sel-opt' + (selected ? ' is-selected' : '') + (item.disabled ? ' is-disabled' : '') + (item.grouped ? ' is-grouped' : '') + (o.value === '' ? ' is-placeholder' : '') + '" aria-selected="' + selected + '"' + (item.disabled ? ' aria-disabled="true"' : '') + '>' +
                '<span class="fx-sel-text">' + (label || '&nbsp;') + (hint ? '<small>' + esc(hint) + '</small>' : '') + '</span><span class="fx-sel-check">' + CHECK + '</span></li>';
            self.visible.push(o.index);
            pos++;
        });
        if (!pos) html = '<li class="fx-sel-empty">' + esc(tr('No matches')) + '</li>';
        this.list.innerHTML = html;
        var cur = this.visible.indexOf(sel.selectedIndex);
        this.setActive(cur === -1 ? 0 : cur, true);
    };

    Inst.prototype.setActive = function (pos, scroll) {
        if (!this.list) return;
        this.active = pos;
        var items = this.list.querySelectorAll('.fx-sel-opt');
        items.forEach(function (li, i) { li.classList.toggle('is-active', i === pos); });
        if (scroll && items[pos]) items[pos].scrollIntoView({ block: 'nearest' });
    };

    Inst.prototype.move = function (d) {
        var items = this.list.querySelectorAll('.fx-sel-opt'), n = items.length;
        if (!n) return;
        var p = this.active;
        for (var k = 0; k < n; k++) {
            p = (p + d + n) % n;
            if (!items[p].classList.contains('is-disabled')) break;
        }
        this.setActive(p, true);
    };

    Inst.prototype.pick = function (index) {
        var sel = this.sel, changed = sel.selectedIndex !== index;
        sel.selectedIndex = index;
        this.close(true);
        this.render();
        if (changed) {
            sel.dispatchEvent(new Event('input', { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    Inst.prototype.onTriggerKey = function (e) {
        var k = e.key;
        if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'Enter' || k === ' ' || (k === 'F4')) { e.preventDefault(); this.open(); return; }
        // type-ahead without opening
        if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            var ch = k.toLowerCase(), sel = this.sel, start = sel.selectedIndex;
            for (var i = 1; i <= sel.options.length; i++) {
                var o = sel.options[(start + i) % sel.options.length];
                if (!o.disabled && o.text.trim().toLowerCase().indexOf(ch) === 0) { this.pick(o.index); this.btn.focus(); break; }
            }
        }
    };

    Inst.prototype.onListKey = function (e) {
        var k = e.key;
        if (k === 'ArrowDown') { e.preventDefault(); this.move(1); }
        else if (k === 'ArrowUp') { e.preventDefault(); this.move(-1); }
        else if (k === 'Home') { e.preventDefault(); this.setActive(0, true); }
        else if (k === 'End') { e.preventDefault(); this.setActive(this.visible.length - 1, true); }
        else if (k === 'Enter') { e.preventDefault(); var i = this.visible[this.active]; if (i !== undefined) this.pick(i); }
        else if (k === 'Escape') { e.preventDefault(); e.stopPropagation(); this.close(true); }
        else if (k === 'Tab') { this.close(); }
        else if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && e.target.tagName !== 'INPUT') {
            // type-ahead inside the open list
            var self = this;
            clearTimeout(this._taTimer);
            this._ta = (this._ta || '') + k.toLowerCase();
            this._taTimer = setTimeout(function () { self._ta = ''; }, 700);
            var items = this.list.querySelectorAll('.fx-sel-opt');
            for (var i = 0; i < items.length; i++) {
                if (!items[i].classList.contains('is-disabled') && items[i].textContent.trim().toLowerCase().indexOf(this._ta) === 0) { this.setActive(i, true); break; }
            }
        }
    };

    Inst.prototype.position = function () {
        if (!this.panel) return;
        var r = this.btn.getBoundingClientRect(), p = this.panel, vh = window.innerHeight, vw = window.innerWidth;
        var width = Math.max(r.width, 180);
        p.style.minWidth = width + 'px';
        p.style.maxWidth = Math.max(width, Math.min(380, vw - 16)) + 'px';
        var left = Math.min(r.left, vw - p.offsetWidth - 8);
        p.style.left = Math.max(8, left) + 'px';
        var below = vh - r.bottom, ph = Math.min(p.scrollHeight, 340);
        if (below < ph + 12 && r.top > below) {
            p.style.top = ''; p.style.bottom = (vh - r.top + 6) + 'px'; p.classList.add('is-above');
        } else {
            p.style.bottom = ''; p.style.top = (r.bottom + 6) + 'px'; p.classList.remove('is-above');
        }
        // close if the trigger scrolled out of view
        if (r.bottom < 0 || r.top > vh) this.close();
    };

    Inst.prototype.destroy = function () {
        this.close();
        this.mo.disconnect();
        this.btn.remove();
        this.sel.classList.remove('fx-sel-native');
        this.sel.removeAttribute('tabindex');
        this.sel.removeAttribute('aria-hidden');
        instances.delete(this.sel);
    };

    // ---- Programmatic value changes (jQuery .val(), el.value = …) refresh the trigger
    ['value', 'selectedIndex'].forEach(function (prop) {
        var d = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, prop);
        if (!d || !d.set) return;
        Object.defineProperty(HTMLSelectElement.prototype, prop, {
            configurable: true, enumerable: d.enumerable,
            get: d.get,
            set: function (v) { d.set.call(this, v); var inst = instances.get(this); if (inst) inst.render(); }
        });
    });

    function enhance(root) {
        root = root || document;
        var list = root.tagName === 'SELECT' ? [root] : root.querySelectorAll('select');
        Array.prototype.forEach.call(list, function (sel) {
            if (instances.has(sel) || skip(sel) || !sel.parentNode) return;
            instances.set(sel, new Inst(sel));
        });
    }

    document.addEventListener('mousedown', function (e) {
        if (openInst && !e.target.closest('.fx-sel-panel') && e.target !== openInst.btn && !openInst.btn.contains(e.target)) openInst.close();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && openInst) openInst.close(true); }, true);
    // Forms reset → refresh labels
    document.addEventListener('reset', function (e) {
        setTimeout(function () { e.target.querySelectorAll && e.target.querySelectorAll('select').forEach(function (s) { var i = instances.get(s); if (i) i.render(); }); }, 0);
    });

    function boot() {
        enhance(document);
        new MutationObserver(function (muts) {
            muts.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeType !== 1 || n.classList.contains('fx-sel-panel') || n.classList.contains('fx-sel')) return;
                    if (n.tagName === 'SELECT' || n.querySelector('select')) enhance(n);
                });
                m.removedNodes.forEach(function (n) {
                    if (n.nodeType !== 1) return;
                    var sels = n.tagName === 'SELECT' ? [n] : (n.querySelectorAll ? n.querySelectorAll('select') : []);
                    Array.prototype.forEach.call(sels, function (s) { var i = instances.get(s); if (i && !document.contains(s)) { i.close(); i.mo.disconnect(); instances.delete(s); } });
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();

    window.FxSelect = {
        enhance: enhance,
        refresh: function (sel) { var i = instances.get(sel); if (i) i.render(); },
        destroy: function (sel) { var i = instances.get(sel); if (i) i.destroy(); }
    };
})(window, document);
