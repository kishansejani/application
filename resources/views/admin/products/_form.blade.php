{{-- Shared create/edit form for products. Expects $categories, optional $product and $subCategories (edit). --}}
@php
    $product = $product ?? null;
    $isEdit = (bool) $product;
    $subCategories = $subCategories ?? collect();
    $isActive = $errors->any() ? (bool) old('is_active') : ($product->is_active ?? true);
    $isFeatured = $errors->any() ? (bool) old('is_featured') : ($product->is_featured ?? false);
    $selectedCat = old('category_id', $product->category_id ?? '');
    $selectedSub = old('sub_category_id', $product->sub_category_id ?? '');
    // Reload subcategories client-side when the server-rendered list doesn't match the selected category
    $reloadSubs = $selectedCat && (!$isEdit || (string) $selectedCat !== (string) $product->category_id);
    $err = 'text-xs font-semibold text-rose-600 mt-1';
@endphp

<form id="productForm" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
      class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-5 items-start">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- ================= Main column ================= --}}
    <div class="space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-info"></i> General information</h3>
                    <p class="card-subtitle">Product names, pack size and item code.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name_en" class="form-label">Product name (English) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_en" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}" required placeholder="e.g. Fresh hybrid tomatoes" class="form-control">
                    @if($isEdit)
                        <p class="form-hint truncate">URL: <span class="font-mono text-slate-600 dark:text-slate-300">/product/<b class="text-emerald-600 dark:text-emerald-400">{{ $product->slug }}</b></span></p>
                    @else
                        <p class="form-hint truncate">URL preview: <span class="font-mono text-slate-600 dark:text-slate-300">/product/<b id="slugPreview" class="text-emerald-600 dark:text-emerald-400">…</b>-<span class="text-slate-400">####</span></span></p>
                    @endif
                    @error('name_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_gu" class="form-label">Product name (ગુજરાતી) <span class="text-rose-500">*</span></label>
                    <input type="text" id="name_gu" name="name_gu" lang="gu" value="{{ old('name_gu', $product->name_gu ?? '') }}" required placeholder="દા.ત. તાજા ટામેટા" class="form-control">
                    @error('name_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="unit" class="form-label">Pack size / unit <span class="text-rose-500">*</span></label>
                    <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit ?? '1 kg') }}" required placeholder="e.g. 1 kg, 500 g, 1 L, 1 pack" class="form-control" list="unitSuggestions">
                    <datalist id="unitSuggestions">
                        @foreach(['250 g', '500 g', '1 kg', '2 kg', '5 kg', '200 ml', '500 ml', '1 L', '1 pack', '1 dozen', '1 piece'] as $u)<option value="{{ $u }}"></option>@endforeach
                    </datalist>
                    @error('unit')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sku" class="form-label">SKU / item code</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" placeholder="Auto-generated if empty" class="form-control font-mono">
                    @error('sku')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-currency-inr"></i> Pricing</h3>
                        <p class="card-subtitle">MRP and optional sale price.</p>
                    </div>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="form-label">Regular price (MRP) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400 pointer-events-none">₹</span>
                                <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required placeholder="0.00" class="form-control !pl-7 font-bold">
                            </div>
                            @error('price')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="discount_price" class="form-label">Sale price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-emerald-500 pointer-events-none">₹</span>
                                <input type="number" step="0.01" min="0" id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="Optional" class="form-control !pl-7 font-bold">
                            </div>
                            @error('discount_price')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div id="pricePreview" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Customer sees</p>
                        <div class="mt-1 flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span id="ppFinal" class="text-2xl font-extrabold text-slate-900 dark:text-white">₹0.00</span>
                            <span id="ppMrp" class="text-sm text-slate-400 line-through hidden"></span>
                            <span id="ppOff" class="badge badge-success" style="display:none"></span>
                        </div>
                        <p id="ppNote" class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter a price to preview.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="ph-duotone ph-warehouse"></i> Inventory</h3>
                        <p class="card-subtitle">Stock on hand and low-stock alert.</p>
                    </div>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="stock_quantity" class="form-label">{{ $isEdit ? 'Available stock' : 'Opening stock' }} <span class="text-rose-500">*</span></label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 50) }}" required min="0" class="form-control font-bold">
                            @error('stock_quantity')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="form-label">Low-stock alert at</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" min="0" class="form-control font-bold">
                            @error('low_stock_threshold')<p class="{{ $err }}">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stock status</p>
                        <div class="mt-1.5" id="stockPreview"></div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Quantities can also be adjusted later from <a href="{{ route('admin.stock.index') }}" class="font-semibold underline">Stock & inventory</a>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-translate"></i> Descriptions</h3>
                    <p class="card-subtitle">Short tagline for listings and full details for the product page.</p>
                </div>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="short_description_en" class="form-label">Short tagline (English)</label>
                    <input type="text" id="short_description_en" name="short_description_en" value="{{ old('short_description_en', $product->short_description_en ?? '') }}" placeholder="e.g. Farm-fresh, juicy red tomatoes" class="form-control">
                    @error('short_description_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="short_description_gu" class="form-label">Short tagline (ગુજરાતી)</label>
                    <input type="text" id="short_description_gu" name="short_description_gu" lang="gu" value="{{ old('short_description_gu', $product->short_description_gu ?? '') }}" placeholder="દા.ત. ખેતરમાંથી સીધા તાજા ટામેટા" class="form-control">
                    @error('short_description_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_en" class="form-label">Full description (English)</label>
                    <textarea id="description_en" name="description_en" rows="5" placeholder="Specifications, benefits, origin…" class="form-control">{{ old('description_en', $product->description_en ?? '') }}</textarea>
                    @error('description_en')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description_gu" class="form-label">Full description (ગુજરાતી)</label>
                    <textarea id="description_gu" name="description_gu" lang="gu" rows="5" placeholder="પ્રોડક્ટની વિગતવાર માહિતી…" class="form-control">{{ old('description_gu', $product->description_gu ?? '') }}</textarea>
                    @error('description_gu')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-images"></i> Gallery</h3>
                    <p class="card-subtitle">Additional images shown on the product page.</p>
                </div>
                @if($isEdit)
                    <span class="badge badge-neutral" id="galleryCount">{{ $product->images->count() }} {{ \Illuminate\Support\Str::plural('image', $product->images->count()) }}</span>
                @endif
            </div>
            <div class="card-body space-y-4">
                @if($isEdit && $product->images->count() > 0)
                    <div>
                        <p class="form-label">Current images</p>
                        <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-6 gap-3" id="existingGallery">
                            @foreach($product->images as $img)
                                <div class="gallery-item relative group aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                    <img src="{{ $img->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                    <button type="button" onclick="deleteProductImage({{ $img->id }}, this)" title="Delete image" aria-label="Delete image"
                                        class="absolute top-1.5 right-1.5 w-8 h-8 rounded-lg bg-white/90 dark:bg-slate-900/90 text-rose-600 shadow flex items-center justify-center opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition hover:bg-rose-600 hover:text-white">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div id="galleryDropzone" class="dropzone-container p-6 rounded-2xl text-center bg-slate-50 dark:bg-slate-900/40 cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-900/70" role="button" tabindex="0">
                    <input type="file" id="galleryFileInput" name="gallery_files[]" accept="image/*" multiple class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="stat-icon tone-violet !w-11 !h-11 text-xl"><i class="ph-duotone ph-film-strip"></i></span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $isEdit ? 'Add more images' : 'Drop gallery images' }} here, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span></p>
                        <p class="text-xs text-slate-400">Select several files at once · JPG, PNG or WEBP · up to 5 MB each</p>
                    </div>
                    <div id="galleryPreview" class="mt-4 flex flex-wrap justify-center gap-3 empty:hidden"></div>
                </div>
                @error('gallery_files.*')<p class="{{ $err }}">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- ================= Sidebar ================= --}}
    <aside class="space-y-5 min-w-0">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-eye"></i> Visibility</h3>
            </div>
            <div class="card-body space-y-3">
                @include('admin.categories._toggle', ['name' => 'is_active', 'checked' => $isActive, 'label' => 'Active', 'hint' => 'Customers can find and buy this product.'])
                @include('admin.categories._toggle', ['name' => 'is_featured', 'checked' => $isFeatured, 'label' => 'Featured', 'hint' => 'Show in featured products on the home page.'])
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ph-duotone ph-tree-structure"></i> Organisation</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="categorySelect" class="form-label">Category <span class="text-rose-500">*</span></label>
                    <select id="categorySelect" name="category_id" required class="form-select w-full" data-search>
                        @unless($isEdit)<option value="">Choose a category</option>@endunless
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) $selectedCat === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name_en }} ({{ $cat->name_gu }})</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="subCategorySelect" class="form-label">Subcategory</label>
                    <select id="subCategorySelect" name="sub_category_id" class="form-select w-full" data-search>
                        <option value="">No subcategory</option>
                        @unless($reloadSubs)
                            @foreach($subCategories as $sub)
                                <option value="{{ $sub->id }}" {{ (string) $selectedSub === (string) $sub->id ? 'selected' : '' }}>{{ $sub->name_en }} ({{ $sub->name_gu }})</option>
                            @endforeach
                        @endunless
                    </select>
                    <p class="form-hint">Optional. The list updates when you change the category.</p>
                    @error('sub_category_id')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="ph-duotone ph-image"></i> Thumbnail</h3>
                    <p class="card-subtitle">Main image used in listings.</p>
                </div>
            </div>
            <div class="card-body space-y-4">
                @if($isEdit && $product->thumbnail)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $product->thumbnail_url }}" alt="" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Current thumbnail</p>
                            <p class="form-hint !mt-0.5">Upload or paste a URL to replace it.</p>
                        </div>
                    </div>
                @endif
                <div id="thumbDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 dark:bg-slate-900/40 cursor-pointer hover:bg-slate-100/80 dark:hover:bg-slate-900/70" role="button" tabindex="0">
                    <input type="file" id="thumbFileInput" name="thumbnail_file" accept="image/*" class="hidden">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <span class="stat-icon tone-emerald !w-11 !h-11 text-xl"><i class="ph-duotone ph-cloud-arrow-up"></i></span>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Drop an image, or <span class="text-emerald-600 dark:text-emerald-400 underline">browse</span></p>
                        <p class="text-xs text-slate-400">JPG, PNG or WEBP · up to 5 MB</p>
                    </div>
                    <div id="thumbPreview" class="mt-4 flex justify-center empty:hidden"></div>
                </div>
                @error('thumbnail_file')<p class="{{ $err }}">{{ $message }}</p>@enderror
                <div>
                    <label for="thumbnail_url" class="form-label">Or image URL</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url" value="{{ old('thumbnail_url', $isEdit && filter_var($product->thumbnail, FILTER_VALIDATE_URL) ? $product->thumbnail : '') }}" placeholder="https://…" class="form-control">
                    @error('thumbnail_url')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </aside>

    {{-- ================= Sticky save bar ================= --}}
    <div class="xl:col-span-2 sticky bottom-0 xl:bottom-4 z-20 -mx-4 sm:-mx-6 xl:mx-0 px-4 sm:px-6 xl:px-5 py-3
                bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t xl:border border-slate-200 dark:border-slate-700 xl:rounded-2xl xl:shadow-lg
                flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 min-w-0 text-sm">
            <span class="font-extrabold text-slate-900 dark:text-white" id="barPrice">—</span>
            <span class="text-slate-300 dark:text-slate-600">·</span>
            <span id="barStock" class="truncate text-slate-500 dark:text-slate-400"></span>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto sm:ml-auto">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary flex-1 sm:flex-none"><i class="ph-bold ph-check"></i> {{ $isEdit ? 'Update product' : 'Save product' }}</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(function () {
        // Uploaders (thumbnail + gallery)
        [['thumbDropzone', 'thumbFileInput', 'thumbPreview'], ['galleryDropzone', 'galleryFileInput', 'galleryPreview']].forEach(function (ids) {
            const drop = document.getElementById(ids[0]), file = document.getElementById(ids[1]);
            drop.addEventListener('click', function (e) { if (e.target !== file) file.click(); });
            drop.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); file.click(); } });
            initDragAndDropUploader(ids[0], ids[1], ids[2]);
        });

        // Slug preview (create only — slug is generated server-side)
        const slugify = s => (s || '').toString().toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const $slugPrev = $('#slugPreview');
        if ($slugPrev.length) {
            const upd = () => $slugPrev.text(slugify($('#name_en').val()) || '…');
            $('#name_en').on('input', upd); upd();
        }

        // Live price / discount preview
        const inr = n => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        function updatePrice() {
            const price = parseFloat($('#price').val()), sale = parseFloat($('#discount_price').val());
            const $note = $('#ppNote'), $mrp = $('#ppMrp'), $off = $('#ppOff'), $box = $('#pricePreview');
            $box.removeClass('!border-rose-300 dark:!border-rose-500/40');
            $mrp.addClass('hidden'); $off.hide();
            if (isNaN(price)) { $('#ppFinal').text('₹0.00'); $('#barPrice').text('—'); $note.text('Enter a price to preview.'); return; }
            if (!isNaN(sale) && $('#discount_price').val() !== '') {
                if (sale >= price) {
                    $('#ppFinal').text(inr(price)); $('#barPrice').text(inr(price));
                    $box.addClass('!border-rose-300 dark:!border-rose-500/40');
                    $note.html('<span class="font-semibold text-rose-600">Sale price must be lower than the regular price.</span>');
                    return;
                }
                const pct = price > 0 ? Math.round((price - sale) / price * 100) : 0;
                $('#ppFinal').text(inr(sale)); $('#barPrice').text(inr(sale));
                $mrp.text(inr(price)).removeClass('hidden');
                $off.text(pct + '% off').show();
                $note.text('Customer saves ' + inr(price - sale) + ' per unit.');
            } else {
                $('#ppFinal').text(inr(price)); $('#barPrice').text(inr(price));
                $note.text('No discount applied.');
            }
        }
        $('#price, #discount_price').on('input', updatePrice); updatePrice();

        // Live stock status preview
        function updateStock() {
            const q = parseInt($('#stock_quantity').val(), 10), t = parseInt($('#low_stock_threshold').val(), 10);
            const thr = isNaN(t) ? 5 : t;
            let html, txt;
            if (isNaN(q) || q <= 0) { html = '<span class="badge badge-danger badge-dot">Out of stock</span>'; txt = 'Out of stock'; }
            else if (q <= thr) { html = '<span class="badge badge-warning badge-dot">Low stock · ' + q + ' left</span>'; txt = q + ' in stock (low)'; }
            else { html = '<span class="badge badge-success badge-dot">' + q + ' in stock</span>'; txt = q + ' in stock'; }
            $('#stockPreview').html(html); $('#barStock').text(txt);
        }
        $('#stock_quantity, #low_stock_threshold').on('input', updateStock); updateStock();

        // Dependent subcategory dropdown
        const $sub = $('#subCategorySelect');
        function loadSubs(catId, selected) {
            $sub.prop('disabled', true).html('<option value="">Loading…</option>');
            if (!catId) { $sub.prop('disabled', false).html('<option value="">No subcategory</option>'); return; }
            $.get(`/admin/categories/${catId}/subcategories`)
                .done(function (data) {
                    $sub.html('<option value="">No subcategory</option>');
                    data.forEach(function (s) {
                        $sub.append($('<option>').val(s.id).text(`${s.name_en} (${s.name_gu})`).prop('selected', String(s.id) === String(selected || '')));
                    });
                })
                .fail(function () { $sub.html('<option value="">No subcategory</option>'); toastr.error('Could not load subcategories.'); })
                .always(function () { $sub.prop('disabled', false); });
        }
        $('#categorySelect').on('change', function () { loadSubs($(this).val(), null); });
        @if($reloadSubs)
            loadSubs(@json((string) $selectedCat), @json((string) $selectedSub));
        @endif
    });

    function deleteProductImage(imageId, btn) {
        Swal.fire({
            title: 'Delete this gallery image?', text: 'The image is removed immediately.', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'is-danger' }, focusCancel: true
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({ url: `/admin/products/images/${imageId}`, type: 'DELETE' })
                .done(function (res) {
                    if (!res.success) return;
                    $(btn).closest('.gallery-item').fadeOut(150, function () {
                        $(this).remove();
                        const n = $('#existingGallery .gallery-item').length;
                        $('#galleryCount').text(n + (n === 1 ? ' image' : ' images'));
                        if (!n) $('#existingGallery').parent().remove();
                    });
                    toastr.success('Image removed from gallery');
                })
                .fail(function () { toastr.error('Could not delete the image. Please try again.'); });
        });
    }
</script>
@endpush
