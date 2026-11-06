/**
 * Antigravity Admin Console - In-Sidebar Navigation Customizer
 * In-place customization inside the sidebar itself (exact same sidebar colors):
 * - Switches sidebar to "Customize menu" mode in-place
 * - Drag & drop reordering, Up/Down arrow buttons
 * - Eye icon to hide/show (Dashboard and Settings locked)
 * - "Reset to default" and "Done" buttons inside sidebar
 * - LocalStorage persistence across all pages and sessions
 */
(function ($, window, document) {
    'use strict';

    if (!window.AdminSidebarCustomizer) {
        window.AdminSidebarCustomizer = {};
    }

    var ASC = window.AdminSidebarCustomizer;
    var STORAGE_KEY = 'admin_sidebar_customization';
    var defaultItems = [];
    var wasMini = false;

    // Scan default items from sidebar navigation
    function scanDefaultSidebarItems() {
        defaultItems = [];
        $('#sidebarNav .sb-item').each(function () {
            var $item = $(this);
            var $group = $item.closest('.sb-group');
            var key = $item.attr('data-nav-key') || $item.attr('href');
            var label = $item.attr('data-nav-label') || $item.find('.sb-label').text().trim();
            var icon = $item.attr('data-nav-icon') || ($item.find('.sb-icon i').attr('class') || '').replace(/^.*ph-([a-z0-9-]+).*$/, '$1');
            var isLocked = $item.attr('data-locked') === '1' || /dashboard|setting/i.test(key) || /dashboard|setting/i.test(label);

            if (key) {
                defaultItems.push({
                    key: key,
                    label: label,
                    icon: icon || 'squares-four',
                    href: $item.attr('href'),
                    locked: isLocked,
                    groupKey: $group.attr('data-group') || 'main'
                });
            }
        });
    }

    function renderItemCard(item, isHidden) {
        var iconHtml = '<i class="ph-duotone ph-' + item.icon + '"></i>';
        var actionHtml = '';

        if (item.locked) {
            actionHtml = '<span class="sb-cust-lock" title="Always shown"><i class="ph-fill ph-lock-simple"></i></span>';
        } else {
            actionHtml = 
                '<button type="button" class="sb-cust-btn-eye ' + (isHidden ? 'is-hidden-state' : '') + '" title="Toggle visibility">' +
                '    <i class="ph ' + (isHidden ? 'ph-eye-slash' : 'ph-eye') + '"></i>' +
                '</button>';
        }

        return (
            '<div class="sb-cust-item ' + (isHidden ? 'is-hidden-menu' : '') + '" data-key="' + item.key + '" draggable="true">' +
            '    <span class="sb-cust-drag-handle" title="Drag to reorder">' +
            '        <i class="ph ph-dots-six-vertical"></i>' +
            '    </span>' +
            '    <span class="sb-cust-icon">' + iconHtml + '</span>' +
            '    <span class="sb-cust-title">' + $('<div>').text(item.label).html() + '</span>' +
            '    <button type="button" class="sb-cust-btn-arrow btn-up" title="Move up"><i class="ph ph-caret-up"></i></button>' +
            '    <button type="button" class="sb-cust-btn-arrow btn-down" title="Move down"><i class="ph ph-caret-down"></i></button>' +
            '    ' + actionHtml +
            '</div>'
        );
    }

    function updateArrowStates() {
        var $cards = $('#sbCustList .sb-cust-item');
        $cards.each(function (idx) {
            var $up = $(this).find('.btn-up');
            var $down = $(this).find('.btn-down');
            $up.prop('disabled', idx === 0);
            $down.prop('disabled', idx === $cards.length - 1);
        });
    }

    function getDragAfterElement(container, y) {
        var elements = container.querySelectorAll('.sb-cust-item:not(.is-dragging)');
        var closest = { offset: Number.NEGATIVE_INFINITY, element: null };

        for (var i = 0; i < elements.length; i++) {
            var box = elements[i].getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                closest = { offset: offset, element: elements[i] };
            }
        }
        return closest.element;
    }

    // Public API
    ASC.open = function () {
        scanDefaultSidebarItems();

        // If sidebar is mini, expand it temporarily during customization
        if (document.documentElement.classList.contains('sidebar-mini')) {
            wasMini = true;
            document.documentElement.classList.remove('sidebar-mini');
        } else {
            wasMini = false;
        }

        var saved = ASC.loadState();
        var savedOrder = saved ? saved.order : [];
        var savedHidden = saved ? (saved.hidden || []) : [];

        var orderedItems = [];
        var usedKeys = {};

        if (savedOrder && savedOrder.length) {
            savedOrder.forEach(function (key) {
                var item = defaultItems.find(function (d) { return d.key === key; });
                if (item) {
                    orderedItems.push(item);
                    usedKeys[key] = true;
                }
            });
        }

        defaultItems.forEach(function (d) {
            if (!usedKeys[d.key]) {
                orderedItems.push(d);
            }
        });

        // Populate cards
        var $list = $('#sbCustList').empty();
        orderedItems.forEach(function (item) {
            var isHidden = !item.locked && savedHidden.indexOf(item.key) !== -1;
            $list.append(renderItemCard(item, isHidden));
        });

        updateArrowStates();

        // Switch to In-Sidebar Customize Mode
        $('#sidebarNormalView').hide();
        $('#sidebarCustomizerView').show();
    };

    ASC.close = function () {
        $('#sidebarCustomizerView').hide();
        $('#sidebarNormalView').show();

        if (wasMini) {
            document.documentElement.classList.add('sidebar-mini');
        }
    };

    ASC.save = function () {
        var order = [];
        var hidden = [];

        $('#sbCustList .sb-cust-item').each(function () {
            var key = $(this).attr('data-key');
            order.push(key);
            if ($(this).hasClass('is-hidden-menu')) {
                hidden.push(key);
            }
        });

        var state = { order: order, hidden: hidden };
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch (e) {}

        ASC.applyToSidebar(state);
        ASC.close();

        if (window.AdminToast && window.AdminToast.show) {
            window.AdminToast.show('success', 'Sidebar menu customized successfully.');
        } else if (window.toastr && window.toastr.success) {
            window.toastr.success('Sidebar menu customized successfully.');
        }
    };

    ASC.reset = function () {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {}

        scanDefaultSidebarItems();
        var $list = $('#sbCustList').empty();
        defaultItems.forEach(function (item) {
            $list.append(renderItemCard(item, false));
        });
        updateArrowStates();

        ASC.applyToSidebar({ order: defaultItems.map(function (d) { return d.key; }), hidden: [] });

        if (window.AdminToast && window.AdminToast.show) {
            window.AdminToast.show('info', 'Sidebar menu reset to default.');
        }
    };

    ASC.loadState = function () {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    };

    // Apply custom order & visibility to actual sidebar DOM
    ASC.applyToSidebar = function (state) {
        if (!state) state = ASC.loadState();
        if (!state) return;

        var order = state.order || [];
        var hidden = state.hidden || [];
        var $nav = $('#sidebarNav');
        if (!$nav.length) return;

        // Reorder items in DOM
        order.forEach(function (key) {
            var $link = $nav.find('.sb-item[data-nav-key="' + key + '"]');
            if ($link.length) {
                var $li = $link.closest('li');
                var $ul = $li.closest('ul.sb-group-items');
                if ($ul.length) {
                    $ul.append($li);
                }
            }
        });

        // Hide / show items
        $nav.find('.sb-item').each(function () {
            var key = $(this).attr('data-nav-key') || $(this).attr('href');
            var isLocked = $(this).attr('data-locked') === '1' || /dashboard|setting/i.test(key);
            var shouldHide = !isLocked && hidden.indexOf(key) !== -1;
            var $li = $(this).closest('li');

            if (shouldHide) {
                $li.hide();
            } else {
                $li.show();
            }
        });

        // Hide group if all items are hidden
        $nav.find('.sb-group').each(function () {
            var visibleItems = $(this).find('ul.sb-group-items li').filter(function () {
                return $(this).css('display') !== 'none';
            });
            $(this).toggle(visibleItems.length > 0);
        });
    };

    // Bind In-Place Events
    $(function () {
        scanDefaultSidebarItems();
        ASC.applyToSidebar();

        var $list = $('#sbCustList');

        // Open customize mode in sidebar
        $(document).on('click', '#openSidebarCustomizerBtn', function (e) {
            e.preventDefault();
            ASC.open();
        });

        // Close / Exit customize mode
        $(document).on('click', '#sbCustCloseInlineBtn', function (e) {
            e.preventDefault();
            ASC.close();
        });

        // Up arrow click
        $list.on('click', '.btn-up', function () {
            var $card = $(this).closest('.sb-cust-item');
            var $prev = $card.prev('.sb-cust-item');
            if ($prev.length) {
                $card.insertBefore($prev);
                updateArrowStates();
            }
        });

        // Down arrow click
        $list.on('click', '.btn-down', function () {
            var $card = $(this).closest('.sb-cust-item');
            var $next = $card.next('.sb-cust-item');
            if ($next.length) {
                $card.insertAfter($next);
                updateArrowStates();
            }
        });

        // Eye toggle click
        $list.on('click', '.sb-cust-btn-eye', function () {
            var $card = $(this).closest('.sb-cust-item');
            var isHidden = $card.hasClass('is-hidden-menu');

            if (isHidden) {
                $card.removeClass('is-hidden-menu');
                $(this).removeClass('is-hidden-state');
                $(this).find('i').attr('class', 'ph ph-eye');
            } else {
                $card.addClass('is-hidden-menu');
                $(this).addClass('is-hidden-state');
                $(this).find('i').attr('class', 'ph ph-eye-slash');
            }
        });

        // Smooth Drag and Drop in Sidebar
        var draggedEl = null;

        $list.on('dragstart', '.sb-cust-item', function (e) {
            draggedEl = this;
            if (e.originalEvent && e.originalEvent.dataTransfer) {
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', $(this).attr('data-key'));
            }
            setTimeout(function () {
                if (draggedEl) $(draggedEl).addClass('is-dragging');
            }, 0);
        });

        $list.on('dragover', function (e) {
            e.preventDefault();
            if (!draggedEl) return;
            var clientY = e.originalEvent ? e.originalEvent.clientY : e.clientY;
            var afterElement = getDragAfterElement(this, clientY);

            if (!afterElement) {
                this.appendChild(draggedEl);
            } else if (afterElement !== draggedEl) {
                this.insertBefore(draggedEl, afterElement);
            }
            updateArrowStates();
        });

        $list.on('dragend', '.sb-cust-item', function () {
            if (draggedEl) {
                $(draggedEl).removeClass('is-dragging');
                draggedEl = null;
            }
            updateArrowStates();
        });

        // Reset & Done buttons
        $(document).on('click', '#sbCustResetBtn', function () {
            ASC.reset();
        });

        $(document).on('click', '#sbCustDoneBtn', function () {
            ASC.save();
        });
    });

})(jQuery, window, document);
