<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount(['subCategories', 'products']);
        if ($request->has('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            elseif ($request->status === 'inactive') $query->where('is_active', false);
        }
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        $categories = $query->orderBy('sort_order')->get();
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'featured' => Category::where('is_featured', true)->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'image_url' => 'nullable|url',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('categories', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name_en);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        Category::create([
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'slug' => $slug,
            'image' => $imagePath,
            'icon' => $request->icon ?? 'fa-solid fa-layer-group',
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'sort_order' => $request->sort_order ?? 0,
            'is_featured' => $request->boolean('is_featured', false),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'image_url' => 'nullable|url',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image_file')) {
            if ($category->image && !filter_var($category->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image_file')->store('categories', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name_en);

        $category->update([
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'slug' => $slug,
            'image' => $imagePath,
            'icon' => $request->icon ?? 'fa-solid fa-layer-group',
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'sort_order' => $request->sort_order ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->image && !filter_var($category->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();
        return response()->json(['success' => true, 'is_active' => $category->is_active]);
    }
}
