<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::orderBy('sort_order')->get();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $offers = Offer::where('is_active', true)->get();
        return view('admin.sliders.create', compact('categories', 'products', 'offers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'image_url' => 'nullable|url',
            'title_en' => 'nullable|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('sliders', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        } else {
            return back()->withInput()->with('error', 'Please upload a slider banner image or provide an image URL.');
        }

        Slider::create([
            'title_en' => $request->title_en,
            'title_gu' => $request->title_gu,
            'subtitle_en' => $request->subtitle_en,
            'subtitle_gu' => $request->subtitle_gu,
            'badge_en' => $request->badge_en,
            'badge_gu' => $request->badge_gu,
            'image' => $imagePath,
            'link_type' => $request->link_type ?? 'none',
            'target_id' => $request->target_id,
            'link_url' => $request->link_url,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider banner created successfully!');
    }

    public function edit(Slider $slider)
    {
        $categories = Category::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $offers = Offer::where('is_active', true)->get();
        return view('admin.sliders.edit', compact('slider', 'categories', 'products', 'offers'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
            'image_url' => 'nullable|url',
            'title_en' => 'nullable|string|max:255',
            'title_gu' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $slider->image;
        if ($request->hasFile('image_file')) {
            if ($slider->image && !filter_var($slider->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }
            $imagePath = $request->file('image_file')->store('sliders', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $slider->update([
            'title_en' => $request->title_en,
            'title_gu' => $request->title_gu,
            'subtitle_en' => $request->subtitle_en,
            'subtitle_gu' => $request->subtitle_gu,
            'badge_en' => $request->badge_en,
            'badge_gu' => $request->badge_gu,
            'image' => $imagePath,
            'link_type' => $request->link_type ?? 'none',
            'target_id' => $request->target_id,
            'link_url' => $request->link_url,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->image && !filter_var($slider->image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }
        $slider->delete();
        return redirect()->route('admin.sliders.index')->with('success', 'Slider banner deleted successfully.');
    }

    public function toggleStatus(Slider $slider)
    {
        $slider->is_active = !$slider->is_active;
        $slider->save();
        return response()->json(['success' => true, 'is_active' => $slider->is_active]);
    }
}
