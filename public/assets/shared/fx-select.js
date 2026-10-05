/* ==========================================================================
   FxSelect – Beautiful, searchable, accessible themed select dropdowns
   Used across Admin Console & Storefront.
   Matches Talmud Testing System style (Image 4):
   - Clearable trigger option
   - Embedded search bar with icon and instant filtering
   - Bilingual / Gujarati + English search support
   - Accent checkmark on active / selected item
   - Smooth floating popover with collision detection & dark mode support
   - Seamless compatibility with native form submits & jQuery .on('change')
   ========================================================================== */
(function (window, document) {
    'use strict';

    if (window.FxSelect) return;

    var ICONS = {
        check: '<svg viewBox="0 0 256 256" class="fx-icon" aria-hidden="true"><path fill="currentColor" d="M229.66 77.66l-128 128a8 8 0 0 1-11.32 0l-56-56a8 8 0 0 1 11.32-11.32L96 188.69 218.34 66.34a8 8 0 0 1 11.32 11.32Z"/></svg>',
        caret: '<svg viewBox="0 0 256 256" class="fx-icon" aria-hidden="true"><path fill="currentColor" d="M213.66 101.66l-80 80a8 8 0 0 1-11.32 0l-80-80a8 8 0 0 1 11.32-11.32L128 164.69l74.34-74.35a8 8 0 0 1 11.32 11.32Z"/></svg>',
        search: '<svg viewBox="0 0 256 256" class="fx-icon" aria-hidden="true"><path fill="currentColor" d="M229.66 218.34l-50.07-50.06a88.11 88.11 0 1 0-11.31 11.31l50.06 50.07a8 8 0 0 0 11.32-11.32ZM40 112a72 72 0 1 1 72 72 72.08 72.08 0 0 1-72-72Z"/></svg>',
        clear: '<svg viewBox="0 0 256 256" class="fx-icon" aria-hidden="true"><path fill="currentColor" d="M205.66 194.34a8 8 0 0 1-11.32 11.32L128 139.31l-66.34 66.35a8 8 0 0 1-11.32-11.32L116.69 128 50.34 61.66a8 8 0 0 1 11.32-11.32L128 116.69l66.34-66.35a8 8 0 0 1 11.32 11.32L139.31 128Z"/></svg>',
        empty: '<svg viewBox="0 0 256 256" class="fx-icon" aria-hidden="true"><path fill="currentColor" d="M128 24a104 104 0 1 0 104 104A104.11 104.11 0 0 0 128 24Zm0 192a88 88 0 1 1 88-88 88.1 88.1 0 0 1-88 88Zm-8-128a8 8 0 0 1 16 0v48a8 8 0 0 1-16 0Zm8 80a12 12 0 1 1 12-12 12 12 0 0 1-12 12Z"/></svg>'
    };

    var TRANSLATIONS = {
        gu: {
            'Search…': 'શોધો…',
            'Search options…': 'વિકલ્પો શોધો…',
            'No matches found': 'કોઈ પરિણામ મળ્યું નથી',
            'Clear selection': 'પસંદગી સાફ કરો',
            'Clear': 'સાફ કરો'
        },
        en: {
            'Search…': 'Search…',
            'Search options…': 'Search options…',
            'No matches found': 'No matches found',
            'Clear selection': 'Clear selection',
            'Clear': 'Clear'
        }
    };

    function t(str) {
        var locale = (document.documentElement.lang || 'en').toLowerCase().indexOf('gu') === 0 ? 'gu' : 'en';
        if (window.__ && typeof window.__ === 'function') {
            var val = window.__(str);
            if (val && val !== str) return val;
        }
        return TRANSLATIONS[locale] && TRANSLATIONS[locale][str] ? TRANSLATIONS[locale][str] : str;
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/[&<>"']/g, function (char) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
        });
    }

    var uid = 0;
    var instances = new WeakMap();
    var activeInstance = null;

    function shouldSkip(select) {
        if (!select || select.multiple || select.size > 1) return true;
        if (select.hasAttribute('data-native') || select.classList.contains('fx-native')) return true;
        if (select.closest('[data-native-selects]') || select.closest('.dt-button-collection')) return true;
        // Skip hidden template selects if any
        if (select.closest('template') || select.closest('.hidden:not(.table-card)')) {
            // only skip if parent is template
            if (select.closest('template')) return true;
        }
        return false;
    }

    function SelectInstance(select) {
        this.select = select;
        this.id = 'fx-select-' + (++uid);
        this.activeIndex = -1;
        this.query = '';
        this.panel = null;
        this.visibleIndices = [];
        this.build();
    }

    SelectInstance.prototype.build = function () {
        var sel = this.select;
        var self = this;

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', this.id);
        if (sel.id) {
            trigger.id = sel.id + '__fx';
            var label = document.querySelector('label[for="' + sel.id + '"]');
            if (label) label.setAttribute('for', trigger.id);
        }
        if (sel.getAttribute('aria-label')) {
            trigger.setAttribute('aria-label', sel.getAttribute('aria-label'));
        }

        trigger.innerHTML = 
            '<span class="fx-sel-inner">' +
                '<span class="fx-sel-icon-slot"></span>' +
                '<span class="fx-sel-label"></span>' +
            '</span>' +
            '<span class="fx-sel-actions">' +
                '<span role="button" tabindex="-1" class="fx-sel-clear-trigger hidden" title="' + escapeHtml(t('Clear selection')) + '" aria-label="' + escapeHtml(t('Clear selection')) + '">' + ICONS.clear + '</span>' +
                '<span class="fx-sel-caret">' + ICONS.caret + '</span>' +
            '</span>';

        sel.classList.add('fx-sel-native');
        sel.setAttribute('tabindex', '-1');
        sel.setAttribute('aria-hidden', 'true');

        if (sel.parentNode) {
            sel.parentNode.insertBefore(trigger, sel.nextSibling);
        }
        this.trigger = trigger;

        // Clear trigger click
        var clearBtn = trigger.querySelector('.fx-sel-clear-trigger');
        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.clearValue();
            });
        }

        // Trigger open/close
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (self.isOpen()) {
                self.close();
            } else {
                self.open();
            }
        });

        trigger.addEventListener('keydown', function (e) {
            self.onTriggerKey(e);
        });

        sel.addEventListener('change', function () {
            self.syncFromNative();
        });

        sel.addEventListener('invalid', function () {
            trigger.classList.add('is-invalid');
        });

        // Observe native select mutations (options added/removed/disabled)
        this.observer = new MutationObserver(function () {
            self.syncFromNative();
            if (self.isOpen()) {
                self.renderList();
                self.position();
            }
        });
        this.observer.observe(sel, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['disabled', 'selected', 'class', 'style', 'hidden', 'title']
        });

        this.syncFromNative();
    };

    SelectInstance.prototype.getOptions = function () {
        var items = [];
        Array.prototype.forEach.call(this.select.children, function (node) {
            if (node.tagName === 'OPTGROUP') {
                items.push({ isGroup: true, label: node.label });
                Array.prototype.forEach.call(node.children, function (opt) {
                    items.push({ opt: opt, isGrouped: true, disabled: opt.disabled || node.disabled });
                });
            } else if (node.tagName === 'OPTION') {
                items.push({ opt: node, disabled: node.disabled });
            }
        });
        return items;
    };

    SelectInstance.prototype.syncFromNative = function () {
        var sel = this.select;
        var selectedOpt = sel.options[sel.selectedIndex];
        var text = selectedOpt ? (selectedOpt.getAttribute('data-label') || selectedOpt.text || '').trim() : '';
        var val = selectedOpt ? selectedOpt.value : '';
        var isPlaceholder = !selectedOpt || val === '';

        var labelEl = this.trigger.querySelector('.fx-sel-label');
        if (labelEl) {
            labelEl.textContent = text || '—';
            labelEl.classList.toggle('is-placeholder', isPlaceholder);
        }

        // Icon slot if option has data-icon
        var iconSlot = this.trigger.querySelector('.fx-sel-icon-slot');
        if (iconSlot) {
            var iconClass = selectedOpt ? (selectedOpt.getAttribute('data-icon') || '') : '';
            if (iconClass) {
                iconSlot.innerHTML = '<i class="' + escapeHtml(iconClass) + '"></i>';
                iconSlot.classList.remove('hidden');
            } else {
                iconSlot.innerHTML = '';
                iconSlot.classList.add('hidden');
            }
        }

        // Clear button display (only when a non-empty value is selected and select is clearable / not explicitly locked)
        var clearBtn = this.trigger.querySelector('.fx-sel-clear-trigger');
        if (clearBtn) {
            var canClear = !sel.disabled && !isPlaceholder && (sel.options.length > 0 && sel.options[0].value === '');
            clearBtn.classList.toggle('hidden', !canClear);
        }

        this.trigger.disabled = sel.disabled;
        this.trigger.classList.toggle('is-disabled', sel.disabled);

        if (sel.checkValidity && sel.checkValidity()) {
            this.trigger.classList.remove('is-invalid');
        }

        // Inherit classes and sizing
        var cls = (sel.getAttribute('class') || '').replace(/\bfx-sel-native\b/g, '').trim();
        var styled = /(^|\s)(form-select|form-control|fx-input|border|p[xy]?-|h-|w-)/.test(cls);
        var combinedClass = cls + ' fx-sel' + (styled ? '' : ' fx-sel-default') + (this.isOpen() ? ' is-open' : '') + (sel.disabled ? ' is-disabled' : '');
        if (this._lastClass !== combinedClass) {
            this.trigger.className = combinedClass;
            this._lastClass = combinedClass;
        }

        var styleAttr = sel.getAttribute('style') || '';
        if (this._lastStyle !== styleAttr) {
            this.trigger.setAttribute('style', styleAttr);
            this._lastStyle = styleAttr;
        }

        this.trigger.hidden = sel.hidden;
    };

    SelectInstance.prototype.clearValue = function () {
        var sel = this.select;
        if (sel.disabled) return;
        // Pick first option if it's empty, or reset selectedIndex
        if (sel.options.length > 0 && sel.options[0].value === '') {
            this.pickIndex(0);
        } else {
            sel.value = '';
            this.pickIndex(0);
        }
    };

    SelectInstance.prototype.isOpen = function () {
        return !!this.panel;
    };

    SelectInstance.prototype.open = function () {
        if (this.select.disabled) return;
        if (activeInstance && activeInstance !== this) {
            activeInstance.close();
        }
        activeInstance = this;

        var self = this;
        var sel = this.select;
        var hasNoSearch = sel.hasAttribute('data-no-search') || sel.getAttribute('data-search') === 'false';
        // Enable search by default unless explicitly disabled, or if only 1-2 options
        var showSearch = !hasNoSearch && (sel.options.length > 2 || sel.hasAttribute('data-search'));

        var panel = document.createElement('div');
        panel.className = 'fx-sel-panel';
        if (document.documentElement.classList.contains('dark')) {
            panel.classList.add('is-dark');
        }

        var searchHtml = '';
        if (showSearch) {
            var placeholder = sel.getAttribute('data-search-placeholder') || t('Search…');
            searchHtml = 
                '<div class="fx-sel-search">' +
                    '<span class="fx-sel-search-icon">' + ICONS.search + '</span>' +
                    '<input type="text" placeholder="' + escapeHtml(placeholder) + '" autocomplete="off" spellcheck="false" aria-label="' + escapeHtml(placeholder) + '">' +
                    '<button type="button" class="fx-sel-search-clear hidden" aria-label="' + escapeHtml(t('Clear')) + '">' + ICONS.clear + '</button>' +
                '</div>';
        }

        panel.innerHTML = searchHtml + '<ul class="fx-sel-list" role="listbox" id="' + this.id + '"></ul>';
        document.body.appendChild(panel);

        this.panel = panel;
        this.list = panel.querySelector('.fx-sel-list');
        this.query = '';
        this.trigger.classList.add('is-open');
        this.trigger.setAttribute('aria-expanded', 'true');

        this.renderList();
        this.position();

        // Reveal animation
        requestAnimationFrame(function () {
            panel.classList.add('is-visible');
        });

        // Search Input listeners
        var searchInput = panel.querySelector('.fx-sel-search input');
        var searchClear = panel.querySelector('.fx-sel-search-clear');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                self.query = searchInput.value.trim().toLowerCase();
                if (searchClear) searchClear.classList.toggle('hidden', !self.query);
                self.renderList();
            });

            if (searchClear) {
                searchClear.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    searchInput.value = '';
                    self.query = '';
                    searchClear.classList.add('hidden');
                    self.renderList();
                    searchInput.focus();
                });
            }

            searchInput.addEventListener('keydown', function (e) {
                self.onListKey(e);
            });

            setTimeout(function () {
                searchInput.focus();
            }, 10);
        } else {
            this.list.setAttribute('tabindex', '-1');
            this.list.addEventListener('keydown', function (e) {
                self.onListKey(e);
            });
            setTimeout(function () {
                self.list.focus({ preventScroll: true });
            }, 10);
        }

        // List item selection
        this.list.addEventListener('mousedown', function (e) {
            e.preventDefault();
        });

        this.list.addEventListener('click', function (e) {
            var item = e.target.closest('[data-opt-index]');
            if (item && !item.classList.contains('is-disabled')) {
                self.pickIndex(+item.getAttribute('data-opt-index'));
            }
        });

        this.list.addEventListener('mousemove', function (e) {
            var item = e.target.closest('[data-pos]');
            if (item && !item.classList.contains('is-disabled')) {
                self.setActiveIndex(+item.getAttribute('data-pos'), false);
            }
        });

        this._repositionHandler = function () {
            self.position();
        };
        window.addEventListener('resize', this._repositionHandler);
        window.addEventListener('scroll', this._repositionHandler, true);
    };

    SelectInstance.prototype.close = function (returnFocus) {
        if (!this.panel) return;
        var p = this.panel;
        this.panel = null;
        this.list = null;
        p.classList.remove('is-visible');
        setTimeout(function () {
            if (p.parentNode) p.remove();
        }, 140);

        this.trigger.classList.remove('is-open');
        this.trigger.setAttribute('aria-expanded', 'false');

        window.removeEventListener('resize', this._repositionHandler);
        window.removeEventListener('scroll', this._repositionHandler, true);

        if (activeInstance === this) activeInstance = null;
        if (returnFocus) this.trigger.focus();
    };

    SelectInstance.prototype.renderList = function () {
        if (!this.list) return;

        var q = (this.query || '').trim().toLowerCase();
        var words = q ? q.split(/\s+/).filter(Boolean) : [];
        var html = '';
        var pos = 0;
        var sel = this.select;
        var self = this;
        this.visibleIndices = [];
        var pendingGroup = null;

        this.getOptions().forEach(function (item) {
            if (item.isGroup) {
                pendingGroup = item.label;
                return;
            }

            var opt = item.opt;
            var text = (opt.getAttribute('data-label') || opt.text || '').trim();
            var val = (opt.value || '').trim();
            var hint = (opt.getAttribute('data-hint') || '').trim();
            var icon = (opt.getAttribute('data-icon') || '').trim();
            var searchTerms = (opt.getAttribute('data-search-terms') || '').trim();

            if (words.length > 0) {
                var haystack = (text + ' ' + val + ' ' + hint + ' ' + searchTerms).toLowerCase();
                var matches = words.every(function (w) {
                    return haystack.indexOf(w) !== -1;
                });
                if (!matches) return;
            }

            if (pendingGroup !== null) {
                html += '<li class="fx-sel-group" role="presentation">' + escapeHtml(pendingGroup) + '</li>';
                pendingGroup = null;
            }

            var isSelected = opt.index === sel.selectedIndex;
            var labelHtml = escapeHtml(text);

            // Highlight matches
            if (q && text.toLowerCase().indexOf(q) !== -1) {
                var startIdx = text.toLowerCase().indexOf(q);
                labelHtml = escapeHtml(text.slice(0, startIdx)) + 
                            '<mark class="fx-sel-mark">' + escapeHtml(text.slice(startIdx, startIdx + q.length)) + '</mark>' + 
                            escapeHtml(text.slice(startIdx + q.length));
            }

            var iconHtml = icon ? '<i class="' + escapeHtml(icon) + ' fx-sel-opt-icon"></i>' : '';
            var hintHtml = hint ? '<small class="fx-sel-hint">' + escapeHtml(hint) + '</small>' : '';
            var isPlaceholder = opt.value === '';

            html += 
                '<li role="option" data-opt-index="' + opt.index + '" data-pos="' + pos + '" ' +
                    'class="fx-sel-opt' + (isSelected ? ' is-selected' : '') + (item.disabled ? ' is-disabled' : '') + 
                    (item.isGrouped ? ' is-grouped' : '') + (isPlaceholder ? ' is-placeholder' : '') + '" ' +
                    'aria-selected="' + (isSelected ? 'true' : 'false') + '"' + (item.disabled ? ' aria-disabled="true"' : '') + '>' +
                    '<span class="fx-sel-opt-content">' +
                        iconHtml +
                        '<span class="fx-sel-opt-title">' + (labelHtml || '&nbsp;') + hintHtml + '</span>' +
                    '</span>' +
                    '<span class="fx-sel-check">' + ICONS.check + '</span>' +
                '</li>';

            self.visibleIndices.push(opt.index);
            pos++;
        });

        if (!pos) {
            html = 
                '<li class="fx-sel-empty" role="presentation">' +
                    '<span class="fx-sel-empty-icon">' + ICONS.empty + '</span>' +
                    '<span>' + escapeHtml(t('No matches found')) + '</span>' +
                '</li>';
        }

        this.list.innerHTML = html;

        var currentIndex = this.visibleIndices.indexOf(sel.selectedIndex);
        this.setActiveIndex(currentIndex === -1 ? 0 : currentIndex, true);
    };

    SelectInstance.prototype.setActiveIndex = function (pos, shouldScroll) {
        if (!this.list) return;
        this.activeIndex = pos;
        var items = this.list.querySelectorAll('.fx-sel-opt');
        items.forEach(function (li, i) {
            li.classList.toggle('is-active', i === pos);
        });
        if (shouldScroll && items[pos]) {
            items[pos].scrollIntoView({ block: 'nearest' });
        }
    };

    SelectInstance.prototype.moveActive = function (delta) {
        var items = this.list.querySelectorAll('.fx-sel-opt');
        var n = items.length;
        if (!n) return;
        var p = this.activeIndex;
        for (var k = 0; k < n; k++) {
            p = (p + delta + n) % n;
            if (!items[p].classList.contains('is-disabled')) break;
        }
        this.setActiveIndex(p, true);
    };

    SelectInstance.prototype.pickIndex = function (optIndex) {
        var sel = this.select;
        var hasChanged = sel.selectedIndex !== optIndex;
        sel.selectedIndex = optIndex;
        this.close(true);
        this.syncFromNative();

        if (hasChanged) {
            sel.dispatchEvent(new Event('input', { bubbles: true }));
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    SelectInstance.prototype.onTriggerKey = function (e) {
        var key = e.key;
        if (key === 'ArrowDown' || key === 'ArrowUp' || key === 'Enter' || key === ' ' || key === 'F4') {
            e.preventDefault();
            this.open();
            return;
        }
        // Direct alphanumeric jump when closed
        if (key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            var char = key.toLowerCase();
            var sel = this.select;
            var start = sel.selectedIndex;
            for (var i = 1; i <= sel.options.length; i++) {
                var opt = sel.options[(start + i) % sel.options.length];
                if (!opt.disabled && opt.text.trim().toLowerCase().indexOf(char) === 0) {
                    this.pickIndex(opt.index);
                    this.trigger.focus();
                    break;
                }
            }
        }
    };

    SelectInstance.prototype.onListKey = function (e) {
        var key = e.key;
        if (key === 'ArrowDown') {
            e.preventDefault();
            this.moveActive(1);
        } else if (key === 'ArrowUp') {
            e.preventDefault();
            this.moveActive(-1);
        } else if (key === 'Home') {
            e.preventDefault();
            this.setActiveIndex(0, true);
        } else if (key === 'End') {
            e.preventDefault();
            this.setActiveIndex(this.visibleIndices.length - 1, true);
        } else if (key === 'Enter') {
            e.preventDefault();
            var chosen = this.visibleIndices[this.activeIndex];
            if (chosen !== undefined) {
                this.pickIndex(chosen);
            }
        } else if (key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();
            this.close(true);
        } else if (key === 'Tab') {
            this.close();
        }
    };

    SelectInstance.prototype.position = function () {
        if (!this.panel) return;
        var triggerRect = this.trigger.getBoundingClientRect();
        var panel = this.panel;
        var viewportHeight = window.innerHeight;
        var viewportWidth = window.innerWidth;

        var minW = Math.max(triggerRect.width, 210);
        panel.style.minWidth = minW + 'px';
        panel.style.maxWidth = Math.max(minW, Math.min(420, viewportWidth - 20)) + 'px';

        var left = Math.min(triggerRect.left, viewportWidth - panel.offsetWidth - 10);
        panel.style.left = Math.max(10, left) + 'px';

        var spaceBelow = viewportHeight - triggerRect.bottom;
        var spaceAbove = triggerRect.top;
        var panelHeight = Math.min(panel.scrollHeight, 360);

        if (spaceBelow < panelHeight + 12 && spaceAbove > spaceBelow) {
            panel.style.top = '';
            panel.style.bottom = (viewportHeight - triggerRect.top + 6) + 'px';
            panel.classList.add('is-above');
        } else {
            panel.style.bottom = '';
            panel.style.top = (triggerRect.bottom + 6) + 'px';
            panel.classList.remove('is-above');
        }

        if (triggerRect.bottom < 0 || triggerRect.top > viewportHeight) {
            this.close();
        }
    };

    SelectInstance.prototype.destroy = function () {
        this.close();
        if (this.observer) this.observer.disconnect();
        if (this.trigger) this.trigger.remove();
        this.select.classList.remove('fx-sel-native');
        this.select.removeAttribute('tabindex');
        this.select.removeAttribute('aria-hidden');
        instances.delete(this.select);
    };

    // Override programmatic property setter for native selects to keep trigger updated
    ['value', 'selectedIndex'].forEach(function (prop) {
        var desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, prop);
        if (!desc || !desc.set) return;
        Object.defineProperty(HTMLSelectElement.prototype, prop, {
            configurable: true,
            enumerable: desc.enumerable,
            get: desc.get,
            set: function (val) {
                desc.set.call(this, val);
                var inst = instances.get(this);
                if (inst) inst.syncFromNative();
            }
        });
    });

    function enhance(root) {
        root = root || document;
        var selects = root.tagName === 'SELECT' ? [root] : root.querySelectorAll('select');
        Array.prototype.forEach.call(selects, function (sel) {
            if (instances.has(sel) || shouldSkip(sel) || !sel.parentNode) return;
            instances.set(sel, new SelectInstance(sel));
        });
    }

    // Global outside click handler
    document.addEventListener('mousedown', function (e) {
        if (activeInstance) {
            var insidePanel = e.target.closest('.fx-sel-panel');
            var insideTrigger = activeInstance.trigger && (e.target === activeInstance.trigger || activeInstance.trigger.contains(e.target));
            if (!insidePanel && !insideTrigger) {
                activeInstance.close();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && activeInstance) {
            activeInstance.close(true);
        }
    }, true);

    // Form resets
    document.addEventListener('reset', function (e) {
        setTimeout(function () {
            if (e.target.querySelectorAll) {
                e.target.querySelectorAll('select').forEach(function (s) {
                    var inst = instances.get(s);
                    if (inst) inst.syncFromNative();
                });
            }
        }, 0);
    });

    function boot() {
        enhance(document);

        // Auto enhance dynamically injected selects (AJAX / modals / alpine)
        new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (n) {
                    if (n.nodeType !== 1 || n.classList.contains('fx-sel-panel') || n.classList.contains('fx-sel')) return;
                    if (n.tagName === 'SELECT' || n.querySelector('select')) {
                        enhance(n);
                    }
                });
                m.removedNodes.forEach(function (n) {
                    if (n.nodeType !== 1) return;
                    var removedSelects = n.tagName === 'SELECT' ? [n] : (n.querySelectorAll ? n.querySelectorAll('select') : []);
                    Array.prototype.forEach.call(removedSelects, function (s) {
                        var inst = instances.get(s);
                        if (inst && !document.contains(s)) {
                            inst.destroy();
                        }
                    });
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.FxSelect = {
        enhance: enhance,
        refresh: function (sel) {
            if (!sel) return;
            var inst = instances.get(sel);
            if (inst) inst.syncFromNative();
            else enhance(sel);
        },
        destroy: function (sel) {
            if (!sel) return;
            var inst = instances.get(sel);
            if (inst) inst.destroy();
        }
    };
})(window, document);
