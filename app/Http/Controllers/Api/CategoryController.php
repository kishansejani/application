<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('sort_order')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->localized_name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'image_url' => $c->image_url,
                'description' => $c->localized_description,
                'subcategories' => $c->subCategories->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->localized_name,
                    'slug' => $s->slug,
                    'image_url' => $s->image_url,
                ]),
            ];
        });

        return response()->json(['status' => true, 'data' => $categories]);
    }

    public function show($slug)
    {
        $category = Category::with(['subCategories', 'products' => fn($q) => $q->where('is_active', true)])->where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->localized_name,
                'slug' => $category->slug,
                'image_url' => $category->image_url,
                'subcategories' => $category->subCategories->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->localized_name,
                    'slug' => $s->slug,
                    'image_url' => $s->image_url,
                ]),
                'products' => $category->products->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->localized_name,
                    'unit' => $p->unit,
                    'price' => (float) $p->price,
                    'effective_price' => (float) $p->effective_price,
                    'discount_percent' => $p->discount_percent,
                    'thumbnail_url' => $p->thumbnail_url,
                    'is_in_stock' => $p->is_in_stock,
                ]),
            ],
        ]);
    }
}
