@extends('admin.layouts.admin')

@section('title', 'Create Promo Offer')
@section('page-title', 'Create New Promo Coupon')
@section('page-subtitle', 'Configure promo codes, percentage or flat discounts, and validity limits')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.offers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- English Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Offer Title (English) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title_en" value="{{ old('title_en') }}" required placeholder="e.g. Flat 20% OFF on Fresh Grocery" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Gujarati Title -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                    Offer Title (ગુજરાતી) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title_gu" value="{{ old('title_gu') }}" required placeholder="દા.ત. તાજી કરિયાણા પર ફ્લેટ ૨૦% છૂટ" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Coupon Code -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Coupon Code (Uppercase) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. FRESH20, FESTIVE50" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono font-bold uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Discount Type -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Discount Type</label>
                <select name="discount_type" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="percentage">Percentage Discount (%)</option>
                    <option value="flat">Flat Amount (₹)</option>
                </select>
            </div>

            <!-- Discount Value -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Discount Value <span class="text-rose-500">*</span>
                </label>
                <input type="number" step="0.01" name="discount_value" value="{{ old('discount_value') }}" required placeholder="e.g. 20 (for 20%) or 50 (for ₹50)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Minimum Order Amount -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Minimum Cart Amount (₹)</label>
                <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" placeholder="0.00" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Max Discount Cap (for Percentage) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Max Discount Cap (₹ Optional)</label>
                <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount') }}" placeholder="Leave blank for no limit" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Usage Limit -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Total Usage Limit</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="e.g. 500 orders" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Valid From -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Valid From</label>
                <input type="datetime-local" name="valid_from" value="{{ old('valid_from') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Valid To -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Valid Until</label>
                <input type="datetime-local" name="valid_to" value="{{ old('valid_to') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Drag & Drop Banner Upload -->
        <div class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Offer Banner Image (Drag & Drop or Browse)
            </label>
            <div id="offerDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                <input type="file" id="offerFileInput" name="banner_file" accept="image/*" class="hidden">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-700">Drop offer banner here, or <span class="text-brand-600 underline">browse</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">Supports PNG, JPG, WEBP</p>
                    </div>
                </div>
                <div id="offerPreview" class="mt-4 flex justify-center"></div>
            </div>
            <div class="mt-2 text-center text-xs text-slate-400 font-medium">OR Image URL:</div>
            <input type="url" name="banner_url" value="{{ old('banner_url') }}" placeholder="https://..." class="mt-1.5 w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
            <!-- English Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (English)</label>
                <textarea name="description_en" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_en') }}</textarea>
            </div>

            <!-- Gujarati Description -->
            <div>
                <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Description (ગુજરાતી)</label>
                <textarea name="description_gu" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_gu') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-6 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Active</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.offers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 btn-theme-primary font-bold rounded-xl text-xs shadow-md flex items-center gap-2 transition active:scale-95">
                <i class="fas fa-check"></i>
                <span>Save Offer</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropArea = document.getElementById('offerDropzone');
        const fileInput = document.getElementById('offerFileInput');
        dropArea.addEventListener('click', () => fileInput.click());
        initDragAndDropUploader('offerDropzone', 'offerFileInput', 'offerPreview');
    });
</script>
@endpush
