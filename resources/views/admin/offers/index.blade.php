@extends('admin.layouts.admin')

@section('title', 'Offers')

@section('content')
    <x-admin.page-header title="Offers & coupons" subtitle="Manage promo codes, percentage or flat discounts, minimum order rules and validity." icon="ticket">
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Create offer</a>
    </x-admin.page-header>

    @php $noFilter = !request('status'); @endphp
    <div class="stat-grid cols-5">
        <x-admin.stat-card label="All offers" :value="$stats['total'] ?? $offers->count()" icon="ticket" tone="slate"
            :href="route('admin.offers.index')" :active="$noFilter" meta="Coupon campaigns" />
        <x-admin.stat-card label="Active" :value="$stats['active'] ?? 0" icon="check-circle" tone="emerald"
            :href="route('admin.offers.index', ['status' => 'active'])" :active="request('status') === 'active'" meta="Redeemable at checkout" />
        <x-admin.stat-card label="Inactive" :value="$stats['inactive'] ?? 0" icon="pause-circle" tone="rose"
            :href="route('admin.offers.index', ['status' => 'inactive'])" :active="request('status') === 'inactive'" meta="Paused or switched off" />
        <x-admin.stat-card label="Percentage deals" :value="$stats['percentage'] ?? 0" icon="percent" tone="blue" meta="% off the cart" />
        <x-admin.stat-card label="Flat discounts" :value="$stats['flat'] ?? 0" icon="currency-inr" tone="amber" meta="Fixed ₹ off" class="col-span-2 md:col-span-1" />
    </div>

    <div class="card table-card">
        <div class="filter-bar">
            <form action="{{ route('admin.offers.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5" data-no-loading>
                <select name="status" onchange="this.form.submit()" class="form-select w-auto min-w-[10rem]" aria-label="Status">
                    <option value="">All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @if(!$noFilter)
                    <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost btn-sm text-rose-600"><i class="ph ph-x-circle"></i> Clear filters</a>
                @endif
            </form>
            <span class="ml-auto text-xs font-semibold text-slate-500">{{ $offers->count() }} {{ \Illuminate\Support\Str::plural('offer', $offers->count()) }}</span>
        </div>

        <table id="offersTable" class="w-full" data-export-title="Offers and coupons">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Offer</th>
                    <th>Discount</th>
                    <th>Min. order</th>
                    <th>Validity</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offers as $offer)
                    @php
                        $isPct = $offer->discount_type === 'percentage';
                        $discountText = $isPct
                            ? rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.').'% off'.($offer->max_discount_amount ? ' (max ₹'.number_format($offer->max_discount_amount, 2).')' : '')
                            : '₹'.number_format($offer->discount_value, 2).' flat off';
                        $expired = $offer->valid_to && $offer->valid_to->isPast();
                        $upcoming = $offer->valid_from && $offer->valid_from->isFuture();
                        $validityText = $offer->valid_from || $offer->valid_to
                            ? ($offer->valid_from ? $offer->valid_from->format('d M Y') : 'Any time').' – '.($offer->valid_to ? $offer->valid_to->format('d M Y') : 'No end date')
                            : 'Always valid';
                        $usageText = $offer->used_count.($offer->usage_limit ? ' / '.$offer->usage_limit : ' used');
                        $usagePct = $offer->usage_limit ? min(100, round($offer->used_count / max($offer->usage_limit, 1) * 100)) : null;
                    @endphp
                    <tr>
                        <td data-export="{{ $offer->code }}">
                            <button type="button" onclick="copyToClipboard('{{ $offer->code }}', 'Coupon code copied')" class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-dashed border-amber-300 bg-amber-50 text-amber-900 dark:bg-amber-500/10 dark:border-amber-500/40 dark:text-amber-200 font-mono font-bold text-[12.5px] tracking-wider" title="Copy code">
                                {{ $offer->code }} <i class="ph ph-copy text-amber-600 dark:text-amber-300 opacity-60 group-hover:opacity-100"></i>
                            </button>
                        </td>
                        <td data-export="{{ $offer->title_en }}{{ $offer->title_gu ? ' / '.$offer->title_gu : '' }}">
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($offer->banner_url)
                                    <img src="{{ $offer->banner_url }}" alt="" class="thumb thumb-lg" loading="lazy">
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="block font-bold text-slate-900 dark:text-white hover:underline leading-snug">{{ $offer->title_en }}</a>
                                    @if($offer->title_gu)<span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5" lang="gu">{{ $offer->title_gu }}</span>@endif
                                    @if($offer->description_en)<span class="block text-[11px] text-slate-400 mt-0.5 max-w-xs truncate">{{ $offer->description_en }}</span>@endif
                                </div>
                            </div>
                        </td>
                        <td data-order="{{ $offer->discount_value }}" data-export="{{ $discountText }}" class="whitespace-nowrap">
                            <span class="badge {{ $isPct ? 'badge-info' : 'badge-success' }}">
                                <i class="ph-bold {{ $isPct ? 'ph-percent' : 'ph-currency-inr' }}"></i>
                                {{ $isPct ? rtrim(rtrim(number_format($offer->discount_value, 2), '0'), '.').'% off' : '₹'.number_format($offer->discount_value, 0).' off' }}
                            </span>
                            @if($isPct && $offer->max_discount_amount)
                                <span class="block text-[11px] text-slate-400 mt-1">Max ₹{{ number_format($offer->max_discount_amount, 2) }}</span>
                            @endif
                        </td>
                        <td data-order="{{ $offer->min_order_amount }}" data-export="₹{{ number_format($offer->min_order_amount, 2) }}" class="whitespace-nowrap font-semibold text-slate-700 dark:text-slate-200">
                            {{ $offer->min_order_amount > 0 ? '₹'.number_format($offer->min_order_amount, 2) : 'No minimum' }}
                        </td>
                        <td data-order="{{ $offer->valid_to ? $offer->valid_to->timestamp : 9999999999 }}" data-export="{{ $validityText }}{{ $expired ? ' (expired)' : ($upcoming ? ' (scheduled)' : '') }}" class="whitespace-nowrap">
                            <span class="block text-[12.5px] font-semibold text-slate-700 dark:text-slate-200">{{ $validityText }}</span>
                            @if($expired)
                                <span class="badge badge-danger mt-1">Expired</span>
                            @elseif($upcoming)
                                <span class="badge badge-warning mt-1">Scheduled</span>
                            @endif
                        </td>
                        <td data-order="{{ $offer->used_count }}" data-export="{{ $usageText }}">
                            <span class="text-[12.5px] font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">{{ $usageText }}</span>
                            @if(!is_null($usagePct))
                                <span class="block w-24 h-1.5 mt-1.5 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden"><span class="block h-full rounded-full {{ $usagePct >= 90 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $usagePct }}%"></span></span>
                            @endif
                        </td>
                        <td data-order="{{ $offer->is_active ? 1 : 0 }}" data-export="{{ $offer->is_active ? 'Active' : 'Inactive' }}">
                            <button type="button" onclick="toggleOfferStatus({{ $offer->id }}, this)" class="switch {{ $offer->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $offer->is_active ? 'true' : 'false' }}">
                                <span class="switch-track"></span><span class="switch-text">{{ $offer->is_active ? 'Active' : 'Inactive' }}</span>
                            </button>
                        </td>
                        <td class="text-right">
                            <div class="act justify-end">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="act-btn is-edit" title="Edit"><i class="ph ph-pencil-simple-line"></i></a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="act-btn is-danger confirm-delete-btn" data-confirm-title="Delete coupon “{{ $offer->code }}”?" title="Delete"><i class="ph ph-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#offersTable').DataTable({
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, responsivePriority: 1 },
                { targets: 1, responsivePriority: 2 },
                { targets: 2, responsivePriority: 3 },
                { targets: 6, responsivePriority: 4 },
                { targets: 7, responsivePriority: 5 }
            ]
        });
    });

    function toggleOfferStatus(id, btn) {
        $.ajax({ url: `/admin/offers/${id}/toggle-status`, type: 'PATCH' })
            .done(function (res) {
                if (!res.success) return;
                const on = !!res.is_active;
                $(btn).toggleClass('is-on', on).attr('aria-checked', on ? 'true' : 'false').find('.switch-text').text(on ? 'Active' : 'Inactive');
                $(btn).closest('td').attr('data-export', on ? 'Active' : 'Inactive').attr('data-order', on ? 1 : 0);
                toastr[on ? 'success' : 'info'](on ? 'Offer activated' : 'Offer deactivated');
            })
            .fail(function () { toastr.error('Could not update the offer. Please try again.'); });
    }
</script>
@endpush
