{{-- A4 GST tax invoice body. Shared by admin/invoices/show and admin/invoices/print. Styles: partials/styles. --}}
@php
    $inrWords = function ($amount) {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
                 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $two = function ($n) use ($ones, $tens) { return $n < 20 ? $ones[$n] : trim($tens[intdiv($n, 10)].' '.$ones[$n % 10]); };
        $three = function ($n) use ($two, $ones) {
            $h = intdiv($n, 100); $r = $n % 100;
            return trim(($h ? $ones[$h].' Hundred' : '').($h && $r ? ' ' : '').($r ? $two($r) : ''));
        };
        $rupees = (int) floor($amount);
        $paise = (int) round(($amount - $rupees) * 100);
        if ($paise === 100) { $rupees++; $paise = 0; }
        $parts = [];
        foreach ([['Crore', 10000000], ['Lakh', 100000], ['Thousand', 1000]] as [$label, $div]) {
            if ($rupees >= $div) { $parts[] = ($div === 10000000 ? $three(intdiv($rupees, $div)) : $two(intdiv($rupees, $div))).' '.$label; $rupees %= $div; }
        }
        if ($rupees > 0) $parts[] = $three($rupees);
        $words = $parts ? implode(' ', $parts) : 'Zero';
        return 'Rupees '.$words.($paise ? ' and '.$two($paise).' Paise' : '').' Only';
    };
    $methodLabel = match (strtolower((string) $order->payment_method)) {
        'cod' => 'Cash on delivery', 'upi' => 'UPI', 'card' => 'Card', 'netbanking' => 'Net banking',
        default => ucfirst((string) $order->payment_method),
    };
    $taxTotal = (float) $order->tax_amount;
    $cgst = round($taxTotal / 2, 2);
    $sgst = round($taxTotal - $cgst, 2);
    $qtyTotal = $order->items->sum('quantity');
@endphp
<div class="inv-doc">
    {{-- Header --}}
    <header class="inv-head">
        <div class="inv-brand">
            <div class="inv-logo" aria-hidden="true">FE</div>
            <div>
                <h2 class="inv-store">FreshExpress Grocery</h2>
                <p class="inv-store-gu" lang="gu">ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર</p>
                <p class="inv-addr">
                    Shop 101-103, Green Earth Commercial Arcade, Bodakdev,<br>
                    SG Highway, Ahmedabad, Gujarat – 380054<br>
                    +91 98765 43210 · support@freshexpress.in
                </p>
            </div>
        </div>
        <div class="inv-title-block">
            <p class="inv-title">Tax Invoice</p>
            <p class="inv-copy">Original for recipient</p>
            <table class="inv-ids">
                <tr><th>GSTIN</th><td>24AAACG1234F1Z5</td></tr>
                <tr><th>FSSAI</th><td>10721026000123</td></tr>
                <tr><th>State</th><td>Gujarat (24)</td></tr>
            </table>
        </div>
    </header>

    {{-- Invoice meta --}}
    <section class="inv-meta">
        <div><span>Invoice no.</span><strong class="inv-mono">{{ $order->invoice_number }}</strong></div>
        <div><span>Invoice date</span><strong>{{ $order->created_at->format('d M Y') }}</strong></div>
        <div><span>Order no.</span><strong class="inv-mono">{{ $order->order_number }}</strong></div>
        <div><span>Place of supply</span><strong>Gujarat (24)</strong></div>
        <div><span>Payment</span><strong>{{ $methodLabel }} · {{ ucfirst($order->payment_status) }}</strong></div>
        @if($order->transaction_id)
            <div><span>Transaction ID</span><strong class="inv-mono">{{ $order->transaction_id }}</strong></div>
        @endif
    </section>

    {{-- Parties --}}
    <section class="inv-parties">
        <div class="inv-party">
            <h4>Billed &amp; shipped to</h4>
            <p class="inv-party-name">{{ $order->customer_name }}</p>
            <p>{{ $order->delivery_address }}@if($order->delivery_city && !str_contains($order->delivery_address, $order->delivery_city)), {{ $order->delivery_city }}@endif @if($order->delivery_pincode && !str_contains($order->delivery_address, $order->delivery_pincode)) – {{ $order->delivery_pincode }}@endif</p>
            <p>Phone: +91 {{ $order->customer_phone }}@if($order->customer_email) · {{ $order->customer_email }}@endif</p>
        </div>
        <div class="inv-party">
            <h4>Delivery</h4>
            <p class="inv-party-name">{{ $order->delivery_type === 'two_hours' ? '2-hour express delivery' : 'Next-day morning delivery' }}</p>
            <p>{{ $order->delivery_slot }}</p>
            <p>Order placed at {{ $order->created_at->format('h:i A') }} ({{ $order->created_at->lt($order->created_at->copy()->setTime(12, 0)) ? 'before' : 'after' }} the 12 PM cut-off)</p>
        </div>
    </section>

    {{-- Items --}}
    <table class="inv-items">
        <thead>
            <tr>
                <th class="c-sn">#</th>
                <th>Description of goods <span lang="gu">/ વિગત</span></th>
                <th class="c-unit">Unit</th>
                <th class="c-num">Qty</th>
                <th class="c-num">Rate (₹)</th>
                <th class="c-num">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $idx => $item)
                <tr>
                    <td class="c-sn">{{ $idx + 1 }}</td>
                    <td>
                        <span class="inv-item-en">{{ $item->product_name_en }}</span>
                        @if($item->product_name_gu)<span class="inv-item-gu" lang="gu">{{ $item->product_name_gu }}</span>@endif
                    </td>
                    <td class="c-unit">{{ $item->product_unit }}</td>
                    <td class="c-num">{{ $item->quantity }}</td>
                    <td class="c-num">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="c-num"><strong>{{ number_format($item->total_price, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="2">Total</td>
                <td class="c-num">{{ $qtyTotal }}</td>
                <td></td>
                <td class="c-num">{{ number_format($order->subtotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Totals --}}
    <section class="inv-summary">
        <div class="inv-words">
            <h4>Amount in words</h4>
            <p>{{ $inrWords((float) $order->total_amount) }}</p>
            <h4 class="mt">Terms &amp; conditions</h4>
            <ol>
                <li>Goods once sold can be returned within 24 hours only if defective or damaged.</li>
                <li>All prices are inclusive of applicable GST.</li>
                <li>2-hour express delivery applies to orders placed before 12 PM.</li>
                <li lang="gu">તમામ માલસામાન પર ૨૪ કલાકની સરળ રીપ્લેસમેન્ટ ગેરંટી ઉપલબ્ધ છે.</li>
            </ol>
        </div>
        <table class="inv-totals">
            <tr><th>Items subtotal</th><td>₹{{ number_format($order->subtotal, 2) }}</td></tr>
            @if($order->discount_amount > 0)
                <tr class="is-discount"><th>Coupon discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</th><td>−₹{{ number_format($order->discount_amount, 2) }}</td></tr>
            @endif
            <tr><th>Delivery charge</th><td>{{ $order->delivery_charge == 0 ? 'Free' : '₹'.number_format($order->delivery_charge, 2) }}</td></tr>
            @if($taxTotal > 0)
                <tr><th>CGST</th><td>₹{{ number_format($cgst, 2) }}</td></tr>
                <tr><th>SGST</th><td>₹{{ number_format($sgst, 2) }}</td></tr>
            @else
                <tr><th>GST (CGST + SGST)</th><td>Included</td></tr>
            @endif
            <tr class="is-grand"><th>Grand total</th><td>₹{{ number_format($order->total_amount, 2) }}</td></tr>
            <tr class="is-status"><th>Payment status</th><td>{{ ucfirst($order->payment_status) }}</td></tr>
        </table>
    </section>

    {{-- Footer --}}
    <footer class="inv-foot">
        <div>
            <p>Declaration: We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</p>
            <p class="inv-muted">This is a computer-generated invoice and does not require a physical signature.</p>
        </div>
        <div class="inv-sign">
            <div class="inv-sign-line"></div>
            <strong>For FreshExpress Grocery</strong>
            <span>Authorised signatory</span>
        </div>
    </footer>
    <p class="inv-thanks">Thank you for shopping with FreshExpress Grocery · <span lang="gu">ખરીદી બદલ આભાર</span> · www.freshexpress.in</p>
</div>
