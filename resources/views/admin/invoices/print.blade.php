<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Hind+Vadodara:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Vadodara', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; background: #fff; }
        }
    </style>
</head>
<body class="bg-slate-100 p-8 text-slate-800" onload="window.print()">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow border border-slate-200 print:shadow-none print:border-none print:p-0">
        <!-- Header -->
        <div class="flex justify-between items-start pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">FreshExpress Grocery</h1>
                <p class="text-xs text-emerald-700 font-bold">ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર</p>
                <p class="text-[11px] text-slate-500 mt-2">
                    Bodakdev, SG Highway, Ahmedabad, Gujarat - 380054<br>
                    <strong>GSTIN:</strong> 24AAACG1234F1Z5 • <strong>FSSAI:</strong> 10721026000123
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-0.5 bg-slate-900 text-white font-bold text-[10px] rounded uppercase">Tax Invoice</span>
                <h2 class="text-base font-bold font-mono mt-1">{{ $order->invoice_number }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Order: {{ $order->order_number }}</p>
                <p class="text-xs text-slate-500">Date: {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <!-- Customer & Delivery -->
        <div class="grid grid-cols-2 gap-4 py-4 border-b border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase text-[10px]">Customer:</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $order->customer_name }}</p>
                <p class="text-slate-600 mt-0.5">{{ $order->delivery_address }}</p>
                <p class="text-slate-800 font-semibold mt-1">Phone: +91 {{ $order->customer_phone }}</p>
            </div>
            <div class="bg-slate-50 p-3 rounded-xl">
                <span class="text-slate-400 font-bold uppercase text-[10px]">Delivery Slot:</span>
                <p class="font-bold text-slate-900 mt-0.5">{{ $order->delivery_slot }}</p>
                <p class="text-slate-500 text-[11px] mt-1">Method: {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</p>
            </div>
        </div>

        <!-- Items -->
        <div class="py-4">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-y border-slate-200">
                        <th class="p-2">#</th>
                        <th class="p-2">Item</th>
                        <th class="p-2">Unit</th>
                        <th class="p-2 text-right">Price</th>
                        <th class="p-2 text-center">Qty</th>
                        <th class="p-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($order->items as $i => $item)
                        <tr>
                            <td class="p-2 text-slate-400">{{ $i + 1 }}</td>
                            <td class="p-2 font-bold text-slate-900">
                                {{ $item->product_name_en }}
                                <span class="block text-slate-500 font-normal text-[10px]">{{ $item->product_name_gu }}</span>
                            </td>
                            <td class="p-2 text-slate-500">{{ $item->product_unit }}</td>
                            <td class="p-2 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="p-2 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="p-2 text-right font-bold">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="pt-3 border-t-2 border-slate-900 flex justify-end text-xs">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>Discount ({{ $order->coupon_code }}):</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Delivery:</span>
                    <span class="font-bold">{{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-1.5 border-t border-slate-200">
                    <span>Grand Total:</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-[10px] text-slate-400">
            Thank you for shopping with FreshExpress Grocery! • www.freshexpress.in
        </div>
    </div>

</body>
</html>
