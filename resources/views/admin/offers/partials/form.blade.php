{{-- Shared offer form fields. Expects optional $offer (edit) — null on create. --}}
@php
    $offer = $offer ?? null;
    $fmtDt = fn ($d) => $d ? $d->format('Y-m-d\TH:i') : '';
    $activeDefault = $offer ? (bool) $offer->is_active : true;
    $isActive = old('_token') ? (bool) old('is_active') : $activeDefault;
    $discountType = old('discount_type', $offer->discount_type ?? 'percentage');
    $bannerUrlValue = old('banner_url', $offer && filter_var($offer->banner_image, FILTER_VALIDATE_URL) ? $offer->banner_image : '');
    $err = fn ($f) => $errors->has($f) ? ' !border-rose-400' : '';
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
    {{-- Main column --}}
    <div class="xl:col-span-2 space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-text-align-left"></i> Offer details</h3>
                    <p class="card-subtitle">Shown to customers on the offers page and at checkout.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="title_en" class="form-label">Title (English) <span class="text-rose-500">*</span></label>
                    <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $offer->title_en ?? '') }}" required placeholder="e.g. Flat 20% off on fresh groceries" class="form-control{{ $err('title_en') }}" data-preview="title_en">
                    @error('title_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="title_gu" class="form-label">Title (ગુજરાતી) <span class="text-rose-500">*</span></label>
                    <input type="text" id="title_gu" name="title_gu" lang="gu" value="{{ old('title_gu', $offer->title_gu ?? '') }}" required placeholder="દા.ત. તાજી કરિયાણા પર ફ્લેટ ૨૦% છૂટ" class="form-control{{ $err('title_gu') }}" data-preview="title_gu">
                    @error('title_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_en" class="form-label">Description (English)</label>
                    <textarea id="description_en" name="description_en" rows="3" class="form-control{{ $err('description_en') }}" placeholder="Short terms or a description of the offer" data-preview="description_en">{{ old('description_en', $offer->description_en ?? '') }}</textarea>
                    @error('description_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_gu" class="form-label">Description (ગુજરાતી)</label>
                    <textarea id="description_gu" name="description_gu" lang="gu" rows="3" class="form-control{{ $err('description_gu') }}">{{ old('description_gu', $offer->description_gu ?? '') }}</textarea>
                    @error('description_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-ticket"></i> Coupon &amp; discount</h3>
                    <p class="card-subtitle">Codes are saved in upper case and must be unique.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="couponCode" class="form-label">Coupon code <span class="text-rose-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="couponCode" name="code" value="{{ old('code', $offer->code ?? '') }}" required maxlength="50" autocomplete="off" spellcheck="false" placeholder="e.g. FRESH20" class="form-control font-mono font-bold uppercase tracking-wider placeholder:normal-case placeholder:tracking-normal placeholder:font-sans placeholder:font-normal{{ $err('code') }}">
                        <button type="button" id="generateCodeBtn" class="btn btn-outline shrink-0" title="Generate a code from the title and discount"><i class="ph ph-shuffle"></i> Generate</button>
                    </div>
                    <p class="form-hint">Letters and numbers only work best. Generate creates a code from the title and discount value.</p>
                    @error('code')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="discountType" class="form-label">Discount type <span class="text-rose-500">*</span></label>
                    <select id="discountType" name="discount_type" class="form-select w-full{{ $err('discount_type') }}">
                        <option value="percentage" {{ $discountType === 'percentage' ? 'selected' : '' }}>Percentage discount (%)</option>
                        <option value="flat" {{ $discountType === 'flat' ? 'selected' : '' }}>Flat amount (₹)</option>
                    </select>
                    @error('discount_type')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="discountValue" class="form-label">Discount value <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span id="discountUnit" class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400 pointer-events-none">{{ $discountType === 'flat' ? '₹' : '%' }}</span>
                        <input type="number" step="0.01" min="0" id="discountValue" name="discount_value" value="{{ old('discount_value', $offer->discount_value ?? '') }}" required placeholder="20" class="form-control !pl-8 font-bold{{ $err('discount_value') }}">
                    </div>
                    @error('discount_value')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="minOrder" class="form-label">Minimum cart amount (₹)</label>
                    <input type="number" step="0.01" min="0" id="minOrder" name="min_order_amount" value="{{ old('min_order_amount', $offer->min_order_amount ?? 0) }}" placeholder="0.00" class="form-control{{ $err('min_order_amount') }}">
                    @error('min_order_amount')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="maxDiscountWrap">
                    <label for="maxDiscount" class="form-label">Maximum discount (₹)</label>
                    <input type="number" step="0.01" min="0" id="maxDiscount" name="max_discount_amount" value="{{ old('max_discount_amount', $offer->max_discount_amount ?? '') }}" placeholder="No limit" class="form-control{{ $err('max_discount_amount') }}">
                    <p class="form-hint">Caps percentage discounts. Leave blank for no limit.</p>
                    @error('max_discount_amount')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="usageLimit" class="form-label">Total usage limit</label>
                    <input type="number" min="1" id="usageLimit" name="usage_limit" value="{{ old('usage_limit', $offer->usage_limit ?? '') }}" placeholder="Unlimited" class="form-control{{ $err('usage_limit') }}">
                    @if($offer)
                        <p class="form-hint">Used {{ $offer->used_count }} {{ \Illuminate\Support\Str::plural('time', $offer->used_count) }} so far.</p>
                    @endif
                    @error('usage_limit')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-calendar-check"></i> Validity</h3>
                    <p class="card-subtitle">Leave blank to keep the coupon valid indefinitely.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="validFrom" class="form-label">Valid from</label>
                    <input type="datetime-local" id="validFrom" name="valid_from" value="{{ old('valid_from', $fmtDt($offer->valid_from ?? null)) }}" class="form-control{{ $err('valid_from') }}">
                    @error('valid_from')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="validTo" class="form-label">Valid until</label>
                    <input type="datetime-local" id="validTo" name="valid_to" value="{{ old('valid_to', $fmtDt($offer->valid_to ?? null)) }}" class="form-control{{ $err('valid_to') }}">
                    @error('valid_to')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5 min-w-0 xl:sticky xl:top-20">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Live preview</h3>
                <span class="badge badge-neutral">Customer view</span>
            </div>
            <div class="card-body">
                <div class="coupon-preview" id="couponPreview">
                    <div class="coupon-left">
                        <span class="coupon-amount" id="pvAmount">20% OFF</span>
                        <span class="coupon-cap" id="pvCap"></span>
                    </div>
                    <div class="coupon-right">
                        <p class="coupon-title" id="pvTitle">Offer title</p>
                        <p class="coupon-title-gu" id="pvTitleGu" lang="gu"></p>
                        <p class="coupon-desc" id="pvDesc"></p>
                        <div class="coupon-code-row">
                            <span class="coupon-code" id="pvCode">CODE</span>
                            <span class="coupon-status" id="pvStatus">Active</span>
                        </div>
                        <p class="coupon-terms" id="pvTerms"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-toggle-right"></i> Status</h3>
            </div>
            <div class="card-body">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="isActive" name="is_active" value="1" {{ $isActive ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>
                        <span class="block text-[13px] font-bold text-slate-800 dark:text-slate-100">Active</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">Customers can apply this code at checkout.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-image"></i> Banner image</h3>
                    <p class="card-subtitle">Optional · PNG, JPG or WEBP up to 4 MB</p>
                </div>
            </div>
            <div class="card-body space-y-3">
                @if($offer && $offer->banner_image)
                    <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                        <img src="{{ $offer->banner_url }}" alt="" class="w-24 h-14 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Current banner. Upload a file or enter a URL to replace it.</span>
                    </div>
                @endif
                <input type="file" id="offerFileInput" name="banner_file" accept="image/*" class="hidden">
                <div id="offerDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 dark:bg-slate-800/60 cursor-pointer" role="button" tabindex="0" aria-label="Upload banner image">
                    <div class="flex flex-col items-center justify-center gap-1.5">
                        <span class="w-10 h-10 rounded-full tone-emerald flex items-center justify-center text-lg"><i class="ph ph-cloud-arrow-up"></i></span>
                        <p class="text-[13px] font-bold text-slate-700 dark:text-slate-200">Drop a banner here, or <span class="underline">browse</span></p>
                    </div>
                    <div id="offerPreview" class="mt-3 flex justify-center empty:hidden"></div>
                </div>
                @error('banner_file')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                <div>
                    <label for="bannerUrl" class="form-label">Or image URL</label>
                    <input type="url" id="bannerUrl" name="banner_url" value="{{ $bannerUrlValue }}" placeholder="https://…" class="form-control{{ $err('banner_url') }}">
                    @error('banner_url')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body flex gap-2.5">
                <a href="{{ route('admin.offers.index') }}" class="btn btn-outline flex-1 justify-center">Cancel</a>
                <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="ph ph-check"></i> {{ $offer ? 'Update offer' : 'Save offer' }}</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .coupon-preview { display: flex; border-radius: 16px; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 10px 24px -14px rgba(15,23,42,.35); }
    .coupon-left { position: relative; flex: 0 0 104px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 16px 10px; text-align: center; color: #fff; background: linear-gradient(160deg, #059669, #047857); }
    .coupon-preview.is-flat .coupon-left { background: linear-gradient(160deg, #f59e0b, #d97706); }
    .coupon-preview.is-off .coupon-left { background: linear-gradient(160deg, #94a3b8, #64748b); }
    .coupon-left::after { content: ""; position: absolute; right: -7px; top: 8px; bottom: 8px; width: 14px; background: radial-gradient(circle at 7px 7px, #fff 5px, transparent 5.5px) 0 0 / 14px 16px repeat-y; }
    .coupon-amount { font-size: 21px; font-weight: 800; line-height: 1.05; letter-spacing: -.02em; word-break: break-word; }
    .coupon-cap { font-size: 10.5px; font-weight: 600; opacity: .9; }
    .coupon-right { flex: 1; min-width: 0; padding: 14px 14px 14px 18px; }
    .coupon-title { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; line-height: 1.3; word-break: break-word; }
    .coupon-title-gu { margin: 2px 0 0; font-size: 12px; font-weight: 600; color: #047857; font-family: 'Hind Vadodara', sans-serif; }
    .coupon-title-gu:empty, .coupon-desc:empty, .coupon-cap:empty { display: none; }
    .coupon-desc { margin: 4px 0 0; font-size: 11.5px; color: #64748b; line-height: 1.45; }
    .coupon-code-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; }
    .coupon-code { display: inline-block; padding: 4px 10px; border: 1.5px dashed #f59e0b; border-radius: 8px; background: #fffbeb; color: #78350f; font: 800 13px/1.3 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; letter-spacing: .08em; word-break: break-all; }
    .coupon-status { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #059669; white-space: nowrap; }
    .coupon-preview.is-off .coupon-status { color: #94a3b8; }
    .coupon-terms { margin: 8px 0 0; font-size: 10.5px; color: #94a3b8; line-height: 1.5; }
    .dark .coupon-preview { background: #0f172a; border-color: #334155; }
    .dark .coupon-left::after { background: radial-gradient(circle at 7px 7px, #0f172a 5px, transparent 5.5px) 0 0 / 14px 16px repeat-y; }
    .dark .coupon-title { color: #f8fafc; }
    .dark .coupon-title-gu { color: #6ee7b7; }
    .dark .coupon-code { background: rgba(245,158,11,.12); color: #fde68a; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const $ = (id) => document.getElementById(id);
        const dropArea = $('offerDropzone');
        const fileInput = $('offerFileInput');
        dropArea.addEventListener('click', () => fileInput.click());
        dropArea.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });
        initDragAndDropUploader('offerDropzone', 'offerFileInput', 'offerPreview');

        const inr = (n) => '₹' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });
        const fmtDate = (v) => { if (!v) return ''; const d = new Date(v); return isNaN(d) ? '' : d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); };

        function updatePreview() {
            const type = $('discountType').value;
            const val = parseFloat($('discountValue').value);
            const cap = parseFloat($('maxDiscount').value);
            const min = parseFloat($('minOrder').value);
            const limit = parseInt($('usageLimit').value, 10);
            const active = $('isActive').checked;
            const isPct = type === 'percentage';

            $('discountUnit').textContent = isPct ? '%' : '₹';
            $('maxDiscountWrap').style.opacity = isPct ? '1' : '.55';

            $('pvAmount').textContent = isNaN(val) ? (isPct ? '–% OFF' : '₹– OFF') : (isPct ? (+val.toFixed(2)) + '% OFF' : inr(val) + ' OFF');
            $('pvCap').textContent = isPct && !isNaN(cap) && cap > 0 ? 'Up to ' + inr(cap) : (isPct ? '' : 'Flat discount');
            $('pvTitle').textContent = $('title_en').value.trim() || 'Offer title';
            $('pvTitleGu').textContent = $('title_gu').value.trim();
            $('pvDesc').textContent = $('description_en').value.trim();
            $('pvCode').textContent = ($('couponCode').value.trim() || 'CODE').toUpperCase();
            $('pvStatus').textContent = active ? 'Active' : 'Inactive';

            const terms = [];
            terms.push(!isNaN(min) && min > 0 ? 'Min. order ' + inr(min) : 'No minimum order');
            const from = fmtDate($('validFrom').value), to = fmtDate($('validTo').value);
            if (from && to) terms.push(from + ' – ' + to); else if (to) terms.push('Valid till ' + to); else if (from) terms.push('Starts ' + from);
            if (!isNaN(limit) && limit > 0) terms.push('First ' + limit + ' uses');
            $('pvTerms').textContent = terms.join(' · ');

            const card = $('couponPreview');
            card.classList.toggle('is-flat', !isPct);
            card.classList.toggle('is-off', !active);
        }

        $('couponCode').addEventListener('input', function () {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase().replace(/\s+/g, '');
            try { this.setSelectionRange(pos, pos); } catch (e) {}
        });

        $('generateCodeBtn').addEventListener('click', function () {
            const words = ($('title_en').value || '').toUpperCase().replace(/[^A-Z\s]/g, ' ').split(/\s+/).filter(w => w.length > 2 && !['OFF', 'THE', 'AND', 'FOR', 'FLAT', 'ALL'].includes(w));
            let base = (words[0] || 'SAVE').slice(0, 6);
            const val = parseFloat($('discountValue').value);
            const num = isNaN(val) ? '' : String(Math.round(val));
            const chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
            let rand = '';
            for (let i = 0; i < 3; i++) rand += chars[Math.floor(Math.random() * chars.length)];
            $('couponCode').value = (base + num + rand).slice(0, 20);
            $('couponCode').dispatchEvent(new Event('input'));
            updatePreview();
            if (window.toastr) toastr.info('Generated code ' + $('couponCode').value);
        });

        ['title_en', 'title_gu', 'description_en', 'couponCode', 'discountValue', 'maxDiscount', 'minOrder', 'usageLimit', 'validFrom', 'validTo']
            .forEach(id => $(id).addEventListener('input', updatePreview));
        ['discountType', 'isActive'].forEach(id => $(id).addEventListener('change', updatePreview));
        updatePreview();
    });
</script>
@endpush
