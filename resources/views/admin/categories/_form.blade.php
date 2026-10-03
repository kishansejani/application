{{-- Shared create/edit form for categories. Expects optional $category. --}}
@php
    $category = $category ?? null;
    $isEdit = (bool) $category;
    $isActive = $errors->any() ? (bool) old('is_active') : ($category->is_active ?? true);
    $isFeatured = $errors->any() ? (bool) old('is_featured') : ($category->is_featured ?? false);
    $iconValue = old('icon', $category->icon ?? 'fa-solid fa-carrot');
    $iconPicks = ['fa-solid fa-carrot', 'fa-solid fa-apple-whole', 'fa-solid fa-lemon', 'fa-solid fa-cheese', 'fa-solid fa-egg', 'fa-solid fa-bread-slice',
                  'fa-solid fa-drumstick-bite', 'fa-solid fa-fish', 'fa-solid fa-mug-hot', 'fa-solid fa-bottle-water', 'fa-solid fa-cookie', 'fa-solid fa-pepper-hot',
                  'fa-solid fa-wheat-awn', 'fa-solid fa-jar', 'fa-solid fa-ice-cream', 'fa-solid fa-spray-can-sparkles', 'fa-solid fa-baby', 'fa-solid fa-seedling'];
@endphp

<form action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
      class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Main column --}}
    <div class="space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-text-aa"></i> Basic details</h3>
                    <p class="card-subtitle">Names shown to customers in English and Gujarati.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name_en" class="form-label">Name (English) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_en" name="name_en" value="{{ old('name_en', $category->name_en ?? '') }}" required placeholder="e.g. Fruits & vegetables" class="form-control">
                    @error('name_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_gu" class="form-label">Name (ગુજરાતી) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_gu" name="name_gu" lang="gu" value="{{ old('name_gu', $category->name_gu ?? '') }}" required placeholder="દા.ત. ફળો અને શાકભાજી" class="form-control">
                    @error('name_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="slug" class="form-label">URL slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="Auto-generated from the English name" class="form-control font-mono">
                    <p class="form-hint truncate">Preview: <span class="font-mono text-slate-600 dark:text-slate-300">/category/<b id="slugPreview" class="text-emerald-600 dark:text-emerald-400"></b></span></p>
                    @error('slug')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sort_order" class="form-label">Display order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control">
                    <p class="form-hint">Lower numbers appear first.</p>
                    @error('sort_order')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-article"></i> Description</h3>
                    <p class="card-subtitle">Optional short introduction shown on the category page.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="description_en" class="form-label">Description (English)</label>
                    <textarea id="description_en" name="description_en" rows="4" class="form-control">{{ old('description_en', $category->description_en ?? '') }}</textarea>
                    @error('description_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_gu" class="form-label">Description (ગુજરાતી)</label>
                    <textarea id="description_gu" name="description_gu" lang="gu" rows="4" class="form-control">{{ old('description_gu', $category->description_gu ?? '') }}</textarea>
                    @error('description_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-image"></i> Category image</h3>
                    <p class="card-subtitle">Used on category tiles. If no image is set, the icon is shown instead.</p>
                </div>
            </div>
            <div class="card-body space-y-4">
                @if($isEdit && $category->image)
                    <div class="flex items-center gap-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name_en }}" class="w-20 h-20 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Current image</p>
                            <p class="form-hint !mt-0.5">Upload a new file or paste a URL below to replace it.</p>
                        </div>
                    </div>
                @endif
                <div id="catDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 dark:bg-slate-900/40 cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-900/70" role="button" tabindex="0">
                    <input type="file" id="catFileInput" name="image_file" accept="image/*" class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="stat-icon tone-emerald !w-11 !h-11 text-xl"><i class="ph-duotone ph-cloud-arrow-up"></i></span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Drop an image here, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span></p>
                        <p class="text-xs text-slate-400">PNG, JPG, WEBP or SVG · up to 4 MB</p>
                    </div>
                    <div id="catPreview" class="mt-4 flex justify-center gap-3 empty:hidden"></div>
                </div>
                @error('image_file')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                <div>
                    <label for="image_url" class="form-label">Or image URL</label>
                    <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $isEdit && filter_var($category->image, FILTER_VALIDATE_URL) ? $category->image : '') }}" placeholder="https://…" class="form-control">
                    @error('image_url')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <aside class="space-y-5 min-w-0 xl:sticky xl:top-[84px]">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Visibility</h3>
            </div>
            <div class="card-body space-y-3">
                @include('admin.categories._toggle', ['name' => 'is_active', 'checked' => $isActive, 'label' => 'Active', 'hint' => 'Show this category on the storefront.'])
                @include('admin.categories._toggle', ['name' => 'is_featured', 'checked' => $isFeatured, 'label' => 'Featured', 'hint' => 'Highlight on the home page.'])
            </div>
            <div class="hidden xl:flex items-center gap-2.5 px-5 pb-5">
                <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update category' : 'Save category' }}</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-smiley"></i> Icon</h3>
                    <p class="card-subtitle">Font Awesome class, previewed live.</p>
                </div>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-14 h-14 shrink-0 rounded-2xl flex items-center justify-center text-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
                        <i id="iconPreview" class="{{ $iconValue ?: 'fa-solid fa-layer-group' }}"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <label for="icon" class="form-label">Icon class</label>
                        <input type="text" id="icon" name="icon" value="{{ $iconValue }}" placeholder="fa-solid fa-carrot" class="form-control font-mono !text-[13px]" autocomplete="off" spellcheck="false">
                    </div>
                </div>
                @error('icon')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                <div>
                    <p class="form-hint !mt-0 mb-2">Quick pick</p>
                    <div class="grid grid-cols-6 gap-1.5" id="iconPicks">
                        @foreach($iconPicks as $pick)
                            <button type="button" data-icon="{{ $pick }}" title="{{ $pick }}"
                                class="icon-pick h-9 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center justify-center transition">
                                <i class="{{ $pick }}"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile / tablet sticky save bar --}}
    <div class="xl:hidden sticky bottom-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t border-slate-200 dark:border-slate-700 flex items-center gap-2.5">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update category' : 'Save category' }}</button>
    </div>
</form>

@push('scripts')
<script>
    $(function () {
        const drop = document.getElementById('catDropzone');
        const file = document.getElementById('catFileInput');
        drop.addEventListener('click', function (e) { if (e.target !== file) file.click(); });
        drop.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); file.click(); } });
        initDragAndDropUploader('catDropzone', 'catFileInput', 'catPreview');

        // Live slug preview
        const slugify = s => (s || '').toString().toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const $name = $('#name_en'), $slug = $('#slug'), $prev = $('#slugPreview');
        function updateSlug() { $prev.text(slugify($slug.val() || $name.val()) || '…'); }
        $name.on('input', updateSlug); $slug.on('input', updateSlug); updateSlug();

        // Live icon preview
        const $icon = $('#icon'), $iconPrev = $('#iconPreview');
        function updateIcon() {
            const v = $.trim($icon.val());
            $iconPrev.attr('class', v || 'fa-solid fa-layer-group');
            $('.icon-pick').each(function () {
                const on = $(this).data('icon') === v;
                $(this).toggleClass('!border-emerald-500 !text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10', on).attr('aria-pressed', on ? 'true' : 'false');
            });
        }
        $icon.on('input', updateIcon);
        $('#iconPicks').on('click', '.icon-pick', function () { $icon.val($(this).data('icon')).trigger('input').focus(); });
        updateIcon();
    });
</script>
@endpush
