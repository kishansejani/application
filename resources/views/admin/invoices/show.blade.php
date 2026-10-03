@extends('admin.layouts.admin')

@section('title', 'Invoice ' . $order->invoice_number)

@section('content')
    <x-admin.page-header :title="'Invoice '.$order->invoice_number" :subtitle="'GST tax invoice for order '.$order->order_number.' · '.$order->created_at->format('d M Y')" icon="receipt">
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Order</a>
        <a href="{{ route('admin.invoices.print', $order) }}" target="_blank" class="btn btn-outline" id="invPrintLink"><i class="ph ph-printer"></i> Print</a>
        <a href="{{ route('admin.invoices.print', ['order' => $order, 'autoprint' => 1, 'pdf' => 1]) }}" target="_blank" class="btn btn-primary" id="invPdfLink"><i class="ph ph-file-pdf"></i> Download PDF</a>
    </x-admin.page-header>

    <div class="card mb-5">
        <div class="card-body !py-3 flex flex-wrap items-center gap-3 text-[12.5px]">
            <span class="font-bold text-slate-700 dark:text-slate-200">Page orientation</span>
            <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700" role="radiogroup" aria-label="Page orientation">
                <button type="button" class="inv-orient-btn is-selected" data-orient="portrait" role="radio" aria-checked="true"><i class="ph ph-file"></i> Portrait</button>
                <button type="button" class="inv-orient-btn" data-orient="landscape" role="radio" aria-checked="false"><i class="ph ph-file inv-rot90"></i> Landscape</button>
            </div>
            <span class="text-slate-500 dark:text-slate-400">A4 · choose <b>Save as PDF</b> as the destination in the print dialog to download.</span>
        </div>
    </div>

    <div class="overflow-x-auto pb-2">
        <div class="inv-sheet rounded-2xl shadow-lift border border-slate-200 dark:border-slate-700" id="invSheet">
            @include('admin.invoices.partials.document')
        </div>
    </div>
@endsection

@push('styles')
    @include('admin.invoices.partials.styles')
    <style>
        .inv-orient-btn { display: inline-flex; align-items: center; gap: .35rem; height: 30px; padding: 0 .75rem; border-radius: 9px; font-size: 12px; font-weight: 700; color: #64748b; }
        .inv-orient-btn.is-selected { background: #fff; color: #0f172a; box-shadow: 0 1px 2px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.1); }
        .dark .inv-orient-btn { color: #94a3b8; }
        .dark .inv-orient-btn.is-selected { background: #334155; color: #fff; }
        .inv-rot90 { display: inline-block; transform: rotate(90deg); }
    </style>
@endpush

@push('scripts')
<script>
    (function () {
        var base = { print: @json(route('admin.invoices.print', $order)) };
        var orient = 'portrait';
        try { orient = localStorage.getItem('invoice_orientation') || 'portrait'; } catch (e) {}

        function apply(o) {
            orient = o === 'landscape' ? 'landscape' : 'portrait';
            document.getElementById('invSheet').classList.toggle('is-landscape', orient === 'landscape');
            document.querySelectorAll('.inv-orient-btn').forEach(function (b) {
                var on = b.getAttribute('data-orient') === orient;
                b.classList.toggle('is-selected', on);
                b.setAttribute('aria-checked', on ? 'true' : 'false');
            });
            document.getElementById('invPrintLink').href = base.print + '?orientation=' + orient + '&autoprint=1';
            document.getElementById('invPdfLink').href = base.print + '?orientation=' + orient + '&autoprint=1&pdf=1';
            try { localStorage.setItem('invoice_orientation', orient); } catch (e) {}
        }
        document.querySelectorAll('.inv-orient-btn').forEach(function (b) {
            b.addEventListener('click', function () { apply(b.getAttribute('data-orient')); });
        });
        apply(orient);
    })();
</script>
@endpush
