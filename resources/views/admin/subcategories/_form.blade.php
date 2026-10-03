{{-- Shared create/edit form for subcategories. Expects $categories and optional $subcategory. --}}
@php
    $subcategory = $subcategory ?? null;
    $isEdit = (bool) $subcategory;
    $isActive = $errors->any() ? (bool) old('is_active') : ($subcategory->is_active ?? true);
    $selectedCat = old('category_id', $subcategory->category_id ?? request('category_id'));
    $parentMissing = $isEdit && $subcategory->category && !$categories->contains('id', $subcategory->category_id);
@endphp

<form action="{{ $isEdit ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data"
      class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-text-aa"></i> Basic details</h3>
                    <p class="card-subtitle">Parent category and names shown to customers.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="category_id" class="form-label">Parent category <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <span class="w-[42px] h-[42px] shrink-0 rounded-xl flex items-center justify-center text-lg bg-violet-50 text-violet-600 border border-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:border-violet-500/20">
                            <i id="parentIcon" class="fa-solid fa-layer-group"></i>
                        </span>
                        <select id="category_id" name="category_id" required class="form-select w-full">
                            @unless($isEdit)<option value="">Choose a category</option>@endunless
                            @if($parentMissing)
                                <option value="{{ $subcategory->category_id }}" data-icon="{{ $subcategory->category->icon }}" selected>{{ $subcategory->category->name_en }} ({{ $subcategory->category->name_gu }}) — inactive</option>
                            @endif
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-icon="{{ $cat->icon }}" {{ $selectedCat == $cat->id ? 'selected' : '' }}>{{ $cat->name_en }} ({{ $cat->name_gu }})</option>
                            @endforeach
                        </select>
                    </div>
                    @error('category_id')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_en" class="form-label">Name (English) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_en" name="name_en" value="{{ old('name_en', $subcategory->name_en ?? '') }}" required placeholder="e.g. Fresh vegetables" class="form-control">
                    @error('name_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_gu" class="form-label">Name (ગુજરાતી) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_gu" name="name_gu" lang="gu" value="{{ old('name_gu', $subcategory->name_gu ?? '') }}" required placeholder="દા.ત. તાજા શાકભાજી" class="form-control">
                    @error('name_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="slug" class="form-label">URL slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $subcategory->slug ?? '') }}" placeholder="Auto-generated from the English name" class="form-control font-mono">
                    <p class="form-hint truncate">Preview: <span class="font-mono text-slate-600 dark:text-slate-300">/subcategory/<b id="slugPreview" class="text-emerald-600 dark:text-emerald-400"></b></span></p>
                    @error('slug')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sort_order" class="form-label">Display order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $subcategory->sort_order ?? 0) }}" class="form-control">
                    <p class="form-hint">Lower numbers appear first.</p>
                    @error('sort_order')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-article"></i> Description</h3>
                    <p class="card-subtitle">Optional short introduction shown on the subcategory page.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="description_en" class="form-label">Description (English)</label>
                    <textarea id="description_en" name="description_en" rows="4" class="form-control">{{ old('description_en', $subcategory->description_en ?? '') }}</textarea>
                    @error('description_en')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_gu" class="form-label">Description (ગુજરાતી)</label>
                    <textarea id="description_gu" name="description_gu" lang="gu" rows="4" class="form-control">{{ old('description_gu', $subcategory->description_gu ?? '') }}</textarea>
                    @error('description_gu')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-5 min-w-0 xl:sticky xl:top-[84px]">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Visibility</h3>
            </div>
            <div class="card-body space-y-3">
                @include('admin.categories._toggle', ['name' => 'is_active', 'checked' => $isActive, 'label' => 'Active', 'hint' => 'Show this subcategory on the storefront.'])
            </div>
            <div class="hidden xl:flex items-center gap-2.5 px-5 pb-5">
                <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update subcategory' : 'Save subcategory' }}</button>
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-image"></i> Image</h3>
                    <p class="card-subtitle">Square images work best.</p>
                </div>
            </div>
            <div class="card-body space-y-4">
                @if($isEdit && $subcategory->image)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name_en }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Current image</p>
                            <p class="form-hint !mt-0.5">Upload or paste a URL to replace it.</p>
                        </div>
                    </div>
                @endif
                <div id="subDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 dark:bg-slate-900/40 cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-900/70" role="button" tabindex="0">
                    <input type="file" id="subFileInput" name="image_file" accept="image/*" class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="stat-icon tone-emerald !w-11 !h-11 text-xl"><i class="ph-duotone ph-cloud-arrow-up"></i></span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Drop an image, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span></p>
                        <p class="text-xs text-slate-400">PNG, JPG, WEBP or SVG · up to 4 MB</p>
                    </div>
                    <div id="subPreview" class="mt-4 flex justify-center gap-3 empty:hidden"></div>
                </div>
                @error('image_file')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                <div>
                    <label for="image_url" class="form-label">Or image URL</label>
                    <input type="url" id="image_url" name="image_url" value="{{ old('image_url', $isEdit && filter_var($subcategory->image, FILTER_VALIDATE_URL) ? $subcategory->image : '') }}" placeholder="https://…" class="form-control">
                    @error('image_url')<p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </aside>

    <div class="xl:hidden sticky bottom-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t border-slate-200 dark:border-slate-700 flex items-center gap-2.5">
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary flex-1"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update subcategory' : 'Save subcategory' }}</button>
    </div>
</form>

@push('scripts')
<script>
    $(function () {
        const drop = document.getElementById('subDropzone');
        const file = document.getElementById('subFileInput');
        drop.addEventListener('click', function (e) { if (e.target !== file) file.click(); });
        drop.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); file.click(); } });
        initDragAndDropUploader('subDropzone', 'subFileInput', 'subPreview');

        const slugify = s => (s || '').toString().toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const $name = $('#name_en'), $slug = $('#slug'), $prev = $('#slugPreview');
        function updateSlug() { $prev.text(slugify($slug.val() || $name.val()) || '…'); }
        $name.on('input', updateSlug); $slug.on('input', updateSlug); updateSlug();

        const $cat = $('#category_id');
        function updateParentIcon() { $('#parentIcon').attr('class', $cat.find(':selected').data('icon') || 'fa-solid fa-layer-group'); }
        $cat.on('change', updateParentIcon); updateParentIcon();
    });
</script>
@endpush
