/* ==========================================================================
   OTP verification screen behaviour (frontend/auth/_otp-verify.blade.php)
   - one digit per box: auto-advance, backspace to previous, arrows, paste / SMS autofill
   - submits over fetch (JSON) so it can play the error shake / success check;
     falls back to a normal form post when fetch is unavailable
   - resend countdown ring driven by the server cool-down
   ========================================================================== */
(function () {
    'use strict';

    var root = document.querySelector('[data-otp]');
    if (!root) return;

    var len = parseInt(root.getAttribute('data-length'), 10) || 4;
    var i18n = {};
    try { i18n = JSON.parse(root.getAttribute('data-i18n') || '{}'); } catch (e) {}
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var form = root.querySelector('.fx-otp-form');
    var hidden = root.querySelector('[data-otp-value]');
    var boxes = Array.prototype.slice.call(root.querySelectorAll('[data-otp-box]'));
    var msg = root.querySelector('[data-otp-msg]');
    var notice = root.querySelector('[data-otp-notice]');
    var submit = root.querySelector('[data-otp-submit]');
    var submitText = root.querySelector('[data-otp-submit-text]');
    var submitLabel = submitText ? submitText.textContent : '';
    var busy = false, done = false;
    var csrf = (form.querySelector('input[name=_token]') || {}).value
        || (document.querySelector('meta[name=csrf-token]') || {}).content || '';

    setTimeout(function () { root.classList.add('is-ready'); }, reduced ? 0 : 1400);

    // ------------------------------------------------------------ boxes
    function code() { return boxes.map(function (b) { return b.value; }).join(''); }

    function setBox(b, v) {
        b.value = v;
        b.setAttribute('data-prev', v);
        b.classList.toggle('is-filled', v !== '');
        if (v !== '' && !reduced) {
            b.classList.remove('is-bump'); void b.offsetWidth; b.classList.add('is-bump');
        }
    }

    function clearError() {
        if (root.classList.contains('is-error')) root.classList.remove('is-error');
        if (msg) msg.textContent = '';
    }

    function fill(from, digits) {
        var i = from;
        digits.split('').forEach(function (d) { if (i < len) setBox(boxes[i++], d); });
        var next = boxes[Math.min(i, len - 1)];
        next.focus();
        maybeSubmit();
    }

    function maybeSubmit() {
        if (code().length === len && !busy && !done) {
            setTimeout(function () { if (code().length === len) send(); }, reduced ? 0 : 180);
        }
    }

    boxes.forEach(function (b, idx) {
        b.setAttribute('data-prev', '');
        function advance() { if (idx < len - 1) boxes[idx + 1].focus(); }

        // Hardware keyboards: handle the digit here so an already-filled box is simply replaced.
        b.addEventListener('keydown', function (e) {
            if (/^\d$/.test(e.key) && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                clearError();
                setBox(b, e.key); b.setAttribute('data-prev', e.key);
                advance();
                maybeSubmit();
            } else if (e.key === 'Backspace') {
                clearError();
                if (b.value === '' && idx > 0) { e.preventDefault(); setBox(boxes[idx - 1], ''); boxes[idx - 1].focus(); }
                else { e.preventDefault(); setBox(b, ''); }
                b.setAttribute('data-prev', b.value);
            } else if (e.key === 'ArrowLeft' && idx > 0) { e.preventDefault(); boxes[idx - 1].focus(); }
            else if (e.key === 'ArrowRight' && idx < len - 1) { e.preventDefault(); boxes[idx + 1].focus(); }
            else if (e.key === 'Enter') { e.preventDefault(); send(); }
            else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey) { e.preventDefault(); } // letters, spaces...
        });

        // Soft keyboards, SMS autofill (autocomplete=one-time-code) and IME input arrive here.
        b.addEventListener('input', function () {
            clearError();
            var prev = b.getAttribute('data-prev') || '';
            var digits = b.value.replace(/\D/g, '');
            if (digits.length === 2 && prev && digits.indexOf(prev) !== -1) {
                digits = digits.charAt(0) === prev ? digits.charAt(1) : digits.charAt(0); // typed over an existing digit
            }
            if (digits.length > 1) { setBox(b, ''); fill(idx, digits); return; }
            setBox(b, digits); b.setAttribute('data-prev', digits);
            if (digits) advance();
            maybeSubmit();
        });

        b.addEventListener('focus', function () {
            requestAnimationFrame(function () { if (document.activeElement === b) { try { b.setSelectionRange(0, b.value.length); } catch (e) {} } });
        });
        b.addEventListener('paste', function (e) {
            var t = (e.clipboardData || window.clipboardData).getData('text') || '';
            var digits = t.replace(/\D/g, '').slice(0, len);
            e.preventDefault();
            if (!digits) return;
            clearError();
            boxes.forEach(function (x) { setBox(x, ''); });
            fill(0, digits);
        });
    });

    // Demo hint chip fills the code
    var demo = root.querySelector('[data-otp-fill]');
    if (demo) demo.addEventListener('click', function () {
        clearError();
        boxes.forEach(function (x) { setBox(x, ''); });
        fill(0, demo.getAttribute('data-otp-fill'));
    });

    // ------------------------------------------------------------ states
    function showError(text) {
        busy = false;
        setBusy(false);
        if (msg) msg.textContent = text || '';
        if (notice) notice.classList.add('hidden');
        root.classList.remove('is-error'); void root.offsetWidth; root.classList.add('is-error');
        if (navigator.vibrate) { try { navigator.vibrate([40, 40, 40]); } catch (e) {} }
        setTimeout(function () {
            boxes.forEach(function (x) { setBox(x, ''); });
            boxes[0].focus();
        }, reduced ? 0 : 650);
    }

    function showNotice(text) {
        if (!notice || !text) return;
        notice.querySelector('span').textContent = text;
        notice.classList.remove('hidden');
        notice.style.animation = 'none'; void notice.offsetWidth; notice.style.animation = '';
    }

    function setBusy(on) {
        if (!submit) return;
        submit.classList.toggle('is-loading', !!on);
        submit.disabled = !!on;
        if (submitText) submitText.textContent = on ? (i18n.verifying || submitLabel) : submitLabel;
    }

    function success(redirect) {
        done = true;
        busy = false;
        submit.classList.remove('is-loading');
        submit.disabled = true;
        if (submitText) submitText.textContent = i18n.verified || submitLabel;
        var icon = submit.querySelector('i'); if (icon) icon.className = 'ph-bold ph-check-circle';
        boxes.forEach(function (x) { try { x.setSelectionRange(0, 0); } catch (e) {} x.blur(); x.readOnly = true; });
        if (notice && i18n.redirecting) {
            notice.querySelector('i').className = 'ph-fill ph-check-circle';
            showNotice(i18n.redirecting);
        }
        if (msg) msg.textContent = '';
        root.classList.remove('is-error');
        root.classList.add('is-success');
        setTimeout(function () { window.location.assign(redirect || '/'); }, reduced ? 300 : 1300);
    }

    // ------------------------------------------------------------ submit
    function send() {
        if (busy || done) return;
        var c = code();
        if (c.length !== len) { showError(i18n.incomplete); return; }
        hidden.value = c;

        if (!window.fetch || !window.FormData) { form.submit(); return; }

        busy = true;
        setBusy(true);
        if (msg) msg.textContent = '';

        var fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { return { status: r.status, body: j }; });
        }).then(function (res) {
            var j = res.body || {};
            if (res.status >= 200 && res.status < 300 && j.success) return success(j.redirect);
            if (res.status === 419 && !j.message) { window.location.reload(); return; } // CSRF token expired
            showError(j.message || i18n.network);
            if (j.redirect && (res.status === 419 || res.status === 404 || res.status === 403)) {
                setTimeout(function () { window.location.assign(j.redirect); }, 1600);
            }
            if (j.code_gone) enableResendNow();
        }).catch(function () {
            showError(i18n.network);
        });
    }

    form.addEventListener('submit', function (e) { e.preventDefault(); send(); });

    // ------------------------------------------------------------ resend countdown
    var resendForm = root.querySelector('[data-otp-resend-form]');
    var resendBtn = root.querySelector('[data-otp-resend]');
    var countdown = root.querySelector('[data-otp-countdown]');
    var ready = root.querySelector('[data-otp-ready]');
    var ring = root.querySelector('[data-otp-ring]');
    var timer = root.querySelector('[data-otp-timer]');
    var total = parseInt(root.getAttribute('data-resend-total'), 10) || 30;
    var CIRC = 56.55, endAt = 0, tickId = null;

    function fmt(s) { var m = Math.floor(s / 60); return m + ':' + String(s % 60).padStart(2, '0'); }

    function tick() {
        var left = Math.max(0, Math.ceil((endAt - Date.now()) / 1000));
        if (timer) timer.textContent = fmt(left);
        if (ring) ring.style.strokeDashoffset = (CIRC * (1 - left / total)).toFixed(2);
        if (left <= 0) { enableResendNow(); return; }
        tickId = setTimeout(tick, 250);
    }

    function startCountdown(seconds) {
        clearTimeout(tickId);
        seconds = Math.max(0, parseInt(seconds, 10) || 0);
        if (!seconds) { enableResendNow(); return; }
        total = Math.max(total, seconds);
        endAt = Date.now() + seconds * 1000;
        resendBtn.disabled = true;
        countdown.hidden = false;
        ready.hidden = true;
        if (ring) { ring.style.transition = 'none'; ring.style.strokeDashoffset = (CIRC * (1 - seconds / total)).toFixed(2); void ring.offsetWidth; ring.style.transition = ''; }
        tick();
    }

    function enableResendNow() {
        clearTimeout(tickId);
        resendBtn.disabled = false;
        resendBtn.classList.remove('is-spinning');
        countdown.hidden = true;
        ready.hidden = false;
    }

    startCountdown(root.getAttribute('data-resend-in'));

    resendForm.addEventListener('submit', function (e) {
        if (!window.fetch) return; // plain post fallback
        e.preventDefault();
        if (resendBtn.disabled) return;
        resendBtn.disabled = true;
        resendBtn.classList.add('is-spinning');
        fetch(resendForm.action, {
            method: 'POST',
            body: new FormData(resendForm),
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { return { status: r.status, body: j }; });
        }).then(function (res) {
            var j = res.body || {};
            resendBtn.classList.remove('is-spinning');
            if (res.status === 419 && j.redirect) { window.location.assign(j.redirect); return; }
            if (j.success) {
                clearError();
                boxes.forEach(function (x) { setBox(x, ''); });
                boxes[0].focus();
                showNotice(j.message);
                startCountdown(j.resend_in || total);
            } else {
                if (msg) msg.textContent = j.message || i18n.network;
                startCountdown(j.retry_after || 0);
            }
        }).catch(function () {
            resendBtn.classList.remove('is-spinning');
            if (msg) msg.textContent = i18n.network;
            enableResendNow();
        });
    });

    // ------------------------------------------------------------ server-side error on load
    var initialError = root.getAttribute('data-error');
    if (initialError) {
        setTimeout(function () {
            root.classList.remove('is-error'); void root.offsetWidth; root.classList.add('is-error');
        }, reduced ? 0 : 900);
    }
})();
