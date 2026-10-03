@php
    $orientation = request('orientation') === 'landscape' ? 'landscape' : 'portrait';
    $pdfName = 'Invoice-' . ($order->invoice_number ?: $order->order_number);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->invoice_number }} · FreshExpress Grocery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Vadodara:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

    {{-- Swapped by setOrientation() before window.print() --}}
    <style id="pageSizeStyle">@page { size: A4 {{ $orientation }}; margin: 10mm; }</style>

    @include('admin.invoices.partials.styles')
    <style>
        html, body { margin: 0; padding: 0; }
        body { background: #e2e8f0; font-family: 'Plus Jakarta Sans', 'Hind Vadodara', ui-sans-serif, system-ui, sans-serif; color: #0f172a; }
        .pt-bar { position: sticky; top: 0; z-index: 10; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px 16px; background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border-bottom: 1px solid #cbd5e1; }
        .pt-title { font-size: 13px; font-weight: 800; margin-right: auto; display: flex; align-items: center; gap: 8px; min-width: 0; }
        .pt-title small { font-weight: 600; color: #64748b; }
        .pt-btn { white-space: nowrap; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px; border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-family: inherit; font-weight: 700; font-size: 12.5px; line-height: 1; text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s; }
        .pt-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
        .pt-btn.is-primary { background: #0f172a; border-color: #0f172a; color: #fff; }
        .pt-btn.is-primary:hover { background: #1e293b; }
        .pt-btn i { font-size: 16px; }
        .pt-seg { display: inline-flex; padding: 3px; border-radius: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; }
        .pt-seg button { display: inline-flex; align-items: center; gap: 5px; height: 28px; padding: 0 11px; border: 0; border-radius: 8px; background: transparent; color: #64748b; font-family: inherit; font-weight: 700; font-size: 12px; line-height: 1; cursor: pointer; }
        .pt-seg button.is-selected { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15,23,42,.14); }
        .pt-hint { width: 100%; font-size: 11.5px; color: #64748b; margin: 0; }
        .pt-stage { padding: 24px 16px 40px; overflow-x: auto; }
        .pt-stage .inv-sheet { box-shadow: 0 18px 40px -18px rgba(15,23,42,.35); border-radius: 4px; }
        .rot-90 { display: inline-block; transform: rotate(90deg); }
        @media (max-width: 640px) {
            .pt-bar { padding: 10px 12px; }
            .pt-title { width: 100%; }
            .pt-actions { display: flex; gap: 8px; width: 100%; }
            .pt-actions .pt-btn { flex: 1 1 0; padding: 0 8px; }
            .pt-stage { padding: 12px 8px 24px; }
        }
        @media (min-width: 641px) { .pt-actions { display: flex; gap: 8px; } }

        @media print {
            html, body { background: #fff !important; }
            .no-print { display: none !important; }
            .pt-stage { padding: 0 !important; overflow: visible !important; }
            .pt-stage .inv-sheet { width: auto !important; min-height: 0 !important; max-width: none; margin: 0; padding: 0; box-shadow: none !important; border: 0 !important; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="pt-bar no-print">
        <div class="pt-title">
            <span>Invoice {{ $order->invoice_number }}</span>
            <small>Order {{ $order->order_number }}</small>
        </div>
        <div class="pt-seg" role="radiogroup" aria-label="Page orientation">
            <button type="button" data-orient="portrait" role="radio" class="{{ $orientation === 'portrait' ? 'is-selected' : '' }}" aria-checked="{{ $orientation === 'portrait' ? 'true' : 'false' }}"><i class="ph ph-file"></i> Portrait</button>
            <button type="button" data-orient="landscape" role="radio" class="{{ $orientation === 'landscape' ? 'is-selected' : '' }}" aria-checked="{{ $orientation === 'landscape' ? 'true' : 'false' }}"><i class="ph ph-file rot-90"></i> Landscape</button>
        </div>
        <div class="pt-actions">
            <a href="{{ route('admin.invoices.show', $order) }}" class="pt-btn"><i class="ph ph-arrow-left"></i> Back</a>
            <button type="button" class="pt-btn" onclick="printInvoice(false)"><i class="ph ph-printer"></i> Print</button>
            <button type="button" class="pt-btn is-primary" onclick="printInvoice(true)"><i class="ph ph-file-pdf"></i> Download PDF</button>
        </div>
        <p class="pt-hint">A4 page. To save a PDF, choose <b>Save as PDF</b> as the destination in the print dialog.</p>
    </div>

    <main class="pt-stage">
        <div class="inv-sheet {{ $orientation === 'landscape' ? 'is-landscape' : '' }}" id="invSheet">
            @include('admin.invoices.partials.document')
        </div>
    </main>

    <script>
        (function () {
            var current = @json($orientation);
            var originalTitle = document.title;
            var pdfName = @json($pdfName);

            // Replace the @page rule by swapping the <style> element (some browsers ignore in-place edits).
            window.setOrientation = function (o) {
                current = o === 'landscape' ? 'landscape' : 'portrait';
                var old = document.getElementById('pageSizeStyle');
                var fresh = document.createElement('style');
                fresh.id = 'pageSizeStyle';
                fresh.textContent = '@page { size: A4 ' + current + '; margin: 10mm; }';
                if (old) old.parentNode.replaceChild(fresh, old); else document.head.appendChild(fresh);
                document.getElementById('invSheet').classList.toggle('is-landscape', current === 'landscape');
                document.querySelectorAll('.pt-seg button').forEach(function (b) {
                    var on = b.getAttribute('data-orient') === current;
                    b.classList.toggle('is-selected', on);
                    b.setAttribute('aria-checked', on ? 'true' : 'false');
                });
                try { localStorage.setItem('invoice_orientation', current); } catch (e) {}
                try { var u = new URL(window.location.href); u.searchParams.set('orientation', current); history.replaceState(null, '', u); } catch (e) {}
            };

            // asPdf: use the invoice number as the suggested PDF file name (browsers use document.title).
            window.printInvoice = function (asPdf) {
                window.setOrientation(current);
                if (asPdf) document.title = pdfName;
                var go = function () { window.print(); };
                if (document.fonts && document.fonts.ready) document.fonts.ready.then(go); else go();
            };
            window.addEventListener('afterprint', function () { document.title = originalTitle; });

            document.querySelectorAll('.pt-seg button').forEach(function (b) {
                b.addEventListener('click', function () { window.setOrientation(b.getAttribute('data-orient')); });
            });

            var params = new URLSearchParams(window.location.search);
            if (!params.has('orientation')) {
                try { var saved = localStorage.getItem('invoice_orientation'); if (saved && saved !== current) window.setOrientation(saved); } catch (e) {}
            }
            if (params.get('autoprint') === '1') {
                window.addEventListener('load', function () { setTimeout(function () { window.printInvoice(params.get('pdf') === '1'); }, 250); });
            }
        })();
    </script>
</body>
</html>
