/* ==========================================================================
   Fresh Express storefront runtime (requires jQuery, window.FX config from
   the layout). Exposes the legacy globals used by the views:
   addToCart, toggleWishlist, openCartDrawer, closeCartDrawer, loadDrawerContent,
   updateCartItem, removeCartItem, openQuickView, closeQuickView, toastr (shim).
   ========================================================================== */
(function ($, w, d) {
    'use strict';
    const FX = w.FX = Object.assign({ routes: {}, i18n: {}, freeThreshold: 499, auth: false }, w.FX || {});
    const t = (k, fb) => (FX.i18n && FX.i18n[k]) || fb || k;
    const money = n => '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    FX.money = money; FX.esc = esc;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });

    /* ---------------- Theme (light / dark / system) ---------------- */
    const mq = w.matchMedia ? w.matchMedia('(prefers-color-scheme: dark)') : null;
    function storedTheme() { try { return localStorage.getItem('theme') || 'system'; } catch (e) { return 'system'; } }
    function applyTheme(mode) {
        const dark = mode === 'dark' || (mode === 'system' && mq && mq.matches);
        d.documentElement.classList.toggle('dark', !!dark);
        const icon = mode === 'dark' ? 'ph-moon-stars' : (mode === 'light' ? 'ph-sun' : 'ph-desktop');
        $('[data-theme-icon]').each(function () { this.className = 'ph ' + icon + ' text-lg'; });
        $('[data-theme-set]').each(function () {
            const on = this.getAttribute('data-theme-set') === mode;
            this.classList.toggle('is-active', on);
            this.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        const meta = d.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', dark ? '#020617' : '#ffffff');
    }
    FX.setTheme = function (mode) { try { localStorage.setItem('theme', mode); } catch (e) {} applyTheme(mode); };
    if (mq && mq.addEventListener) mq.addEventListener('change', () => { if (storedTheme() === 'system') applyTheme('system'); });
    $(d).on('click', '[data-theme-set]', function () { FX.setTheme(this.getAttribute('data-theme-set')); closeDropdowns(); });

    /* ---------------- Toasts (+ toastr shim) ---------------- */
    const ICONS = { success: 'ph-fill ph-check-circle', error: 'ph-fill ph-warning-circle', warning: 'ph-fill ph-warning', info: 'ph-fill ph-info' };
    FX.toast = function (type, message, opts) {
        opts = opts || {};
        let box = d.getElementById('fxToasts');
        if (!box) { box = d.createElement('div'); box.id = 'fxToasts'; box.setAttribute('aria-live', 'polite'); d.body.appendChild(box); }
        const ms = opts.timeout || 3200;
        const el = d.createElement('div');
        el.className = 'fx-toast t-' + type;
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');
        el.innerHTML = '<i class="fx-toast-icon ' + (ICONS[type] || ICONS.info) + '"></i><div class="flex-1 min-w-0">' + esc(message) + '</div>' +
            (opts.action ? '<button type="button" class="fx-toast-action">' + esc(opts.action.label) + '</button>' : '') +
            '<button type="button" class="fx-toast-close text-slate-400 hover:text-white" aria-label="' + esc(t('close', 'Close')) + '"><i class="ph ph-x"></i></button>' +
            '<span class="fx-toast-bar t-' + type + '" style="animation-duration:' + ms + 'ms"></span>';
        const kill = () => { el.classList.add('is-leaving'); setTimeout(() => el.remove(), 200); };
        el.querySelector('.fx-toast-close').onclick = kill;
        if (opts.action) el.querySelector('.fx-toast-action').onclick = () => { kill(); opts.action.onClick(); };
        box.appendChild(el);
        while (box.children.length > 3) box.firstChild.remove();
        setTimeout(kill, ms);
        return el;
    };
    w.toastr = { success: m => FX.toast('success', m), error: m => FX.toast('error', m), warning: m => FX.toast('warning', m), info: m => FX.toast('info', m), options: {} };

    /* ---------------- Busy state for buttons ---------------- */
    FX.busy = function (btn, on) {
        if (!btn) return;
        btn = btn.jquery ? btn[0] : btn;
        if (!btn) return;
        btn.classList.toggle('is-loading', !!on);
        if (on) btn.setAttribute('aria-busy', 'true'); else btn.removeAttribute('aria-busy');
    };
    // Forms with [data-loading] show a spinner on their submit button
    $(d).on('submit', 'form[data-loading]', function () {
        const b = this.querySelector('button[type=submit]:not([formnovalidate])');
        if (b) setTimeout(() => FX.busy(b, true), 0);
    });

    /* ---------------- Overlays (drawers, sheets, modals) ---------------- */
    let openCount = 0;
    FX.open = function (id) {
        const el = d.getElementById(id);
        if (!el || el.classList.contains('is-open')) return;
        el.classList.remove('hidden');
        // force reflow so the transition runs
        void el.offsetWidth;
        el.classList.add('is-open');
        el.setAttribute('aria-hidden', 'false');
        openCount++; d.body.classList.add('fx-locked');
        const f = el.querySelector('[data-autofocus]') || el.querySelector('.fx-panel');
        if (f) setTimeout(() => f.focus({ preventScroll: true }), 50);
    };
    FX.close = function (id) {
        const el = d.getElementById(id);
        if (!el || !el.classList.contains('is-open')) return;
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
        openCount = Math.max(0, openCount - 1);
        if (!openCount) d.body.classList.remove('fx-locked');
    };
    $(d).on('click', '[data-open]', function (e) { e.preventDefault(); FX.open(this.getAttribute('data-open')); });
    $(d).on('click', '[data-close]', function (e) { e.preventDefault(); FX.close(this.getAttribute('data-close')); });
    $(d).on('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const open = d.querySelectorAll('.fx-overlay.is-open');
        if (open.length) FX.close(open[open.length - 1].id);
        closeDropdowns();
    });

    /* ---------------- Dropdowns ---------------- */
    function closeDropdowns(except) { $('.fx-dropdown.is-open').not(except).removeClass('is-open').find('[data-dropdown-toggle]').attr('aria-expanded', 'false'); }
    $(d).on('click', '[data-dropdown-toggle]', function (e) {
        e.preventDefault(); e.stopPropagation();
        const dd = $(this).closest('.fx-dropdown');
        closeDropdowns(dd);
        dd.toggleClass('is-open');
        $(this).attr('aria-expanded', dd.hasClass('is-open') ? 'true' : 'false');
    });
    $(d).on('click', function (e) { if (!$(e.target).closest('.fx-dropdown-menu').length) closeDropdowns(); });

    /* ---------------- Cart state ---------------- */
    FX.cart = {}; // product_id -> { id: cart_item_id, qty }
    function setBadges(count) {
        count = parseInt(count, 10) || 0;
        $('[data-cart-count]').each(function () {
            const changed = this.textContent.trim() !== String(count);
            this.textContent = count; this.setAttribute('data-count', count);
            if (changed) { this.classList.remove('fx-bump'); void this.offsetWidth; this.classList.add('fx-bump'); }
        });
        $('#globalCartBadge').text(count);
    }
    FX.setCartCount = setBadges;

    function renderCartControls() {
        $('[data-cart-control]').each(function () {
            const pid = String(this.getAttribute('data-product-id'));
            const entry = FX.cart[pid];
            const add = this.querySelector('[data-add]');
            const step = this.querySelector('[data-stepper]');
            if (!add || !step) return;
            if (entry && entry.qty > 0) {
                add.classList.add('hidden'); step.classList.remove('hidden'); step.classList.add('flex');
                step.querySelector('.fx-stepper-qty').textContent = entry.qty;
            } else {
                step.classList.add('hidden'); step.classList.remove('flex'); add.classList.remove('hidden');
            }
        });
    }

    function syncCart(data) {
        FX.cart = {};
        (data.items || []).forEach(it => { FX.cart[String(it.product_id)] = { id: it.id, qty: it.quantity }; });
        setBadges(data.count);
        renderCartControls();
    }

    let cartReq = null;
    FX.refreshCart = function () {
        if (cartReq) cartReq.abort();
        cartReq = $.get(FX.routes.cartDrawer).done(function (data) { syncCart(data); renderDrawer(data); }).always(() => { cartReq = null; });
        return cartReq;
    };

    function drawerSkeleton() {
        let h = '';
        for (let i = 0; i < 3; i++) h += '<div class="flex gap-3 p-3"><div class="fx-skeleton w-16 h-16 shrink-0 rounded-xl"></div><div class="flex-1 space-y-2 py-1"><div class="fx-skeleton h-3 w-3/4"></div><div class="fx-skeleton h-3 w-1/3"></div><div class="fx-skeleton h-6 w-24"></div></div></div>';
        $('#drawerItemsList').html(h);
    }

    function renderDrawer(data) {
        const list = $('#drawerItemsList');
        if (!list.length) return;
        const count = parseInt(data.count, 10) || 0;
        $('#drawerItemCount').text(count + ' ' + (count === 1 ? t('item', 'item') : t('items', 'items')));
        $('#drawerSubtotal').text(data.formatted_subtotal || money(data.subtotal));
        if (data.delivery_slot) $('#drawerDeliverySlot').text(data.delivery_slot);

        // free-delivery meter
        const sub = Number(data.subtotal || 0), need = Math.max(0, FX.freeThreshold - sub);
        const pct = Math.min(100, Math.round(sub / FX.freeThreshold * 100));
        $('#drawerFreeBar').css('width', pct + '%');
        $('#drawerFreeText').html(need > 0
            ? t('add_more_for_free', 'Add :amount more for FREE delivery').replace(':amount', '<strong>' + money(need) + '</strong>')
            : '<strong>' + esc(t('free_unlocked', 'You unlocked FREE delivery!')) + '</strong>');
        $('#drawerFooter').toggleClass('hidden', !data.items || !data.items.length);
        $('#drawerMeter').toggleClass('hidden', !data.items || !data.items.length);

        list.empty();
        if (!data.items || !data.items.length) {
            list.html('<div class="text-center py-16 px-6">' +
                '<div class="w-20 h-20 mx-auto rounded-full bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-4xl mb-4"><i class="ph-duotone ph-basket"></i></div>' +
                '<p class="font-extrabold text-slate-900 dark:text-white">' + esc(t('cart_empty', 'Your cart is empty')) + '</p>' +
                '<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">' + esc(t('cart_empty_sub', 'Fresh fruits, veggies and daily essentials are a tap away.')) + '</p>' +
                '<a href="' + FX.routes.products + '" class="fx-btn fx-btn-primary mt-5"><span>' + esc(t('start_shopping', 'Start shopping')) + '</span><i class="ph-bold ph-arrow-right"></i></a></div>');
            return;
        }
        data.items.forEach(function (item) {
            list.append(
                '<div class="flex gap-3 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" data-drawer-item="' + item.id + '">' +
                '<img src="' + esc(item.thumbnail) + '" alt="" class="w-16 h-16 rounded-xl object-cover bg-slate-100 dark:bg-slate-800 shrink-0">' +
                '<div class="flex-1 min-w-0">' +
                '<div class="flex items-start justify-between gap-2"><h5 class="text-[13px] font-bold text-slate-900 dark:text-white leading-snug line-clamp-2">' + esc(item.name) + '</h5>' +
                '<button type="button" onclick="removeCartItem(' + item.id + ', this)" class="relative shrink-0 w-7 h-7 -mt-1 -mr-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 flex items-center justify-center" aria-label="' + esc(t('remove', 'Remove')) + '"><i class="ph ph-trash"></i></button></div>' +
                '<p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">' + esc(item.unit) + ' · ' + money(item.price) + '</p>' +
                '<div class="flex items-center justify-between mt-2">' +
                '<div class="fx-stepper !h-8 w-[6.5rem]">' +
                '<button type="button" onclick="updateCartItem(' + item.id + ', ' + (item.quantity - 1) + ', this)" aria-label="-"><i class="ph-bold ph-minus text-xs"></i></button>' +
                '<span class="fx-stepper-qty">' + item.quantity + '</span>' +
                '<button type="button" onclick="updateCartItem(' + item.id + ', ' + (item.quantity + 1) + ', this)" aria-label="+"><i class="ph-bold ph-plus text-xs"></i></button></div>' +
                '<span class="text-sm font-extrabold text-slate-900 dark:text-white">' + money(item.subtotal) + '</span></div></div></div>');
        });
    }

    w.loadDrawerContent = function () { return FX.refreshCart(); };
    w.openCartDrawer = function () { FX.open('cartDrawer'); if (!$('#drawerItemsList').children().length) drawerSkeleton(); FX.refreshCart(); };
    w.closeCartDrawer = function () { FX.close('cartDrawer'); };

    w.addToCart = function (productId, quantity, options) {
        quantity = parseInt(quantity, 10) || 1;
        options = options || {};
        const btn = options.button || (w.event && w.event.currentTarget && w.event.currentTarget.tagName === 'BUTTON' ? w.event.currentTarget : null);
        FX.busy(btn, true);
        return $.ajax({ url: FX.routes.cartAdd, type: 'POST', data: { product_id: productId, quantity: quantity } })
            .done(function (res) {
                if (!res.success) return;
                setBadges(res.cart_count);
                if (options.openDrawer === false) {
                    FX.toast('success', res.item_name ? res.item_name + ' — ' + res.message : res.message, { action: { label: t('view_cart', 'View cart'), onClick: w.openCartDrawer } });
                    FX.refreshCart();
                } else {
                    FX.toast('success', res.message);
                    w.openCartDrawer();
                }
                if (typeof options.onSuccess === 'function') options.onSuccess(res);
            })
            .fail(function (xhr) { FX.toast('error', (xhr.responseJSON && xhr.responseJSON.message) || t('cart_error', 'Could not add to cart')); })
            .always(function () { FX.busy(btn, false); });
    };

    w.updateCartItem = function (itemId, qty, btn) {
        btn = btn || (w.event && w.event.currentTarget && w.event.currentTarget.tagName === 'BUTTON' ? w.event.currentTarget : null);
        FX.busy(btn, true);
        return $.ajax({ url: FX.routes.cartUpdate.replace('__ID__', itemId), type: 'PATCH', data: { quantity: Math.max(0, qty) } })
            .done(function (res) {
                if (!res.success) return;
                setBadges(res.cart_count);
                $(d).trigger('fx:cart-changed', [res, itemId, qty]);
                if (qty <= 0) FX.toast('info', res.message);
                FX.refreshCart();
            })
            .fail(function (xhr) { FX.toast('error', (xhr.responseJSON && xhr.responseJSON.message) || t('cart_update_error', 'Could not update item')); })
            .always(function () { FX.busy(btn, false); });
    };

    w.removeCartItem = function (itemId, btn) {
        btn = btn || (w.event && w.event.currentTarget && w.event.currentTarget.tagName === 'BUTTON' ? w.event.currentTarget : null);
        FX.busy(btn, true);
        return $.ajax({ url: FX.routes.cartRemove.replace('__ID__', itemId), type: 'DELETE' })
            .done(function (res) {
                if (!res.success) return;
                setBadges(res.cart_count);
                FX.toast('info', res.message);
                $(d).trigger('fx:cart-changed', [res, itemId, 0]);
                FX.refreshCart();
            })
            .fail(function (xhr) { FX.toast('error', (xhr.responseJSON && xhr.responseJSON.message) || t('cart_update_error', 'Could not update item')); })
            .always(function () { FX.busy(btn, false); });
    };

    // Product-card controls: add / + / −
    $(d).on('click', '[data-cart-control] [data-add]', function (e) {
        e.preventDefault();
        const pid = $(this).closest('[data-cart-control]').data('product-id');
        w.addToCart(pid, 1, { button: this, openDrawer: false });
    });
    $(d).on('click', '[data-cart-control] [data-step]', function (e) {
        e.preventDefault();
        const ctl = $(this).closest('[data-cart-control]');
        const pid = String(ctl.data('product-id'));
        const entry = FX.cart[pid];
        const delta = parseInt(this.getAttribute('data-step'), 10);
        const max = parseInt(ctl.data('stock'), 10) || 9999;
        if (!entry) { w.addToCart(pid, 1, { button: this, openDrawer: false }); return; }
        const next = entry.qty + delta;
        if (next > max) { FX.toast('warning', t('max_stock', 'Only :n available in stock').replace(':n', max)); return; }
        // optimistic UI
        entry.qty = next; renderCartControls();
        w.updateCartItem(entry.id, next, this);
    });

    /* ---------------- Wishlist ---------------- */
    w.toggleWishlist = function (productId, btn) {
        btn = btn || null;
        FX.busy(btn, true);
        return $.ajax({ url: FX.routes.wishlistToggle, type: 'POST', data: { product_id: productId } })
            .done(function (res) {
                if (!res.success) return;
                // reflect state on every heart for this product on the page
                const hearts = $('[data-wishlist-btn="' + productId + '"]').add(btn).find('i');
                hearts.toggleClass('heart-active text-rose-600', !!res.in_wishlist).toggleClass('ph-fill', !!res.in_wishlist).toggleClass('ph', !res.in_wishlist).addClass('ph-heart');
                hearts.removeClass('heart-pop'); void d.body.offsetWidth; hearts.addClass('heart-pop');
                $('[data-wishlist-btn="' + productId + '"]').add(btn).attr('aria-pressed', res.in_wishlist ? 'true' : 'false');
                FX.toast(res.in_wishlist ? 'success' : 'info', res.message);
                if (res.wishlist_count !== undefined) {
                    $('[data-wishlist-count]').text(res.wishlist_count).attr('data-count', res.wishlist_count);
                    $('#globalWishlistBadge').text(res.wishlist_count);
                }
                $(d).trigger('fx:wishlist-changed', [productId, res]);
            })
            .fail(function (xhr) {
                if (xhr.status === 401) { FX.toast('info', t('login_for_wishlist', 'Please login to save items')); setTimeout(() => { w.location.href = FX.routes.login; }, 700); }
                else FX.toast('error', t('wishlist_error', 'Could not update wishlist'));
            })
            .always(function () { FX.busy(btn, false); });
    };

    /* ---------------- Quick view ---------------- */
    let activeQvProductId = null;
    w.openQuickView = function (productId, url) {
        activeQvProductId = productId;
        $('#qvBody').addClass('hidden'); $('#qvSkeleton').removeClass('hidden');
        FX.open('quickViewModal');
        $.get(FX.routes.quickView.replace('__ID__', productId)).done(function (p) {
            $('#qvImage').attr('src', p.thumbnail).attr('alt', p.name);
            $('#qvCategory').text(p.category || '');
            $('#qvTitle').text(p.name);
            $('#qvUnit').text(p.unit || '');
            $('#qvPrice').text(money(p.effective_price));
            if (p.discount_price) { $('#qvMrp').text(money(p.price)); $('#qvDiscount').text(p.discount_percent + '% ' + t('off', 'OFF')).removeClass('hidden'); }
            else { $('#qvMrp').text(''); $('#qvDiscount').text('').addClass('hidden'); }
            $('#qvDescription').text(p.short_desc || '');
            $('#qvLink').attr('href', url || '#').toggleClass('hidden', !url);
            $('#qvQty').val(1);
            $('#qvSkeleton').addClass('hidden'); $('#qvBody').removeClass('hidden');
        }).fail(function () { FX.close('quickViewModal'); FX.toast('error', t('generic_error', 'Something went wrong')); });
    };
    w.closeQuickView = function () { FX.close('quickViewModal'); };
    $(d).on('click', '#qvAddToCartBtn', function () {
        if (!activeQvProductId) return;
        const qty = parseInt($('#qvQty').val(), 10) || 1;
        w.addToCart(activeQvProductId, qty, { button: this, onSuccess: () => FX.close('quickViewModal') });
    });
    $(d).on('click', '[data-qv-step]', function () {
        const i = $('#qvQty'); i.val(Math.max(1, (parseInt(i.val(), 10) || 1) + parseInt(this.getAttribute('data-qv-step'), 10)));
    });

    /* ---------------- Copy coupon code ---------------- */
    FX.copy = function (code, btn) {
        const done = () => {
            FX.toast('success', t('code_copied', 'Code :code copied').replace(':code', code));
            if (btn) { const lbl = btn.querySelector('[data-copy-label]'); if (lbl) { const o = lbl.textContent; lbl.textContent = t('copied', 'Copied!'); setTimeout(() => { lbl.textContent = o; }, 1600); } }
        };
        if (navigator.clipboard && w.isSecureContext) navigator.clipboard.writeText(code).then(done).catch(() => fallback());
        else fallback();
        function fallback() {
            const ta = d.createElement('textarea'); ta.value = code; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
            d.body.appendChild(ta); ta.select();
            try { d.execCommand('copy'); done(); } catch (e) { FX.toast('info', code); }
            ta.remove();
        }
    };
    $(d).on('click', '[data-copy]', function () { FX.copy(this.getAttribute('data-copy'), this); });

    /* ---------------- Delete confirmation (forms) ---------------- */
    $(d).on('click', '.confirm-delete-btn', function (e) {
        e.preventDefault();
        const form = $(this).closest('form')[0];
        const title = this.getAttribute('data-confirm-title') || t('confirm_delete', 'Delete this item?');
        const text = this.getAttribute('data-confirm-text') || t('confirm_delete_text', 'This action cannot be undone.');
        const go = () => { FX.busy(this, true); form.submit(); };
        if (w.Swal && w.Swal.fire) {
            const dark = d.documentElement.classList.contains('dark');
            w.Swal.fire({
                title: title, text: text, icon: 'warning', showCancelButton: true, reverseButtons: true,
                confirmButtonText: t('delete', 'Delete'), cancelButtonText: t('cancel', 'Cancel'),
                confirmButtonColor: '#e11d48', cancelButtonColor: dark ? '#334155' : '#94a3b8',
                background: dark ? '#0f172a' : '#fff', color: dark ? '#e2e8f0' : '#0f172a'
            }).then(r => { if (r.isConfirmed) go(); });
        } else if (w.confirm(title)) go();
    });

    /* ---------------- Recently viewed (per-browser convenience) ---------------- */
    const RV_KEY = 'fx_recently_viewed';
    FX.recent = {
        read() { try { return JSON.parse(localStorage.getItem(RV_KEY) || '[]') || []; } catch (e) { return []; } },
        push(p) {
            try {
                let list = this.read().filter(x => String(x.id) !== String(p.id));
                list.unshift(p); list = list.slice(0, 12);
                localStorage.setItem(RV_KEY, JSON.stringify(list));
            } catch (e) {}
        },
        clear() { try { localStorage.removeItem(RV_KEY); } catch (e) {} }
    };
    function renderRecent() {
        $('[data-recently-viewed]').each(function () {
            const box = $(this), exclude = String(box.data('exclude') || '');
            const items = FX.recent.read().filter(x => String(x.id) !== exclude).slice(0, 10);
            if (!items.length) { box.addClass('hidden'); return; }
            const rail = box.find('[data-rv-rail]').empty();
            items.forEach(p => rail.append(
                '<a href="' + esc(p.url) + '" class="group fx-card overflow-hidden flex flex-col hover:border-brand-400 dark:hover:border-brand-600 transition-colors">' +
                '<div class="aspect-square bg-slate-100 dark:bg-slate-800 overflow-hidden"><img src="' + esc(p.img) + '" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"></div>' +
                '<div class="p-3"><p class="text-[13px] font-bold text-slate-900 dark:text-white line-clamp-1">' + esc(p.name) + '</p>' +
                '<p class="text-[11px] text-slate-500 dark:text-slate-400">' + esc(p.unit || '') + '</p>' +
                '<p class="text-sm font-extrabold text-slate-900 dark:text-white mt-1">' + esc(p.price) + '</p></div></a>'));
            box.removeClass('hidden');
        });
    }
    $(d).on('click', '[data-rv-clear]', function () { FX.recent.clear(); $('[data-recently-viewed]').addClass('hidden'); });

    /* ---------------- Hero slider (no dependency) ---------------- */
    function initSliders() {
        $('[data-slider]').each(function () {
            const root = this, track = root.querySelector('.fx-slider-track');
            if (!track) return;
            const slides = track.children, dots = root.querySelectorAll('[data-dot]');
            let idx = 0, timer = null;
            const go = i => { idx = (i + slides.length) % slides.length; track.scrollTo({ left: idx * track.clientWidth, behavior: 'smooth' }); };
            const mark = () => { const i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth)); idx = i; dots.forEach((dt, k) => dt.classList.toggle('is-active', k === i)); };
            track.addEventListener('scroll', () => { w.requestAnimationFrame(mark); }, { passive: true });
            dots.forEach((dt, k) => dt.addEventListener('click', () => { go(k); restart(); }));
            const prev = root.querySelector('[data-prev]'), next = root.querySelector('[data-next]');
            if (prev) prev.addEventListener('click', () => { go(idx - 1); restart(); });
            if (next) next.addEventListener('click', () => { go(idx + 1); restart(); });
            const restart = () => { clearInterval(timer); if (slides.length > 1) timer = setInterval(() => { if (!d.hidden) go(idx + 1); }, 5000); };
            root.addEventListener('mouseenter', () => clearInterval(timer));
            root.addEventListener('mouseleave', restart);
            mark(); restart();
        });
    }

    /* ---------------- Footer accordion ---------------- */
    $(d).on('click', '.fx-acc-toggle', function () {
        const acc = $(this).closest('.fx-acc').toggleClass('is-open');
        $(this).attr('aria-expanded', acc.hasClass('is-open') ? 'true' : 'false');
    });

    /* ---------------- Header shadow + back to top ---------------- */
    function onScroll() {
        const y = w.scrollY || 0;
        $('#fxHeader').toggleClass('is-scrolled', y > 8);
        const btt = d.getElementById('fxBackToTop');
        if (btt) { const show = y > 600; btt.classList.toggle('opacity-0', !show); btt.classList.toggle('pointer-events-none', !show); btt.classList.toggle('translate-y-3', !show); }
    }
    w.addEventListener('scroll', onScroll, { passive: true });
    $(d).on('click', '#fxBackToTop', () => w.scrollTo({ top: 0, behavior: 'smooth' }));

    /* ---------------- Password show / hide ---------------- */
    $(d).on('click', '[data-toggle-password]', function () {
        const input = d.getElementById(this.getAttribute('data-toggle-password'));
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        const i = this.querySelector('i'); if (i) i.className = 'ph ' + (show ? 'ph-eye-slash' : 'ph-eye') + ' text-lg';
        this.setAttribute('aria-pressed', show ? 'true' : 'false');
    });

    /* ---------------- Priority navigation: category links that don't fit move into "More" ---------------- */
    function fitPrioNav() {
        const nav = d.querySelector('[data-prio-nav]');
        if (!nav || !nav.offsetParent) return; // hidden below lg
        const items = Array.from(nav.querySelectorAll('[data-prio-item]'));
        const extra = d.querySelector('[data-prio-extra]');
        const row = nav.parentElement;
        const overflowing = () => nav.scrollWidth > nav.clientWidth + 1 || row.scrollWidth > row.clientWidth + 1;
        items.forEach(el => { el.hidden = false; });
        if (extra) extra.hidden = true;
        let hiddenCount = 0;
        for (let i = items.length - 1; i >= 0 && overflowing(); i--) { items[i].hidden = true; hiddenCount++; }
        // the cut-off note is a nice-to-have: show it only when every menu already fits
        if (extra && !hiddenCount) { extra.hidden = false; if (overflowing()) extra.hidden = true; }
        d.querySelectorAll('[data-prio-more-item]').forEach(el => {
            const src = items[+el.getAttribute('data-prio-more-item')];
            el.hidden = !(src && src.hidden);
        });
        const sec = d.querySelector('[data-prio-more-section]');
        if (sec) sec.hidden = !hiddenCount;
    }
    FX.fitNav = fitPrioNav;
    let fitRaf = 0;
    w.addEventListener('resize', () => { cancelAnimationFrame(fitRaf); fitRaf = requestAnimationFrame(fitPrioNav); });
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(fitPrioNav);

    /* ---------------- In-page jumps from the off-canvas menu (e.g. Contact & Help → footer) ---------------- */
    function revealTarget(sel) {
        const t = sel && sel.charAt(0) === '#' ? d.querySelector(sel) : null;
        if (!t) return;
        if (t.classList.contains('fx-acc')) { t.classList.add('is-open'); $(t).find('.fx-acc-toggle').attr('aria-expanded', 'true'); }
        t.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    $(d).on('click', '[data-scroll-to]', function () { const sel = this.getAttribute('data-scroll-to'); setTimeout(() => revealTarget(sel), 300); });
    $(d).on('click', 'a[href="#fxSupport"]:not([data-scroll-to])', function (e) { e.preventDefault(); closeDropdowns(); revealTarget('#fxSupport'); });

    /* ---------------- Themed selects: localise the search box placeholder ---------------- */
    function localiseSelects(root) {
        if (!FX.i18n.select_search) return;
        $(root || d).find('select:not([data-search-placeholder])').attr('data-search-placeholder', FX.i18n.select_search);
    }

    /* ---------------- Boot ---------------- */
    $(function () {
        applyTheme(storedTheme());
        fitPrioNav();
        localiseSelects();
        if (w.location.hash === '#fxSupport') revealTarget('#fxSupport');
        onScroll();
        initSliders();
        renderRecent();
        // flash messages from the server → toasts
        (FX.flash || []).forEach(f => FX.toast(f.type, f.message, { timeout: 4500 }));
        // sync card steppers + badges only if the page has cart controls
        if ($('[data-cart-control]').length) FX.refreshCart();
    });
})(jQuery, window, document);
