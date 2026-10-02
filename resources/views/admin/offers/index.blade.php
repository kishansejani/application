@extends('admin.layouts.admin')

@section('title', 'Manage Offers & Coupons')
@section('page-title', 'Offers & Promo Codes')
@section('page-subtitle', 'Manage discount coupons, minimum cart conditions, and promotional banners')

@section('action-buttons')
<a href="{{ route('admin.offers.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
    <i class="fa-solid fa-plus"></i>
    <span>Create New Promo Offer</span>
</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table id="offersTable" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <th class="p-3">Coupon Code</th>
                    <th class="p-3">Title & Details</th>
                    <th class="p-3">Discount Value</th>
                    <th class="p-3">Min Order</th>
                    <th class="p-3">Used Count</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($offers as $offer)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3">
                            <span class="inline-block px-3 py-1 bg-amber-50 text-amber-900 border border-amber-300 font-mono font-extrabold text-xs rounded-xl shadow-sm tracking-wider">
                                {{ $offer->code }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-900 text-sm">{{ $offer->title_en }}</div>
                            <div class="text-xs text-brand-700 font-semibold">{{ $offer->title_gu }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $offer->description_en }}</div>
                        </td>
                        <td class="p-3">
                            @if($offer->discount_type === 'percentage')
                                <span class="font-extrabold text-brand-700 text-sm">{{ $offer->discount_value }}% OFF</span>
                                @if($offer->max_discount_amount)
                                    <span class="block text-[10px] text-slate-400">Max ₹{{ number_format($offer->max_discount_amount, 2) }}</span>
                                @endif
                            @else
                                <span class="font-extrabold text-brand-700 text-sm">₹{{ number_format($offer->discount_value, 2) }} FLAT</span>
                            @endif
                        </td>
                        <td class="p-3 font-semibold text-slate-700">
                            ₹{{ number_format($offer->min_order_amount, 2) }}
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                {{ $offer->used_count }} {{ $offer->usage_limit ? '/ ' . $offer->usage_limit : 'times' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <button onclick="toggleOfferStatus({{ $offer->id }}, this)" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all {{ $offer->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $offer->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="confirm-delete-btn p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">No promo offers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#offersTable').DataTable({
            responsive: true,
            pageLength: 10
        });
    });

    function toggleOfferStatus(id, btn) {
        $.ajax({
            url: `/admin/offers/${id}/toggle-status`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_active) {
                        $(btn).removeClass('bg-slate-100 text-slate-500').addClass('bg-emerald-100 text-emerald-800').text('Active');
                        toastr.success('Offer activated');
                    } else {
                        $(btn).removeClass('bg-emerald-100 text-emerald-800').addClass('bg-slate-100 text-slate-500').text('Inactive');
                        toastr.info('Offer deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
