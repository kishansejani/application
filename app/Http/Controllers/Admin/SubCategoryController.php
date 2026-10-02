<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name_en')->get();
        $query = SubCategory::with('category')->withCount('products')->orderBy('sort_order');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            elseif ($request->status === 'inactive') $query->where('is_active', false);
        }

        $subCategories = $query->get();
        $stats = [
            'total' => SubCategory::count(),
            'active' => SubCategory::where('is_active', true)->count(),
            'inactive' => SubCategory::where('is_active', false)->count(),
            'categories' => Category::count(),
        ];

        return view('admin.subcategories.index', compact('subCategories', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name_en')->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:sub_categories,slug',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'image_url' => 'nullable|url',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('subcategories', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name_en);
        if (SubCategory::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        SubCategory::create([
            'category_id' => $request->category_id,
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'slug' => $slug,
            'image' => $imagePath,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategory created successfully!');
    }

    public function edit(SubCategory $subcategory)
    {
        $categories = Category::where('is_active', true)->orderBy('name_en')->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en' => 'required|string|max:255',
            'name_gu' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:sub_categories,slug,' . $subcategory->id,
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'image_url' => 'nullable|url',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $subcategory->image;
        if ($request->hasFile('image_file')) {
            if ($subcategory->image && !filter_var($subcategory->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($subcategory->image)) {
                Storage::disk('public')->delete($subcategory->image);
            }
            $imagePath = $request->file('image_file')->store('subcategories', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name_en);

        $subcategory->update([
            'category_id' => $request->category_id,
            'name_en' => $request->name_en,
            'name_gu' => $request->name_gu,
            'slug' => $slug,
            'image' => $imagePath,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategory updated successfully!');
    }

    public function destroy(SubCategory $subcategory)
    {
        if ($subcategory->image && !filter_var($subcategory->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($subcategory->image)) {
            Storage::disk('public')->delete($subcategory->image);
        }
        $subcategory->delete();
        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategory deleted successfully.');
    }

    public function getByCategory(Category $category)
    {
        return response()->json($category->subCategories);
    }
}
