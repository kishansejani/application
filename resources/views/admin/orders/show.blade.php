@extends('admin.layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('content')
@php
    $steps = [
        'pending'          => ['Order placed',     'receipt',        'Waiting for confirmation'],
        'confirmed'        => ['Confirmed',        'check-circle',   'Accepted by the store'],
        'processing'       => ['Packing',          'package',        'Items being packed'],
        'out_for_delivery' => ['Out for delivery', 'truck',          'Rider is on the way'],
        'delivered'        => ['Delivered',        'house-line',     'Handed to customer'],
    ];
    $stepKeys = array_keys($steps);
    $isCancelled = $order->order_status === 'cancelled';
    $currentIndex = $isCancelled ? -1 : array_search($order->order_status, $stepKeys);
    if ($currentIndex === false) $currentIndex = 0;

    $statusMeta = [
        'pending'          => ['Pending',          'badge-warning'],
        'confirmed'        => ['Confirmed',        'badge-info'],
        'processing'       => ['Processing',       'badge-violet'],
        'out_for_delivery' => ['Out for delivery', 'badge-violet'],
        'delivered'        => ['Delivered',        'badge-success'],
        'cancelled'        => ['Cancelled',        'badge-danger'],
    ];
    $paymentMeta = [
        'pending' => ['Pending', 'badge-warning'], 'paid' => ['Paid', 'badge-success'],
        'failed' => ['Failed', 'badge-danger'], 'refunded' => ['Refunded', 'badge-neutral'],
    ];
    $methodLabel = match (strtolower((string) $order->payment_method)) {
        'cod' => 'Cash on delivery', 'upi' => 'UPI', 'card' => 'Card', 'netbanking' => 'Net banking',
        default => ucfirst((string) $order->payment_method),
    };
    [$stLabel, $stClass] = $statusMeta[$order->order_status] ?? [ucfirst(str_replace('_', ' ', $order->order_status)), 'badge-neutral'];
    [$payLabel, $payClass] = $paymentMeta[$order->payment_status] ?? [ucfirst($order->payment_status), 'badge-neutral'];

    $phoneDigits = preg_replace('/\D+/', '', (string) $order->customer_phone);
    $waNumber = strlen($phoneDigits) === 10 ? '91'.$phoneDigits : $phoneDigits;
    $waText = rawurlencode("Hello {$order->customer_name}, this is regarding your order {$order->order_number}.");
    $hasPin = $order->delivery_lat && $order->delivery_lng;
    $mapUrl = $hasPin
        ? 'https://www.google.com/maps?q='.$order->delivery_lat.','.$order->delivery_lng
        : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($order->delivery_address);
    $fullAddress = trim($order->delivery_address.($order->delivery_city && !str_contains($order->delivery_address, $order->delivery_city) ? ', '.$order->delivery_city : '').($order->delivery_pincode && !str_contains($order->delivery_address, $order->delivery_pincode) ? ' - '.$order->delivery_pincode : ''));
    $itemCount = $order->items->count();
    $qtyTotal = $order->items->sum('quantity');
@endphp

    <x-admin.page-header :title="'Order '.$order->order_number" :subtitle="'Placed on '.$order->created_at->format('d M Y, h:i A').' · '.$itemCount.' '.\Illuminate\Support\Str::plural('item', $itemCount)" icon="receipt">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Orders</a>
        <a href="{{ route('admin.invoices.show', $order) }}" class="btn btn-primary"><i class="ph ph-receipt"></i> View invoice</a>
    </x-admin.page-header>

    {{-- Quick actions --}}
    <div class="card mb-5">
        <div class="card-body !py-3.5 flex flex-wrap items-center gap-2.5">
            <div class="flex flex-wrap items-center gap-2 mr-auto min-w-0">
                <span class="badge {{ $stClass }} badge-dot">{{ $stLabel }}</span>
                <span class="badge {{ $payClass }}">{{ $payLabel }} · {{ $methodLabel }}</span>
                @if($order->delivery_type === 'two_hours')
                    <span class="badge badge-warning"><i class="ph-fill ph-lightning"></i> 2-hour express</span>
                @else
                    <span class="badge badge-info"><i class="ph ph-calendar-blank"></i> Next-day</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.invoices.print', ['order' => $order, 'autoprint' => 1]) }}" target="_blank" class="btn btn-outline btn-sm flex-1 sm:flex-none"><i class="ph ph-printer"></i> Print invoice</a>
                <a href="tel:{{ $order->customer_phone }}" class="btn btn-outline btn-sm flex-1 sm:flex-none"><i class="ph ph-phone"></i> Call</a>
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm flex-1 sm:flex-none !text-emerald-700 dark:!text-emerald-400"><i class="ph ph-whatsapp-logo"></i> WhatsApp</a>
                <button type="button" class="btn btn-outline btn-sm flex-1 sm:flex-none" onclick="copyToClipboard(@js($order->customer_name."\n".$fullAddress."\n+91 ".$order->customer_phone), 'Delivery address copied')"><i class="ph ph-copy"></i> Copy address</button>
                <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm flex-1 sm:flex-none"><i class="ph ph-map-pin"></i> Map</a>
            </div>
        </div>
    </div>

    {{-- Status timeline --}}
    <div class="card mb-5">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="ph-duotone ph-path"></i> Fulfilment progress</h3>
                <p class="card-subtitle">{{ $isCancelled ? 'This order was cancelled.' : 'Step '.($currentIndex + 1).' of '.count($steps).' · '.$steps[$stepKeys[$currentIndex]][2] }}</p>
            </div>
            @if($order->estimated_delivery_at && !$isCancelled && $order->order_status !== 'delivered')
                <span class="badge badge-neutral"><i class="ph ph-clock"></i> ETA {{ $order->estimated_delivery_at->format('d M, h:i A') }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($isCancelled)
                <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-500/10 dark:border-rose-500/30 dark:text-rose-300">
                    <i class="ph-fill ph-x-circle text-2xl shrink-0"></i>
                    <div>
                        <p class="font-bold text-sm">Order cancelled</p>
                        <p class="text-[12.5px] mt-0.5">Reason: {{ $order->cancellation_reason ?: 'Cancelled by administrator' }}</p>
                        <p class="text-[11.5px] mt-1 opacity-80">Item stock was returned to inventory when the order was cancelled.</p>
                    </div>
                </div>
            @else
                <ol class="order-stepper">
                    @foreach($steps as $key => [$label, $icon, $hint])
                        @php
                            $idx = $loop->index;
                            $state = $idx < $currentIndex ? 'is-done' : ($idx === $currentIndex ? 'is-current' : '');
                            $sub = $key === 'pending' ? $order->created_at->format('d M, h:i A') : ($idx === $currentIndex ? $hint : ($idx < $currentIndex ? 'Completed' : 'Upcoming'));
                        @endphp
                        <li class="order-step {{ $state }}">
                            <span class="order-step-dot">
                                <i class="{{ $idx < $currentIndex ? 'ph-bold ph-check' : 'ph-duotone ph-'.$icon }}"></i>
                            </span>
                            <span class="order-step-text">
                                <span class="block text-[12.5px] font-bold">{{ $label }}</span>
                                <span class="block text-[11px] opacity-75">{{ $sub }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        {{-- Left: items + delivery --}}
        <div class="xl:col-span-2 space-y-5 min-w-0">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-shopping-bag"></i> Ordered items</h3>
                        <p class="card-subtitle">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('product', $itemCount) }} · {{ $qtyTotal }} {{ \Illuminate\Support\Str::plural('unit', $qtyTotal) }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-4 sm:px-5 py-2.5">Product</th>
                                <th class="px-3 py-2.5 text-right whitespace-nowrap hidden sm:table-cell">Unit price</th>
                                <th class="px-3 py-2.5 text-center hidden sm:table-cell">Qty</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3 sm:min-w-[13rem]">
                                            <img src="{{ $item->product ? $item->product->thumbnail_url : ($item->product_image ?: asset('images/default-product.png')) }}" alt="" class="thumb" loading="lazy">
                                            <div class="min-w-0">
                                                @if($item->product)
                                                    <a href="{{ route('admin.products.edit', $item->product) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $item->product_name_en }}</a>
                                                @else
                                                    <span class="block font-bold text-slate-900 dark:text-white leading-snug">{{ $item->product_name_en }}</span>
                                                @endif
                                                <span class="block text-xs text-slate-500 dark:text-slate-400" lang="gu">{{ $item->product_name_gu }}</span>
                                                <span class="block text-[11px] text-slate-400">{{ $item->product_unit }}<span class="sm:hidden"> · ₹{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}</span></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap text-slate-600 dark:text-slate-300 hidden sm:table-cell">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-slate-800 dark:text-slate-100 hidden sm:table-cell">× {{ $item->quantity }}</td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap font-bold text-slate-900 dark:text-white">₹{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 dark:border-slate-700 px-5 py-4">
                    <dl class="ml-auto max-w-sm space-y-2 text-[13px]">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <dt>Items subtotal</dt>
                            <dd class="font-semibold">₹{{ number_format($order->subtotal, 2) }}</dd>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-700 dark:text-emerald-400">
                                <dt>Coupon discount @if($order->coupon_code)<span class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10">{{ $order->coupon_code }}</span>@endif</dt>
                                <dd class="font-semibold">−₹{{ number_format($order->discount_amount, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <dt>Delivery fee</dt>
                            <dd class="font-semibold">{{ $order->delivery_charge == 0 ? 'Free' : '₹'.number_format($order->delivery_charge, 2) }}</dd>
                        </div>
                        @if($order->tax_amount > 0)
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <dt>GST</dt>
                                <dd class="font-semibold">₹{{ number_format($order->tax_amount, 2) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between items-baseline pt-2.5 border-t border-slate-200 dark:border-slate-700">
                            <dt class="font-bold text-slate-900 dark:text-white">{{ $order->payment_status === 'paid' ? 'Total paid' : 'Total payable' }}</dt>
                            <dd class="text-lg font-extrabold text-slate-900 dark:text-white">₹{{ number_format($order->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-clock-countdown"></i> Delivery schedule</h3>
                        <p class="card-subtitle">Calculated from the 12:00 PM same-day cut-off</p>
                    </div>
                    @if($order->delivery_type === 'two_hours')
                        <span class="badge badge-warning"><i class="ph-fill ph-lightning"></i> 2-hour express</span>
                    @else
                        <span class="badge badge-info"><i class="ph ph-calendar-blank"></i> Next-day morning</span>
                    @endif
                </div>
                <div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Delivery window</span>
                        <span class="block font-bold text-slate-800 dark:text-slate-100 text-[13px]">{{ $order->delivery_slot }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Estimated by</span>
                        <span class="block font-bold text-slate-800 dark:text-slate-100 text-[13px]">{{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d M Y, h:i A') : 'Not set' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: customer, status, payment --}}
        <div class="space-y-5 min-w-0">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-user-circle"></i> Customer</h3>
                    @if($order->user)
                        <span class="badge badge-neutral">Registered</span>
                    @else
                        <span class="badge badge-neutral">Guest</span>
                    @endif
                </div>
                <div class="card-body space-y-4 text-[13px]">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl tone-slate flex items-center justify-center font-extrabold text-sm shrink-0">{{ mb_strtoupper(mb_substr($order->customer_name ?: 'C', 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 dark:text-white truncate">{{ $order->customer_name }}</p>
                            <a href="tel:{{ $order->customer_phone }}" class="text-[12.5px] font-semibold text-slate-500 dark:text-slate-400 hover:underline">+91 {{ $order->customer_phone }}</a>
                            @if($order->customer_email)
                                <a href="mailto:{{ $order->customer_email }}" class="block text-[12px] text-slate-400 hover:underline truncate">{{ $order->customer_email }}</a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Delivery address</span>
                            <button type="button" class="text-[11.5px] font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white inline-flex items-center gap-1" onclick="copyToClipboard(@js($fullAddress), 'Address copied')"><i class="ph ph-copy"></i> Copy</button>
                        </div>
                        <p class="leading-relaxed text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-100 dark:border-slate-700">{{ $fullAddress }}</p>
                    </div>

                    @if($hasPin)
                        <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="btn btn-outline w-full justify-center"><i class="ph-fill ph-map-pin text-rose-500"></i> Open GPS pin in Google Maps</a>
                        <p class="form-hint text-center -mt-2 font-mono">{{ $order->delivery_lat }}, {{ $order->delivery_lng }}</p>
                    @else
                        <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="btn btn-outline w-full justify-center"><i class="ph ph-map-trifold"></i> Search address on map</a>
                    @endif

                    @if($order->notes)
                        <div>
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Notes</span>
                            <p class="text-slate-700 dark:text-amber-100 bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-500/20 p-3 rounded-xl">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-truck"></i> Update order status</h3>
                </div>
                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="card-body">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">

                    <div>
                        <label for="orderStatusSelect" class="form-label">Status <span class="text-rose-500">*</span></label>
                        <select name="order_status" id="orderStatusSelect" class="form-select w-full">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing / packing</option>
                            <option value="out_for_delivery" {{ $order->order_status === 'out_for_delivery' ? 'selected' : '' }}>Out for delivery</option>
                            <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('order_status')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                        @if($order->payment_method === 'cod' && $order->payment_status !== 'paid')
                            <p class="form-hint">Marking a cash-on-delivery order as delivered also marks it paid.</p>
                        @endif
                    </div>

                    <div id="cancelReasonBox" class="{{ $order->order_status === 'cancelled' ? '' : 'hidden' }}">
                        <label for="cancellationReason" class="form-label !text-rose-700 dark:!text-rose-400">Cancellation reason</label>
                        <input type="text" id="cancellationReason" name="cancellation_reason" value="{{ old('cancellation_reason', $order->cancellation_reason) }}" placeholder="Customer requested, out of stock…" class="form-control">
                        <p class="form-hint">Cancelling returns all item quantities to stock.</p>
                        @error('cancellation_reason')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="orderNotes" class="form-label">Admin notes</label>
                        <textarea id="orderNotes" name="notes" rows="2" class="form-control !min-h-[72px]" placeholder="Internal note for this order">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full justify-center"><i class="ph ph-check-circle"></i> Update status</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ph-duotone ph-credit-card"></i> Payment</h3>
                    <span class="badge {{ $payClass }} badge-dot">{{ $payLabel }}</span>
                </div>
                <form action="{{ route('admin.orders.update_payment', $order) }}" method="POST" class="card-body">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">

                    <div class="flex items-center justify-between text-[13px] p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                        <span class="text-slate-500 dark:text-slate-400">Method</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $methodLabel }}</span>
                    </div>

                    <div>
                        <label for="paymentStatusSelect" class="form-label">Payment status <span class="text-rose-500">*</span></label>
                        <select id="paymentStatusSelect" name="payment_status" class="form-select w-full">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        @error('payment_status')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="transactionId" class="form-label">Transaction ID</label>
                        <input type="text" id="transactionId" name="transaction_id" value="{{ old('transaction_id', $order->transaction_id) }}" placeholder="UPI / gateway reference" class="form-control font-mono">
                        @error('transaction_id')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-outline w-full justify-center"><i class="ph ph-floppy-disk"></i> Update payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .order-stepper { display: grid; gap: 0; grid-template-columns: 1fr; list-style: none; margin: 0; padding: 0; }
    .order-step { position: relative; display: flex; align-items: flex-start; gap: .8rem; padding-bottom: 1.1rem; color: #94a3b8; }
    .order-step:last-child { padding-bottom: 0; }
    .order-step::before { content: ""; position: absolute; left: 19px; top: 40px; bottom: 0; width: 2px; background: #e2e8f0; }
    .order-step:last-child::before { display: none; }
    .order-step-dot { position: relative; z-index: 1; width: 40px; height: 40px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; background: #f1f5f9; color: #94a3b8; border: 2px solid #e2e8f0; transition: all .2s; }
    .order-step-text { padding-top: .25rem; min-width: 0; }
    .order-step.is-done { color: #334155; }
    .order-step.is-done .order-step-dot { background: #10b981; border-color: #10b981; color: #fff; }
    .order-step.is-done::before { background: #10b981; }
    .order-step.is-current { color: #0f172a; }
    .order-step.is-current .order-step-dot { background: var(--btn-primary-bg); border-color: var(--btn-primary-bg); color: var(--btn-primary-text); box-shadow: 0 0 0 5px rgb(var(--c-primary) / .14); }
    .dark .order-step { color: #64748b; }
    .dark .order-step::before { background: #334155; }
    .dark .order-step-dot { background: #1e293b; border-color: #334155; color: #64748b; }
    .dark .order-step.is-done { color: #cbd5e1; }
    .dark .order-step.is-done::before, .dark .order-step.is-done .order-step-dot { background: #059669; border-color: #059669; color: #fff; }
    .dark .order-step.is-current { color: #fff; }
    .dark .order-step.is-current .order-step-dot { background: #f8fafc; border-color: #f8fafc; color: #0f172a; box-shadow: 0 0 0 5px rgba(248,250,252,.12); }
    @media (min-width: 768px) {
        .order-stepper { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .order-step { flex-direction: column; align-items: center; text-align: center; padding-bottom: 0; gap: .55rem; }
        .order-step::before { left: calc(50% + 24px); right: calc(-50% + 24px); top: 19px; bottom: auto; width: auto; height: 2px; }
        .order-step-text { padding-top: 0; }
    }
</style>
@endpush

@push('scripts')
<script>
    $('#orderStatusSelect').on('change', function () {
        $('#cancelReasonBox').toggleClass('hidden', $(this).val() !== 'cancelled');
    });
</script>
@endpush
