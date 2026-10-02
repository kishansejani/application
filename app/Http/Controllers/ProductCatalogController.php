<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Order;
use Illuminate\Http\Request;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('sort_order')->get();
        $query = Product::with(['category', 'subCategory', 'images'])->where('is_active', true);

        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        if ($request->filled('subcategory')) {
            $sub = SubCategory::where('slug', $request->subcategory)->first();
            if ($sub) {
                $query->where('sub_category_id', $sub->id);
            }
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

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sort = $request->get('sort', 'featured');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderBy('is_featured', 'desc')->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $deliverySlotInfo = Order::determineDeliverySlot();

        return view('frontend.products.index', compact('products', 'categories', 'deliverySlotInfo'));
    }

    public function categories()
    {
        $categories = Category::with(['subCategories', 'products' => function($q) {
            $q->where('is_active', true)->take(4);
        }])->where('is_active', true)->orderBy('sort_order')->get();

        return view('frontend.categories.index', compact('categories'));
    }

    public function categoryDetails(Category $category)
    {
        $category->load(['subCategories.products', 'products' => function($q) {
            $q->where('is_active', true);
        }]);

        $deliverySlotInfo = Order::determineDeliverySlot();
        return view('frontend.categories.show', compact('category', 'deliverySlotInfo'));
    }

    public function subCategoryDetails(SubCategory $subcategory)
    {
        $subcategory->load(['category', 'products' => function($q) {
            $q->where('is_active', true);
        }]);

        $deliverySlotInfo = Order::determineDeliverySlot();
        return view('frontend.subcategories.show', compact('subcategory', 'deliverySlotInfo'));
    }

    public function show(Product $product)
    {
        abort_if(!$product->is_active, 404);

        $product->load(['category', 'subCategory', 'images']);
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        $deliverySlotInfo = Order::determineDeliverySlot();

        return view('frontend.products.show', compact('product', 'relatedProducts', 'deliverySlotInfo'));
    }

    public function quickView(Product $product)
    {
        $product->load(['category', 'images']);
        return response()->json([
            'id' => $product->id,
            'name' => $product->localized_name,
            'category' => $product->category ? $product->category->localized_name : '',
            'price' => $product->price,
            'discount_price' => $product->discount_price,
            'effective_price' => $product->effective_price,
            'discount_percent' => $product->discount_percent,
            'unit' => $product->unit,
            'thumbnail' => $product->thumbnail_url,
            'images' => $product->images->map(fn($img) => $img->image_url),
            'short_desc' => $product->localized_short_description,
            'in_stock' => $product->is_in_stock,
            'stock_quantity' => $product->stock_quantity,
        ]);
    }
}
