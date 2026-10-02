@extends('admin.layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product: ' . $product->name_en)
@section('page-subtitle', 'Update prices, stock, images gallery, and multilingual content')

@section('content')
<div class="max-w-5xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1: Basic Information -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-brand-600"></i>
                <span>General Information</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- English Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Product Name (English) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_en" value="{{ old('name_en', $product->name_en) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Gujarati Name -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">
                        Product Name (ગુજરાતી) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_gu" value="{{ old('name_gu', $product->name_gu) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Category <span class="text-rose-500">*</span>
                    </label>
                    <select id="categorySelect" name="category_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name_en }} ({{ $cat->name_gu }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sub Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sub Category</label>
                    <select id="subCategorySelect" name="sub_category_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="">-- No Subcategory --</option>
                        @foreach($subCategories as $sub)
                            <option value="{{ $sub->id }}" {{ old('sub_category_id', $product->sub_category_id) == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name_en }} ({{ $sub->name_gu }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Packing / Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- SKU -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Item Code</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Section 2: Pricing & Stock Inventory -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-indian-rupee-sign text-emerald-600"></i>
                <span>Pricing & Inventory Management</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Regular MRP Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Regular Price (₹ MRP) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Discount Price -->
                <div>
                    <label class="block text-xs font-bold text-emerald-700 uppercase mb-1">
                        Offer / Sale Price (₹)
                    </label>
                    <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" placeholder="Leave blank if no discount" class="w-full px-4 py-2.5 border border-emerald-200 bg-emerald-50/30 rounded-xl text-sm font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Stock Quantity -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Available Stock Qty <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Low Stock Threshold -->
                <div>
                    <label class="block text-xs font-bold text-amber-700 uppercase mb-1">
                        Low Stock Alert At
                    </label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" class="w-full px-4 py-2.5 border border-amber-200 bg-amber-50/30 rounded-xl text-sm font-bold text-amber-800 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Section 3: Media & Gallery Management -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-images text-indigo-600"></i>
                <span>Product Images & Gallery</span>
            </h4>

            <!-- Existing Gallery -->
            @if($product->images->count() > 0)
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Existing Gallery Images</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($product->images as $img)
                            <div class="relative group w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                <img src="{{ $img->image_url }}" class="w-full h-full object-cover">
                                <button type="button" onclick="deleteProductImage({{ $img->id }}, this)" class="absolute inset-0 bg-rose-900/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Delete
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Primary Thumbnail Upload -->
                <div>
                    @if($product->thumbnail)
                        <div class="mb-3 flex items-center gap-3">
                            <img src="{{ $product->thumbnail_url }}" class="w-14 h-14 rounded-xl object-cover border border-slate-200 shadow-sm">
                            <span class="text-xs text-slate-500 font-medium">Current Thumbnail</span>
                        </div>
                    @endif
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Replace Thumbnail</label>
                    <div id="thumbDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                        <input type="file" id="thumbFileInput" name="thumbnail_file" accept="image/*" class="hidden">
                        <div class="flex flex-col items-center justify-center space-y-1.5">
                            <i class="fa-solid fa-image text-brand-600 text-2xl"></i>
                            <p class="text-xs font-bold text-slate-700">Drop primary image or <span class="text-brand-600 underline">browse</span></p>
                        </div>
                        <div id="thumbPreview" class="mt-3 flex justify-center"></div>
                    </div>
                    <input type="url" name="thumbnail_url" value="{{ old('thumbnail_url', filter_var($product->thumbnail, FILTER_VALIDATE_URL) ? $product->thumbnail : '') }}" placeholder="Or Thumbnail URL: https://..." class="mt-2 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Multi-Image Gallery Upload -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Add More Gallery Images</label>
                    <div id="galleryDropzone" class="dropzone-container p-5 rounded-2xl text-center bg-slate-50 cursor-pointer hover:bg-slate-100/80">
                        <input type="file" id="galleryFileInput" name="gallery_files[]" accept="image/*" multiple class="hidden">
                        <div class="flex flex-col items-center justify-center space-y-1.5">
                            <i class="fa-solid fa-photo-film text-indigo-600 text-2xl"></i>
                            <p class="text-xs font-bold text-slate-700">Drop additional gallery pictures or <span class="text-indigo-600 underline">browse</span></p>
                        </div>
                        <div id="galleryPreview" class="mt-3 flex flex-wrap gap-2 justify-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Descriptions (Bilingual) -->
        <div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-language text-purple-600"></i>
                <span>Bilingual Descriptions</span>
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Short Description EN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Short Tagline (English)</label>
                    <input type="text" name="short_description_en" value="{{ old('short_description_en', $product->short_description_en) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Short Description GU -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Short Tagline (ગુજરાતી)</label>
                    <input type="text" name="short_description_gu" value="{{ old('short_description_gu', $product->short_description_gu) }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Full Description EN -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Description (English)</label>
                    <textarea name="description_en" rows="4" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_en', $product->description_en) }}</textarea>
                </div>

                <!-- Full Description GU -->
                <div>
                    <label class="block text-xs font-bold text-brand-700 uppercase mb-1">Full Description (ગુજરાતી)</label>
                    <textarea name="description_gu" rows="4" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description_gu', $product->description_gu) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 5: Status Flags -->
        <div class="flex items-center gap-8 pt-4 border-t border-slate-100">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Feature on Storefront</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-slate-800">Active</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold">Cancel</a>
            <button type="submit" class="px-7 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-500/25 transition-all">Update Product</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thumbnail upload
        const thumbDrop = document.getElementById('thumbDropzone');
        const thumbInput = document.getElementById('thumbFileInput');
        thumbDrop.addEventListener('click', () => thumbInput.click());
        initDragAndDropUploader('thumbDropzone', 'thumbFileInput', 'thumbPreview');

        // Gallery upload
        const galDrop = document.getElementById('galleryDropzone');
        const galInput = document.getElementById('galleryFileInput');
        galDrop.addEventListener('click', () => galInput.click());
        initDragAndDropUploader('galleryDropzone', 'galleryFileInput', 'galleryPreview');

        // Dynamic Subcategories
        $('#categorySelect').on('change', function() {
            const catId = $(this).val();
            const subSelect = $('#subCategorySelect');
            subSelect.html('<option value="">-- Loading Sub Categories --</option>');

            if (!catId) {
                subSelect.html('<option value="">-- Select Sub Category --</option>');
                return;
            }

            $.get(`/admin/categories/${catId}/subcategories`, function(data) {
                subSelect.html('<option value="">-- Select Sub Category --</option>');
                data.forEach(function(sub) {
                    subSelect.append(`<option value="${sub.id}">${sub.name_en} (${sub.name_gu})</option>`);
                });
            });
        });
    });

    function deleteProductImage(imageId, btn) {
        if (!confirm('Delete this gallery image?')) return;
        $.ajax({
            url: `/admin/products/images/${imageId}`,
            type: 'DELETE',
            success: function(res) {
                if (res.success) {
                    $(btn).closest('.group').remove();
                    toastr.success('Image removed from gallery');
                }
            }
        });
    }
</script>
@endpush
