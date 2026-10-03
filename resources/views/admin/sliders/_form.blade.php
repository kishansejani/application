{{-- Shared create/edit form for home sliders. Expects $categories, $products, $offers and optional $slider. --}}
@php
    $slider = $slider ?? null;
    $isEdit = (bool) $slider;
    $isActive = $errors->any() ? (bool) old('is_active') : ($slider->is_active ?? true);
    $linkType = old('link_type', $slider->link_type ?? 'none');
    $targetId = old('target_id', $slider->target_id ?? '');
    $err = 'text-xs font-semibold text-rose-600 mt-1';
    $categories = $categories ?? collect();
    $products = $products ?? collect();
    $offers = $offers ?? collect();
@endphp

<form action="{{ $isEdit ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data"
      class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="space-y-5 min-w-0">
        {{-- Live preview --}}
        <div class="card overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-monitor"></i> Live preview</h3>
                    <p class="card-subtitle">Approximate look of the banner on the home page.</p>
                </div>
                <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold" role="tablist" aria-label="Preview language">
                    <button type="button" class="prev-lang px-3 py-1.5 rounded-lg bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white" data-lang="en" aria-pressed="true">English</button>
                    <button type="button" class="prev-lang px-3 py-1.5 rounded-lg text-slate-500 dark:text-slate-400" data-lang="gu" aria-pressed="false">ગુજરાતી</button>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <div id="bannerPreview" class="relative w-full aspect-[12/5] min-h-[170px] rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-800 bg-cover bg-center"
                     style="{{ $isEdit && $slider->image ? 'background-image:url(' . e($slider->image_url) . ')' : '' }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/35 to-transparent"></div>
                    <div id="bpEmpty" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 {{ $isEdit && $slider->image ? 'hidden' : '' }}">
                        <i class="ph-duotone ph-image text-4xl"></i>
                        <span class="text-xs font-semibold mt-1">Add a banner image to preview</span>
                    </div>
                    <div class="relative h-full flex flex-col justify-center gap-1.5 sm:gap-2.5 p-5 sm:p-8 max-w-[75%]">
                        <span id="bpBadge" class="self-start inline-flex px-2.5 py-1 rounded-full bg-amber-400 text-amber-950 text-[10px] sm:text-xs font-extrabold hidden"></span>
                        <h4 id="bpTitle" class="text-white text-lg sm:text-3xl font-extrabold leading-tight drop-shadow"></h4>
                        <p id="bpSubtitle" class="text-white/85 text-xs sm:text-sm font-medium"></p>
                        <span id="bpCta" class="self-start mt-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-slate-900 text-[11px] sm:text-xs font-bold hidden">Shop now <i class="ph-bold ph-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-text-aa"></i> Banner text</h3>
                    <p class="card-subtitle">All text is optional — leave blank for an image-only banner.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach([
                    ['title', 'Title', 'e.g. Farm-fresh fruits & vegetables', 'દા.ત. ખેતરમાંથી સીધા તાજા ફળો અને શાકભાજી'],
                    ['subtitle', 'Subtitle', 'e.g. Express 2-hour delivery before 12 PM', 'દા.ત. બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો'],
                    ['badge', 'Badge text', 'e.g. 30% off today', 'દા.ત. આજે ૩૦% સુધી છૂટ'],
                ] as [$key, $label, $phEn, $phGu])
                    <div>
                        <label for="{{ $key }}_en" class="form-label">{{ $label }} (English)</label>
                        <input type="text" id="{{ $key }}_en" name="{{ $key }}_en" value="{{ old($key.'_en', $slider->{$key.'_en'} ?? '') }}" placeholder="{{ $phEn }}" class="form-control bp-field" data-bp="{{ $key }}" data-lang="en">
                        @error($key.'_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="{{ $key }}_gu" class="form-label">{{ $label }} (ગુજરાતી)</label>
                        <input type="text" id="{{ $key }}_gu" name="{{ $key }}_gu" lang="gu" value="{{ old($key.'_gu', $slider->{$key.'_gu'} ?? '') }}" placeholder="{{ $phGu }}" class="form-control bp-field" data-bp="{{ $key }}" data-lang="gu">
                        @error($key.'_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-link-simple"></i> Click destination</h3>
                    <p class="card-subtitle">Where customers go when they tap the banner.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="link_type" class="form-label">Link type</label>
                    <select id="link_type" name="link_type" class="form-select w-full">
                        <option value="none" {{ $linkType === 'none' ? 'selected' : '' }}>No link (display only)</option>
                        <option value="category" {{ $linkType === 'category' ? 'selected' : '' }}>Open a category</option>
                        <option value="product" {{ $linkType === 'product' ? 'selected' : '' }}>Open a product</option>
                        <option value="offer" {{ $linkType === 'offer' ? 'selected' : '' }}>Open an offer</option>
                        <option value="custom" {{ $linkType === 'custom' ? 'selected' : '' }}>Custom URL</option>
                    </select>
                    @error('link_type')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <div class="link-target" data-for="none">
                        <span class="form-label">Target</span>
                        <p class="h-[42px] flex items-center px-3.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-sm text-slate-400">Banner is not clickable</p>
                    </div>
                    <div class="link-target hidden" data-for="category">
                        <label for="target_category" class="form-label">Category</label>
                        <select id="target_category" name="target_id" class="form-select w-full" disabled>
                            <option value="">Choose a category</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}" {{ $linkType === 'category' && (string) $targetId === (string) $c->id ? 'selected' : '' }}>{{ $c->name_en }}</option>@endforeach
                        </select>
                    </div>
                    <div class="link-target hidden" data-for="product">
                        <label for="target_product" class="form-label">Product</label>
                        <select id="target_product" name="target_id" class="form-select w-full" disabled>
                            <option value="">Choose a product</option>
                            @foreach($products as $p)<option value="{{ $p->id }}" {{ $linkType === 'product' && (string) $targetId === (string) $p->id ? 'selected' : '' }}>{{ $p->name_en }} ({{ $p->unit }})</option>@endforeach
                        </select>
                    </div>
                    <div class="link-target hidden" data-for="offer">
                        <label for="target_offer" class="form-label">Offer</label>
                        <select id="target_offer" name="target_id" class="form-select w-full" disabled>
                            <option value="">Choose an offer</option>
                            @foreach($offers as $o)<option value="{{ $o->id }}" {{ $linkType === 'offer' && (string) $targetId === (string) $o->id ? 'selected' : '' }}>{{ $o->title_en }}{{ $o->code ? ' · '.$o->code : '' }}</option>@endforeach
                        </select>
                    </div>
                    <div class="link-target hidden" data-for="custom">
                        <label for="link_url" class="form-label">URL</label>
                        <input type="text" id="link_url" name="link_url" value="{{ old('link_url', $slider->link_url ?? '') }}" placeholder="https://… or /products" class="form-control" disabled>
                    </div>
                    @error('target_id')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    @error('link_url')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-5 min-w-0 xl:sticky xl:top-[84px]">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Publishing</h3>
            </div>
            <div class="card-body space-y-4">
                @include('admin.categories._toggle', ['name' => 'is_active', 'checked' => $isActive, 'label' => 'Active', 'hint' => 'Show this banner in the home page slider.'])
                <div>
                    <label for="sort_order" class="form-label">Display order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $slider->sort_order ?? 0) }}" class="form-control">
                    <p class="form-hint">Lower numbers appear first.</p>
                    @error('sort_order')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="hidden xl:flex items-center gap-2.5 px-5 pb-5">
                <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update banner' : 'Save banner' }}</button>
                <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-image"></i> Banner image @unless($isEdit)<span class="text-rose-500">*</span>@endunless</h3>
                    <p class="card-subtitle">Recommended 1200 × 500 px.</p>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div id="sliderDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 dark:bg-slate-900/40 cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-900/70" role="button" tabindex="0">
                    <input type="file" id="sliderFileInput" name="image_file" accept="image/*" class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="stat-icon tone-emerald !w-11 !h-11 text-xl"><i class="ph-duotone ph-cloud-arrow-up"></i></span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $isEdit ? 'Replace image' : 'Drop an image' }}, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span></p>
                        <p class="text-xs text-slate-400">PNG, JPG, WEBP or GIF · up to 4 MB{{ $isEdit ? ' · leave empty to keep' : '' }}</p>
                    </div>
                    <div id="sliderPreview" class="mt-4 flex justify-center empty:hidden"></div>
                </div>
                @error('image_file')<p class="{{ $err }}">{{ $message }}</p>@enderror
                <div>
                    <label for="image_url" class="form-label">Or image URL</label>
                    <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $isEdit && filter_var($slider->image, FILTER_VALIDATE_URL) ? $slider->image : '') }}" placeholder="https://…" class="form-control">
                    @error('image_url')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </aside>

    <div class="xl:hidden sticky bottom-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t border-slate-200 dark:border-slate-700 flex items-center gap-2.5">
        <a href="{{ route('admin.sliders.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update banner' : 'Save banner' }}</button>
    </div>
</form>

@push('scripts')
<script>
    $(function () {
        const drop = document.getElementById('sliderDropzone');
        const file = document.getElementById('sliderFileInput');
        drop.addEventListener('click', function (e) { if (e.target !== file) file.click(); });
        drop.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); file.click(); } });
        initDragAndDropUploader('sliderDropzone', 'sliderFileInput', 'sliderPreview');

        // Link target: show + enable only the field for the chosen link type
        function syncTarget() {
            const t = $('#link_type').val();
            $('.link-target').each(function () {
                const on = this.dataset.for === t;
                $(this).toggleClass('hidden', !on).find('select, input').prop('disabled', !on);
            });
            $('#bpCta').toggleClass('hidden', t === 'none');
        }
        $('#link_type').on('change', syncTarget); syncTarget();

        // Live banner preview
        let lang = 'en';
        const $bp = $('#bannerPreview');
        function setImage(src) {
            if (src) { $bp.css('background-image', 'url("' + src.replace(/"/g, '%22') + '")'); $('#bpEmpty').addClass('hidden'); }
        }
        function renderText() {
            const v = k => $.trim($(`#${k}_${lang}`).val()) || $.trim($(`#${k}_en`).val());
            const title = v('title'), sub = v('subtitle'), badge = v('badge');
            $('#bpTitle').text(title).attr('lang', lang).toggleClass('hidden', !title);
            $('#bpSubtitle').text(sub).attr('lang', lang).toggleClass('hidden', !sub);
            $('#bpBadge').text(badge).toggleClass('hidden', !badge);
        }
        $('.bp-field').on('input', renderText);
        $('.prev-lang').on('click', function () {
            lang = this.dataset.lang;
            $('.prev-lang').removeClass('bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white').addClass('text-slate-500 dark:text-slate-400').attr('aria-pressed', 'false');
            $(this).addClass('bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white').removeClass('text-slate-500 dark:text-slate-400').attr('aria-pressed', 'true');
            renderText();
        });
        $('#image_url').on('change input', function () { if (/^https?:\/\//i.test(this.value)) setImage(this.value); });
        file.addEventListener('change', function () {
            if (!file.files.length) return;
            const r = new FileReader(); r.onload = e => setImage(e.target.result); r.readAsDataURL(file.files[0]);
        });
        drop.addEventListener('drop', function (e) {
            const f = e.dataTransfer && e.dataTransfer.files[0];
            if (f && f.type.indexOf('image/') === 0) { const r = new FileReader(); r.onload = ev => setImage(ev.target.result); r.readAsDataURL(f); }
        });
        renderText();
        $('#image_url').trigger('change');
    });
</script>
@endpush
