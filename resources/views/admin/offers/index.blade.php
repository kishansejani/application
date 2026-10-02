@extends('admin.layouts.admin')

@section('title', 'Offers')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Offers & Coupons</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage promotional discount codes, percentage cuts, and minimum order rules.</p>
        </div>
        <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-black text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 rounded-2xl text-xs font-bold shadow-md transition active:scale-95">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Create New Offer</span>
        </a>
    </div>

    <!-- Table Card Container -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm p-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="offersTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="px-4 py-3.5 rounded-l-2xl">Coupon Code</th>
                        <th class="px-4 py-3.5">Title & Info</th>
                        <th class="px-4 py-3.5">Discount Value</th>
                        <th class="px-4 py-3.5">Min Order</th>
                        <th class="px-4 py-3.5">Usage</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right rounded-r-2xl">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($offers as $offer)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5">
                                <span class="inline-block px-3 py-1.5 bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800 font-mono font-black text-xs rounded-xl shadow-sm tracking-wider">
                                    {{ $offer->code }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white text-sm leading-tight">{{ $offer->title_en }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">{{ $offer->title_gu }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $offer->description_en }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($offer->discount_type === 'percentage')
                                    <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm">{{ $offer->discount_value }}% OFF</span>
                                    @if($offer->max_discount_amount)
                                        <span class="block text-[10px] text-slate-400">Max ₹{{ number_format($offer->max_discount_amount, 2) }}</span>
                                    @endif
                                @else
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 text-sm">₹{{ number_format($offer->discount_value, 2) }} FLAT</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-700 dark:text-slate-300">
                                ₹{{ number_format($offer->min_order_amount, 2) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs">
                                    {{ $offer->used_count }} {{ $offer->usage_limit ? '/ ' . $offer->usage_limit : 'times' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <button onclick="toggleOfferStatus({{ $offer->id }}, this)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-sm border {{ $offer->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700' }}">
                                    <span class="w-2 h-2 rounded-full {{ $offer->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    <span>{{ $offer->is_active ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Edit Offer">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="confirm-delete-btn w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white flex items-center justify-center transition shadow-sm" title="Delete Offer">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
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
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#offersTable').DataTable();
    });

    function toggleOfferStatus(id, btn) {
        $.ajax({
            url: `/admin/offers/${id}/toggle-status`,
            type: 'PATCH',
            success: function(res) {
                if (res.success) {
                    if (res.is_active) {
                        $(btn).removeClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .addClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .html('<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span><span>Active</span>');
                        toastr.success('Offer activated');
                    } else {
                        $(btn).removeClass('bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800')
                              .addClass('bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700')
                              .html('<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Inactive</span>');
                        toastr.info('Offer deactivated');
                    }
                }
            }
        });
    }
</script>
@endpush
