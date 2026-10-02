<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory', 'images'])->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_gu', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%")
                  ->orWhere('description_gu', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'featured');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderBy('is_featured', 'desc')->latest(),
        };

        $products = $query->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'subCategory', 'images'])->where('is_active', true)->findOrFail($id);

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->localized_name,
                'unit' => $p->unit,
                'price' => (float) $p->price,
                'effective_price' => (float) $p->effective_price,
                'thumbnail_url' => $p->thumbnail_url,
                'is_in_stock' => $p->is_in_stock,
            ]);

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->localized_name,
                'category_name' => $product->category ? $product->category->localized_name : '',
                'subcategory_name' => $product->subCategory ? $product->subCategory->localized_name : '',
                'unit' => $product->unit,
                'price' => (float) $product->price,
                'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                'effective_price' => (float) $product->effective_price,
                'discount_percent' => $product->discount_percent,
                'stock_quantity' => $product->stock_quantity,
                'is_in_stock' => $product->is_in_stock,
                'thumbnail_url' => $product->thumbnail_url,
                'gallery' => $product->images->map(fn($img) => $img->image_url),
                'short_description' => $product->localized_short_description,
                'description' => $product->localized_description,
                'related_products' => $related,
            ],
        ]);
    }
}
