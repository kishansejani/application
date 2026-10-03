<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.tax_invoice') }} - {{ $order->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Hind+Vadodara:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Vadodara', system-ui, sans-serif; }
        @page { size: A4; margin: 14mm; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: #fff !important; }
            .invoice-sheet { box-shadow: none !important; border: 0 !important; padding: 0 !important; border-radius: 0 !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-100 p-3 sm:p-8 text-slate-800 antialiased">

    <div class="no-print max-w-3xl mx-auto mb-4 flex items-center justify-between gap-2">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('order.show', $order->order_number) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-[13px] font-bold hover:bg-slate-50">
            <i class="ph-bold ph-arrow-left"></i>{{ __('messages.back') }}
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 text-white font-bold text-[13px] rounded-xl shadow hover:bg-emerald-700">
            <i class="ph-bold ph-printer"></i>{{ __('messages.print_save_pdf') }}
        </button>
    </div>

    <div class="invoice-sheet max-w-3xl mx-auto bg-white p-5 sm:p-10 rounded-3xl shadow-xl border border-slate-200">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 pb-6 border-b-2 border-slate-900">
            <div class="flex items-start gap-3">
                <span class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-2xl shrink-0"><i class="ph-fill ph-basket"></i></span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">FreshExpress Grocery</h1>
                    <p class="text-xs text-emerald-700 font-bold">ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર</p>
                    <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
                        Bodakdev, SG Highway, Ahmedabad, Gujarat - 380054<br>
                        <strong>GSTIN:</strong> 24AAACG1234F1Z5 · <strong>FSSAI:</strong> 10721026000123
                    </p>
                </div>
            </div>
            <div class="sm:text-right">
                <span class="inline-block px-2.5 py-1 bg-slate-900 text-white font-bold text-[10px] rounded uppercase tracking-wider">{{ __('messages.tax_invoice') }}</span>
                <h2 class="text-base font-bold font-mono mt-1.5">{{ $order->invoice_number }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('messages.order') }}: <span class="font-mono">{{ $order->order_number }}</span></p>
                <p class="text-xs text-slate-500">{{ __('messages.date') }}: {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <!-- Customer & delivery -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-5 border-b border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">{{ __('messages.billed_to') }}</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $order->customer_name }}</p>
                <p class="text-slate-600 mt-0.5 leading-relaxed">{{ $order->delivery_address }}</p>
                <p class="text-slate-800 font-semibold mt-1">{{ __('messages.phone') }}: +91 {{ $order->customer_phone }}</p>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-2xl">
                <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">{{ __('messages.delivery_slot') }}</span>
                <p class="font-bold text-slate-900 mt-0.5">@include('frontend.partials.slot-text', ['order' => $order])</p>
                <p class="text-slate-500 text-[11px] mt-1.5">{{ __('messages.payment') }}: <strong class="text-slate-700">{{ \Lang::has('messages.payment_methods.' . $order->payment_method) ? __('messages.payment_methods.' . $order->payment_method) : strtoupper($order->payment_method) }}</strong> ({{ \Lang::has('messages.payment_statuses.' . $order->payment_status) ? __('messages.payment_statuses.' . $order->payment_status) : ucfirst($order->payment_status) }})</p>
                @if($order->transaction_id)<p class="text-slate-500 text-[11px] font-mono">Txn: {{ $order->transaction_id }}</p>@endif
            </div>
        </div>

        <!-- Items -->
        <div class="py-4 -mx-5 sm:mx-0 overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider">
                        <th class="p-2.5 pl-5 sm:pl-2.5">#</th>
                        <th class="p-2.5">{{ __('messages.item_description') }}</th>
                        <th class="p-2.5">{{ __('messages.unit') }}</th>
                        <th class="p-2.5 text-right">{{ __('messages.price') }}</th>
                        <th class="p-2.5 text-center">{{ __('messages.qty') }}</th>
                        <th class="p-2.5 pr-5 sm:pr-2.5 text-right">{{ __('messages.total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($order->items as $i => $item)
                        <tr>
                            <td class="p-2.5 pl-5 sm:pl-2.5 text-slate-400">{{ $i + 1 }}</td>
                            <td class="p-2.5 font-bold text-slate-900">
                                {{ app()->getLocale() === 'gu' && $item->product_name_gu ? $item->product_name_gu : $item->product_name_en }}
                                <span class="block text-slate-500 font-normal text-[10px]">{{ app()->getLocale() === 'gu' ? $item->product_name_en : $item->product_name_gu }}</span>
                            </td>
                            <td class="p-2.5 text-slate-500">{{ $item->product_unit }}</td>
                            <td class="p-2.5 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="p-2.5 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="p-2.5 pr-5 sm:pr-2.5 text-right font-bold">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="pt-4 border-t-2 border-slate-900 flex justify-end text-xs">
            <div class="w-full sm:w-64 space-y-1.5">
                <div class="flex justify-between text-slate-600"><span>{{ __('messages.subtotal') }}</span><span class="font-bold">₹{{ number_format($order->subtotal, 2) }}</span></div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 font-bold"><span>{{ __('messages.discount') }}{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><span>-₹{{ number_format($order->discount_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between text-slate-600"><span>{{ __('messages.delivery') }}</span><span class="font-bold">{{ $order->delivery_charge == 0 ? __('messages.free') : '₹' . number_format($order->delivery_charge, 2) }}</span></div>
                <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-slate-200"><span>{{ __('messages.grand_total') }}</span><span class="text-emerald-700">₹{{ number_format($order->total_amount, 2) }}</span></div>
            </div>
        </div>

        <div class="mt-10 pt-4 border-t border-dashed border-slate-200 flex flex-col sm:flex-row justify-between gap-2 text-[10px] text-slate-400">
            <span>{{ __('messages.thank_you_shopping') }}</span>
            <span>{{ __('messages.support') }}: +91 98765 43210 · support@freshexpress.in</span>
        </div>
    </div>
</body>
</html>
