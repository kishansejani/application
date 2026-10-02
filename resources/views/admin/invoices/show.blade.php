@extends('admin.layouts.admin')

@section('title', 'Invoice ' . $order->invoice_number)
@section('page-title', 'Tax Invoice: ' . $order->invoice_number)
@section('page-subtitle', 'Generated for Order #' . $order->order_number)

@section('action-buttons')
<div class="flex items-center gap-3">
    <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-print"></i>
        <span>Print Invoice</span>
    </button>
    <a href="{{ route('admin.orders.show', $order) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Order</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl p-8 sm:p-12 print:shadow-none print:border-none print:p-0">

    <!-- Invoice Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-8 border-b-2 border-slate-900 gap-6">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-2xl shadow-md">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">FreshExpress Grocery</h2>
                    <p class="text-xs font-semibold text-brand-700">ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                Shop 101-103, Green Earth Commercial Arcade, Bodakdev,<br>
                SG Highway, Ahmedabad, Gujarat - 380054<br>
                <strong>GSTIN:</strong> 24AAACG1234F1Z5 • <strong>FSSAI:</strong> 10721026000123<br>
                <strong>Phone:</strong> +91 98765 43210 • <strong>Email:</strong> support@freshexpress.in
            </p>
        </div>

        <div class="text-left sm:text-right">
            <span class="inline-block px-3 py-1 bg-slate-900 text-white font-extrabold text-xs tracking-widest uppercase rounded-lg mb-2">
                ORIGINAL TAX INVOICE
            </span>
            <h3 class="text-lg font-mono font-bold text-slate-900">{{ $order->invoice_number }}</h3>
            <p class="text-xs text-slate-500 mt-1"><strong>Order Ref:</strong> {{ $order->order_number }}</p>
            <p class="text-xs text-slate-500"><strong>Invoice Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
            <p class="text-xs text-slate-500"><strong>Payment:</strong> <span class="font-bold uppercase text-slate-800">{{ $order->payment_method }} ({{ $order->payment_status }})</span></p>
        </div>
    </div>

    <!-- Billed To & Delivery Window -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200 text-xs">
        <div>
            <h5 class="font-bold text-slate-400 uppercase tracking-wider mb-2">Billed & Delivered To:</h5>
            <h4 class="font-extrabold text-slate-900 text-sm">{{ $order->customer_name }}</h4>
            <p class="text-slate-600 mt-1 leading-relaxed">{{ $order->delivery_address }}</p>
            <p class="text-slate-800 font-semibold mt-2"><strong>Phone:</strong> +91 {{ $order->customer_phone }}</p>
            @if($order->customer_email)
                <p class="text-slate-500"><strong>Email:</strong> {{ $order->customer_email }}</p>
            @endif
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex flex-col justify-between">
            <div>
                <h5 class="font-bold text-slate-500 uppercase tracking-wider mb-1.5">Delivery Service Details:</h5>
                <p class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                    @if($order->delivery_type === 'two_hours')
                        <span class="text-amber-600">⚡ 2-Hour Express Delivery</span>
                    @else
                        <span class="text-blue-600">📅 Next-Day Morning Slot</span>
                    @endif
                </p>
                <p class="text-slate-600 mt-1"><strong>Slot:</strong> {{ $order->delivery_slot }}</p>
            </div>
            <div class="text-[11px] text-slate-500 pt-2 border-t border-slate-200 mt-2">
                Order Placed at {{ $order->created_at->format('h:i A') }} ({{ $order->created_at->lt($order->created_at->copy()->setTime(12,0)) ? 'Before 12 PM Cutoff' : 'After 12 PM Cutoff' }})
            </div>
        </div>
    </div>

    <!-- Itemized Table -->
    <div class="py-6">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-y border-slate-200">
                    <th class="p-3">#</th>
                    <th class="p-3">Item Description (English / ગુજરાતી)</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3 text-right">Unit Price</th>
                    <th class="p-3 text-center">Qty</th>
                    <th class="p-3 text-right">Total (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td class="p-3 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                        <td class="p-3">
                            <span class="font-bold text-slate-900 text-xs">{{ $item->product_name_en }}</span>
                            <span class="block text-brand-700 font-semibold text-[11px]">{{ $item->product_name_gu }}</span>
                        </td>
                        <td class="p-3 text-slate-600">{{ $item->product_unit }}</td>
                        <td class="p-3 text-right font-semibold">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3 text-center font-bold">{{ $item->quantity }}</td>
                        <td class="p-3 text-right font-bold text-slate-900">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Calculations and Signature -->
    <div class="pt-4 border-t-2 border-slate-900 grid grid-cols-1 sm:grid-cols-2 gap-8 text-xs">
        <div class="space-y-3">
            <h5 class="font-bold text-slate-700 uppercase">Terms & Conditions:</h5>
            <ul class="list-disc list-inside text-slate-500 space-y-1 text-[11px] leading-relaxed">
                <li>Goods once sold are eligible for 24-hour return if defective or damaged.</li>
                <li>All prices include applicable GST as per government regulations.</li>
                <li>Express delivery within 2 hours applies to orders before 12 PM.</li>
                <li>તમામ માલસામાન પર ૨૪ કલાકની સરળ રીપ્લેસમેન્ટ ગેરંટી ઉપલબ્ધ છે.</li>
            </ul>
        </div>

        <div class="space-y-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
            <div class="flex justify-between text-slate-600">
                <span>Items Subtotal:</span>
                <span class="font-bold">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-bold">
                    <span>Coupon Discount ({{ $order->coupon_code }}):</span>
                    <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-slate-600">
                <span>Delivery Charge:</span>
                <span class="font-bold">{{ $order->delivery_charge == 0 ? 'FREE' : '₹' . number_format($order->delivery_charge, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>GST / Taxes:</span>
                <span class="font-bold">Included</span>
            </div>
            <div class="flex justify-between text-slate-900 font-extrabold text-base pt-2 border-t border-slate-300">
                <span>Grand Total:</span>
                <span class="text-brand-700">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer Signature -->
    <div class="mt-12 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-slate-400 text-[11px]">
        <div>
            <span>Computer generated invoice • No physical signature required</span>
        </div>
        <div class="text-center sm:text-right mt-4 sm:mt-0">
            <span class="font-bold text-slate-700 block">For FreshExpress Grocery</span>
            <span class="text-[10px] text-slate-400">Authorized Signatory</span>
        </div>
    </div>

</div>
@endsection
