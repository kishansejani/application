/**
 * Antigravity Admin Console - Table Column Customizer & Reorder Runtime
 * Matches Image 1 (toolbar column icon) & Image 2 (two-column modal dialog)
 * - Single column manage button per table (no duplicates)
 * - Removed list density (compact/comfortable)
 * - Precise, smooth drag-and-drop column reordering (in modal and on table headers)
 * - Real-time column search, reset to default, and localStorage persistence
 */
(function ($, window, document) {
    'use strict';

    if (!window.AdminColumnManager) {
        window.AdminColumnManager = {};
    }

    var ACM = window.AdminColumnManager;
    var currentTable = null;
    var currentDt = null;
    var originalColumns = []; // [{ idx: 0, title: 'User', visible: true, defaultVisible: true }]

    // I18N helper
    function t(str) {
        if (!str || typeof str !== 'string') return str;
        if (typeof window.__ === 'function') {
            return window.__(str);
        }
        var cfg = window.AdminConfig || {};
        if (cfg.locale !== 'gu') return str;
        var I18N = cfg.i18n || {};
        var key = str.replace(/\s+/g, ' ').trim();
        if (I18N[key]) return I18N[key];
        var keyEllipsis = key.replace(/\.{3}$/, '…');
        if (I18N[keyEllipsis]) return I18N[keyEllipsis];
        var keyDots = key.replace(/…$/, '...');
        if (I18N[keyDots]) return I18N[keyDots];
        return str;
    }

    // Unique table ID helper
    function getTableKey(tableEl) {
        var id = $(tableEl).attr('id');
        if (id) return 'dt_cols_' + id;
        var exportTitle = $(tableEl).attr('data-export-title');
        if (exportTitle) return 'dt_cols_' + exportTitle.replace(/[^a-zA-Z0-9_-]/g, '_');
        return 'dt_cols_' + window.location.pathname.replace(/[^a-zA-Z0-9_-]/g, '_');
    }

    // Clean column title from header
    function getCleanHeaderTitle(th) {
        var $th = $(th);
        var title = $th.clone().find('.no-export, .sr-only, script, style').remove().end().text();
        title = title.replace(/[↑↓⇅▲▼]/g, '').replace(/\s+/g, ' ').trim();
        return title || 'Column ' + ($th.index() + 1);
    }

    // Get module title for modal header (e.g. "User columns", "Role columns", "Donation columns")
    function getModuleColumnTitle(tableEl) {
        var explicit = $(tableEl).attr('data-export-title');
        if (explicit) {
            return explicit + ' columns';
        }
        var pageTitle = document.title.split('·')[0].trim();
        if (pageTitle && pageTitle !== 'Admin Console' && pageTitle !== 'Admin Portal') {
            return pageTitle + ' columns';
        }
        return 'Table columns';
    }

    // Modal DOM Injection
    function ensureModalInjected() {
        if (document.getElementById('colCustomizerModal')) return;

        var modalHtml = 
            '<div id="colCustomizerModal" class="col-modal-backdrop is-hidden" aria-hidden="true">' +
            '    <div class="col-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="colModalTitle">' +
            '        <!-- Header -->' +
            '        <div class="col-modal-header">' +
            '            <div>' +
            '                <h3 id="colModalTitle" class="col-modal-title">' + t('Table columns') + '</h3>' +
            '                <p class="col-modal-subtitle" id="colModalSubtitle">' + t('Tick to add. Drag to reorder.') + '</p>' +
            '            </div>' +
            '            <button type="button" class="col-modal-close" id="colModalCloseBtn" aria-label="' + t('Close') + '">' +
            '                <i class="ph ph-x"></i>' +
            '            </button>' +
            '        </div>' +
            '        <!-- Body: 2 Columns -->' +
            '        <div class="col-modal-body">' +
            '            <!-- Left: Search & Checkbox list -->' +
            '            <div class="col-modal-left">' +
            '                <div class="col-search-wrap">' +
            '                    <input type="search" id="colSearchInput" class="col-search-input" placeholder="' + t('Search columns') + '" autocomplete="off">' +
            '                </div>' +
            '                <div class="col-checkbox-list" id="colCheckboxList">' +
            '                    <!-- Checkboxes populated dynamically -->' +
            '                </div>' +
            '            </div>' +
            '            <!-- Right: Shown header & Draggable items -->' +
            '            <div class="col-modal-right">' +
            '                <div class="col-shown-header">' +
            '                    <span id="colShownLabel">' + t('Shown') + ' (<span id="colShownCount">0</span>)</span>' +
            '                </div>' +
            '                <div class="col-draggable-list" id="colDraggableList">' +
            '                    <!-- Draggable items populated dynamically -->' +
            '                </div>' +
            '            </div>' +
            '        </div>' +
            '        <!-- Footer actions -->' +
            '        <div class="col-modal-footer">' +
            '            <button type="button" class="col-btn-reset" id="colBtnReset">' + t('Reset columns') + '</button>' +
            '            <div class="col-footer-actions">' +
            '                <button type="button" class="col-btn-cancel" id="colBtnCancel">' + t('Cancel') + '</button>' +
            '                <button type="button" class="col-btn-apply" id="colBtnApply">' + t('Apply') + '</button>' +
            '            </div>' +
            '        </div>' +
            '    </div>' +
            '</div>';

        $('body').append(modalHtml);
        bindModalEvents();
    }

    // Modal Events Binding
    function bindModalEvents() {
        var $modal = $('#colCustomizerModal');
        var $searchInput = $('#colSearchInput');
        var $checkboxList = $('#colCheckboxList');
        var $draggableList = $('#colDraggableList');

        // Close modal handlers
        $('#colModalCloseBtn, #colBtnCancel').on('click', function () {
            ACM.close();
        });

        $modal.on('click', function (e) {
            if (e.target === this) ACM.close();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && !$modal.hasClass('is-hidden')) {
                ACM.close();
            }
        });

        // Search filtering in left column
        $searchInput.on('input', function () {
            var q = $(this).val().toLowerCase().trim();
            $checkboxList.find('.col-check-row').each(function () {
                var label = $(this).find('.col-check-label').text().toLowerCase();
                $(this).toggle(label.indexOf(q) !== -1);
            });
        });

        // Checkbox change -> update right side shown list
        $checkboxList.on('change', 'input[type="checkbox"]', function () {
            var colIdx = parseInt($(this).attr('data-col-idx'), 10);
            var isChecked = $(this).is(':checked');
            var col = originalColumns.find(function (c) { return c.idx === colIdx; });
            if (!col) return;

            if (isChecked) {
                if (!$draggableList.find('.col-drag-item[data-col-idx="' + colIdx + '"]').length) {
                    var itemHtml = renderDraggableItem(col);
                    $draggableList.append(itemHtml);
                }
            } else {
                $draggableList.find('.col-drag-item[data-col-idx="' + colIdx + '"]').remove();
            }
            updateShownCount();
        });

        // =========================================================================
        // Precise Real-Time Drag and Drop in Right Column
        // Only the dragged item moves; others smoothly shift into position!
        // =========================================================================
        var draggedEl = null;

        $draggableList.on('dragstart', '.col-drag-item', function (e) {
            draggedEl = this;
            var dtTransfer = e.originalEvent ? e.originalEvent.dataTransfer : null;
            if (dtTransfer) {
                dtTransfer.effectAllowed = 'move';
                dtTransfer.setData('text/plain', $(this).attr('data-col-idx'));
            }
            // Slight delay so the drag ghost image captures the regular appearance
            setTimeout(function () {
                if (draggedEl) {
                    $(draggedEl).addClass('is-dragging');
                }
            }, 0);
        });

        $draggableList.on('dragover', function (e) {
            e.preventDefault();
            if (!draggedEl) return;
            var dtTransfer = e.originalEvent ? e.originalEvent.dataTransfer : null;
            if (dtTransfer) {
                dtTransfer.dropEffect = 'move';
            }

            var clientY = e.originalEvent ? e.originalEvent.clientY : e.clientY;
            var afterElement = getDragAfterElement(this, clientY);

            if (!afterElement) {
                this.appendChild(draggedEl);
            } else if (afterElement !== draggedEl) {
                this.insertBefore(draggedEl, afterElement);
            }
        });

        $draggableList.on('dragend', '.col-drag-item', function () {
            if (draggedEl) {
                $(draggedEl).removeClass('is-dragging');
                draggedEl = null;
            }
        });

        // Touch support for mobile/tablets
        initTouchReorder($draggableList[0]);

        // Reset columns button
        $('#colBtnReset').on('click', function () {
            ACM.resetCurrentTable();
        });

        // Apply button
        $('#colBtnApply').on('click', function () {
            ACM.apply();
        });
    }

    // Helper: Finds the exact element to insert before based on mouse Y position
    function getDragAfterElement(container, y) {
        var draggableElements = container.querySelectorAll('.col-drag-item:not(.is-dragging)');
        var closest = { offset: Number.NEGATIVE_INFINITY, element: null };

        for (var i = 0; i < draggableElements.length; i++) {
            var box = draggableElements[i].getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                closest = { offset: offset, element: draggableElements[i] };
            }
        }
        return closest.element;
    }

    // Touch drag reorder helper
    function initTouchReorder(listEl) {
        if (!listEl) return;
        var activeItem = null;

        listEl.addEventListener('touchstart', function (e) {
            var item = e.target.closest('.col-drag-item');
            if (!item) return;

            activeItem = item;
            activeItem.classList.add('is-dragging');
        }, { passive: true });

        listEl.addEventListener('touchmove', function (e) {
            if (!activeItem) return;
            var touch = e.touches[0];
            var target = document.elementFromPoint(touch.clientX, touch.clientY);
            if (!target) return;
            var overItem = target.closest('.col-drag-item');
            if (overItem && overItem !== activeItem && overItem.parentNode === listEl) {
                var rect = overItem.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (touch.clientY < mid) {
                    listEl.insertBefore(activeItem, overItem);
                } else {
                    listEl.insertBefore(activeItem, overItem.nextSibling);
                }
            }
        }, { passive: true });

        var endTouch = function () {
            if (activeItem) {
                activeItem.classList.remove('is-dragging');
                activeItem = null;
            }
        };
        listEl.addEventListener('touchend', endTouch);
        listEl.addEventListener('touchcancel', endTouch);
    }

    function renderDraggableItem(col) {
        return (
            '<div class="col-drag-item" data-col-idx="' + col.idx + '" draggable="true">' +
            '    <span class="col-drag-handle" title="' + t('Drag to reorder') + '">' +
            '        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">' +
            '            <circle cx="5" cy="3" r="1.5"/><circle cx="11" cy="3" r="1.5"/>' +
            '            <circle cx="5" cy="8" r="1.5"/><circle cx="11" cy="8" r="1.5"/>' +
            '            <circle cx="5" cy="13" r="1.5"/><circle cx="11" cy="13" r="1.5"/>' +
            '        </svg>' +
            '    </span>' +
            '    <span class="col-drag-title">' + $('<div>').text(col.title).html() + '</span>' +
            '</div>'
        );
    }

    function updateShownCount() {
        var count = $('#colDraggableList .col-drag-item').length;
        $('#colShownCount').text(count);
        $('#colShownLabel').html(t('Shown') + ' (<span id="colShownCount">' + count + '</span>)');
    }

    // Public API
    ACM.open = function (tableTarget) {
        ensureModalInjected();

        // Refresh translations on open in case language changed
        $('#colModalSubtitle').text(t('Tick to add. Drag to reorder.'));
        $('#colModalCloseBtn').attr('aria-label', t('Close'));
        $('#colBtnReset').text(t('Reset columns'));
        $('#colBtnCancel').text(t('Cancel'));
        $('#colBtnApply').text(t('Apply'));

        var $table = $(tableTarget);
        if (!$table.length) {
            $table = $('table.dataTable').first();
        }
        if (!$table.length) {
            $table = $('table').first();
        }
        if (!$table.length) return;

        currentTable = $table[0];
        var isDt = $.fn.dataTable && $.fn.dataTable.isDataTable(currentTable);
        currentDt = isDt ? $table.DataTable() : null;

        // Set Title
        var title = getModuleColumnTitle(currentTable);
        $('#colModalTitle').text(t(title));

        // Scan columns
        originalColumns = [];
        var currentOrder = [];

        if (currentDt) {
            var colCount = currentDt.columns().count();
            if (currentDt.colReorder && typeof currentDt.colReorder.order === 'function') {
                currentOrder = currentDt.colReorder.order();
            } else {
                for (var i = 0; i < colCount; i++) currentOrder.push(i);
            }

            for (var c = 0; c < colCount; c++) {
                var thNode = currentDt.column(c).header();
                var colTitle = getCleanHeaderTitle(thNode);
                var isVisible = currentDt.column(c).visible();
                originalColumns.push({
                    idx: c,
                    title: colTitle,
                    visible: isVisible,
                    defaultVisible: isVisible
                });
            }
        } else {
            // Standard non-DataTable table fallback
            var $ths = $table.find('thead tr:first th');
            $ths.each(function (idx) {
                var colTitle = getCleanHeaderTitle(this);
                var isVisible = $(this).is(':visible');
                originalColumns.push({
                    idx: idx,
                    title: colTitle,
                    visible: isVisible,
                    defaultVisible: isVisible
                });
                currentOrder.push(idx);
            });
        }

        // Setup search input placeholder
        var searchPlaceholder = (window.AdminConfig && window.AdminConfig.locale === 'gu') 
            ? (originalColumns.length + ' કૉલમ શોધો') 
            : ('Search ' + originalColumns.length + ' columns');
        $('#colSearchInput').val('').attr('placeholder', searchPlaceholder);

        // Populate Left Checkbox List
        var $checkboxList = $('#colCheckboxList').empty();
        originalColumns.forEach(function (col) {
            var isChecked = col.visible;
            var checkRowHtml =
                '<label class="col-check-row">' +
                '    <input type="checkbox" class="col-checkbox" data-col-idx="' + col.idx + '"' + (isChecked ? ' checked' : '') + '>' +
                '    <span class="col-check-label">' + $('<div>').text(col.title).html() + '</span>' +
                '</label>';
            $checkboxList.append(checkRowHtml);
        });

        // Populate Right Draggable List in currentOrder sequence
        var $draggableList = $('#colDraggableList').empty();
        currentOrder.forEach(function (colIdx) {
            var col = originalColumns.find(function (c) { return c.idx === colIdx; });
            if (col && col.visible) {
                $draggableList.append(renderDraggableItem(col));
            }
        });
        updateShownCount();

        // Show Modal
        $('#colCustomizerModal').removeClass('is-hidden');
    };

    ACM.close = function () {
        $('#colCustomizerModal').addClass('is-hidden');
    };

    ACM.apply = function () {
        if (!currentTable) return;

        // Collect new ordered visible column indexes from right list
        var shownIndexes = [];
        $('#colDraggableList .col-drag-item').each(function () {
            shownIndexes.push(parseInt($(this).attr('data-col-idx'), 10));
        });

        // Collect hidden column indexes
        var hiddenIndexes = [];
        originalColumns.forEach(function (c) {
            if (shownIndexes.indexOf(c.idx) === -1) {
                hiddenIndexes.push(c.idx);
            }
        });

        // Full reorder array: all shown columns first, then hidden columns
        var fullNewOrder = shownIndexes.concat(hiddenIndexes);

        // Apply to DataTable if present
        if (currentDt) {
            // Apply column order first
            if (currentDt.colReorder && typeof currentDt.colReorder.order === 'function') {
                try {
                    currentDt.colReorder.order(fullNewOrder, true);
                } catch (e) {
                    console.warn('ColReorder order apply:', e);
                }
            }

            // Apply visibility
            originalColumns.forEach(function (c) {
                var isVisible = shownIndexes.indexOf(c.idx) !== -1;
                try {
                    currentDt.column(c.idx).visible(isVisible, false);
                } catch (e) {
                    console.warn('Column visibility apply:', e);
                }
            });

            // Redraw and adjust columns
            try {
                currentDt.columns.adjust().draw(false);
                if (currentDt.responsive) currentDt.responsive.recalc();
            } catch (e) {}
        } else {
            // Fallback for non-DataTables tables
            ACM.reorderDomTable(currentTable, fullNewOrder, shownIndexes);
        }

        // Save State to localStorage
        ACM.saveState(currentTable, {
            order: fullNewOrder,
            shown: shownIndexes
        });

        // Toast feedback
        var successMsg = t('Columns updated successfully.');
        if (window.AdminToast && window.AdminToast.show) {
            window.AdminToast.show('success', successMsg);
        } else if (window.toastr && window.toastr.success) {
            window.toastr.success(successMsg);
        }

        ACM.close();
    };

    ACM.resetCurrentTable = function () {
        if (!currentTable) return;

        var key = getTableKey(currentTable);
        localStorage.removeItem(key);

        if (currentDt) {
            if (currentDt.colReorder && typeof currentDt.colReorder.reset === 'function') {
                try { currentDt.colReorder.reset(); } catch (e) {}
            }
            originalColumns.forEach(function (c) {
                try {
                    currentDt.column(c.idx).visible(c.defaultVisible, false);
                } catch (e) {}
            });
            try {
                currentDt.columns.adjust().draw(false);
                if (currentDt.responsive) currentDt.responsive.recalc();
            } catch (e) {}
        }

        ACM.open(currentTable);

        var resetMsg = t('Columns reset to default.');
        if (window.AdminToast && window.AdminToast.show) {
            window.AdminToast.show('info', resetMsg);
        }
    };

    ACM.saveState = function (tableEl, state) {
        var key = getTableKey(tableEl);
        try {
            localStorage.setItem(key, JSON.stringify(state));
        } catch (e) {}
    };

    ACM.loadState = function (tableEl) {
        var key = getTableKey(tableEl);
        try {
            var raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    };

    ACM.restoreTableState = function (tableEl) {
        var saved = ACM.loadState(tableEl);
        if (!saved) return;

        var isDt = $.fn.dataTable && $.fn.dataTable.isDataTable(tableEl);
        if (isDt) {
            var dt = $(tableEl).DataTable();

            // Restore column order
            if (saved.order && dt.colReorder && typeof dt.colReorder.order === 'function') {
                try {
                    dt.colReorder.order(saved.order, true);
                } catch (e) {}
            }

            // Restore column visibility
            if (saved.shown && Array.isArray(saved.shown)) {
                var total = dt.columns().count();
                for (var i = 0; i < total; i++) {
                    var isShown = saved.shown.indexOf(i) !== -1;
                    try {
                        dt.column(i).visible(isShown, false);
                    } catch (e) {}
                }
            }

            try {
                dt.columns.adjust().draw(false);
                if (dt.responsive) dt.responsive.recalc();
            } catch (e) {}
        } else {
            if (saved.order && saved.shown) {
                ACM.reorderDomTable(tableEl, saved.order, saved.shown);
            }
        }
    };

    // Reorder DOM table directly (for non-DataTables fallback)
    ACM.reorderDomTable = function (tableEl, fullOrder, shownIndexes) {
        var $table = $(tableEl);
        var $rows = $table.find('tr');

        $rows.each(function () {
            var $row = $(this);
            var $cells = $row.children('th, td');
            if ($cells.length < fullOrder.length) return;

            var cellMap = {};
            $cells.each(function (idx) {
                cellMap[idx] = this;
            });

            fullOrder.forEach(function (origIdx) {
                var cell = cellMap[origIdx];
                if (cell) {
                    $row.append(cell);
                    if (shownIndexes.indexOf(origIdx) === -1) {
                        cell.style.display = 'none';
                    } else {
                        cell.style.display = '';
                    }
                }
            });
        });
    };

    // Strict single column button injection: guarantees EXACTLY ONE button per table
    function ensureColumnButton(tableEl) {
        var $table = $(tableEl);
        var $parentCard = $table.closest('.card, .table-card');
        var $container = $parentCard.length ? $parentCard : $table.closest('.dataTables_wrapper');

        // Check if button already exists in parent card / table container
        var existingBtns = $container.find('.btn-col-modal');
        if (existingBtns.length > 0) {
            // Keep only the first button, remove any accidental duplicates
            if (existingBtns.length > 1) {
                var $preferred = $container.find('.filter-bar .btn-col-modal');
                if ($preferred.length) {
                    existingBtns.not($preferred.first()).remove();
                } else {
                    existingBtns.slice(1).remove();
                }
            }
            return;
        }

        var tableId = $table.attr('id') || '';
        var selector = tableId ? '#' + tableId : 'table';
        var btnTitle = t('Customize columns');

        // Button HTML matching Image 1
        var btnHtml = 
            '<button type="button" class="btn-col-modal" data-target-table="' + selector + '" title="' + btnTitle + '" aria-label="' + btnTitle + '">' +
            '    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
            '        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>' +
            '        <line x1="9" y1="3" x2="9" y2="21"></line>' +
            '        <line x1="15" y1="3" x2="15" y2="21"></line>' +
            '    </svg>' +
            '</button>';

        // Try placing in filter bar first
        var $filterBar = $container.find('.filter-bar');
        if ($filterBar.length) {
            var $rightGroup = $filterBar.find('.ml-auto');
            if ($rightGroup.length) {
                $rightGroup.append(btnHtml);
            } else {
                $filterBar.append('<div class="ml-auto flex items-center gap-2.5">' + btnHtml + '</div>');
            }
            return;
        }

        // Otherwise place in DataTables top right
        var $dtTopRight = $container.find('.dt-top-right');
        if ($dtTopRight.length) {
            $dtTopRight.append(btnHtml);
        }
    }

    // Clean up all duplicate buttons across page
    function cleanDuplicateButtons() {
        $('.card, .table-card, .dataTables_wrapper').each(function () {
            var $btns = $(this).find('.btn-col-modal');
            if ($btns.length > 1) {
                var $fbBtn = $(this).find('.filter-bar .btn-col-modal');
                if ($fbBtn.length) {
                    $btns.not($fbBtn.first()).remove();
                } else {
                    $btns.slice(1).remove();
                }
            }
        });
    }

    // Global click listener for column customizer trigger button
    $(document).on('click', '.btn-col-modal, [data-col-customizer], .btn-colvis', function (e) {
        if ($(this).hasClass('btn-colvis') || $(this).closest('.btn-colvis').length) {
            e.preventDefault();
            e.stopPropagation();
            var dtTable = $(this).closest('.dataTables_wrapper').find('table.dataTable')[0];
            if (dtTable) {
                ACM.open(dtTable);
                return;
            }
        }

        e.preventDefault();
        var target = $(this).attr('data-target-table');
        ACM.open(target || $(this).closest('.table-card, .card').find('table')[0]);
    });

    // Listen to DataTables column-reorder event: saves new order when header is dragged
    $(document).on('column-reorder.dt', function (e, settings, details) {
        var dt = new $.fn.dataTable.Api(settings);
        var tableNode = dt.table().node();
        var currentOrder = dt.colReorder.order();
        var shown = currentOrder.filter(function (idx) { return dt.column(idx).visible(); });
        ACM.saveState(tableNode, {
            order: currentOrder,
            shown: shown
        });
    });

    // Auto-init on page load and DataTables init
    $(function () {
        ensureModalInjected();

        $('table').each(function () {
            ensureColumnButton(this);
            ACM.restoreTableState(this);
        });

        cleanDuplicateButtons();

        $(document).on('init.dt', function (e, settings) {
            var api = new $.fn.dataTable.Api(settings);
            var tableNode = api.table().node();
            ensureColumnButton(tableNode);
            ACM.restoreTableState(tableNode);
            cleanDuplicateButtons();
        });
    });

})(jQuery, window, document);
