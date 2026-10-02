<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name_en')->get();
        $query = Product::with(['category', 'subCategory', 'images'])->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>', 5);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $products = $query->get();
        $stats = [
            'total' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 5)->count(),
            'low_stock' => Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name_en')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'thumbnail_url' => 'nullable|url',
            'gallery_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail_file')) {
            $thumbnailPath = $request->file('thumbnail_file')->store('products', 'public');
        } elseif ($request->filled('thumbnail_url')) {
            $thumbnailPath = $request->input('thumbnail_url');
        }

        $slug = Str::slug($request->name_en) . '-' . rand(1000, 9999);

        $product = Product::create([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'slug' => $slug,
            'unit' => $request->unit,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'thumbnail' => $thumbnailPath,
            'short_description_en' => $request->short_description_en,
            'short_description_gu' => $request->short_description_gu,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => $request->boolean('is_active', true),
            'sku' => $request->sku ?? ('SKU-' . strtoupper(Str::random(6))),
        ]);

        // Additional gallery images
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $idx => $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $idx + 1,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('name_en')->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->where('is_active', true)->get();
        $product->load('images');
        return view('admin.products.edit', compact('product', 'categories', 'subCategories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'thumbnail_url' => 'nullable|url',
            'gallery_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $thumbnailPath = $product->thumbnail;
        if ($request->hasFile('thumbnail_file')) {
            if ($product->thumbnail && !filter_var($product->thumbnail, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($product->thumbnail)) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail_file')->store('products', 'public');
        } elseif ($request->filled('thumbnail_url')) {
            $thumbnailPath = $request->input('thumbnail_url');
        }

        $product->update([
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'unit' => $request->unit,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'thumbnail' => $thumbnailPath,
            'short_description_en' => $request->short_description_en,
            'short_description_gu' => $request->short_description_gu,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sku' => $request->sku ?? $product->sku,
        ]);

        // Upload new gallery files
        if ($request->hasFile('gallery_files')) {
            $maxSort = $product->images()->max('sort_order') ?? 0;
            foreach ($request->file('gallery_files') as $idx => $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $maxSort + $idx + 1,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->thumbnail && !filter_var($product->thumbnail, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($product->thumbnail)) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        foreach ($product->images as $img) {
            if (!filter_var($img->image_path, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function deleteImage(ProductImage $image)
    {
        if (!filter_var($image->image_path, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
        return response()->json(['success' => true]);
    }

    public function toggleFeatured(Product $product)
    {
        $product->is_featured = !$product->is_featured;
        $product->save();
        return response()->json(['success' => true, 'is_featured' => $product->is_featured]);
    }

    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();
        return response()->json(['success' => true, 'is_active' => $product->is_active]);
    }
}
