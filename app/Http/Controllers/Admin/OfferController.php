<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query();
        if ($request->has('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            elseif ($request->status === 'inactive') $query->where('is_active', false);
        }

        $offers = $query->latest()->get();
        $stats = [
            'total' => Offer::count(),
            'active' => Offer::where('is_active', true)->count(),
            'inactive' => Offer::where('is_active', false)->count(),
            'percentage' => Offer::where('discount_type', 'percentage')->count(),
            'flat' => Offer::where('discount_type', 'flat')->count(),
        ];

        return view('admin.offers.index', compact('offers', 'stats'));
    }

    public function create()
    {
        return view('admin.offers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_gu' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:offers,code',
            'discount_type' => 'required|in:percentage,flat',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_url' => 'nullable|url',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_file')) {
            $bannerPath = $request->file('banner_file')->store('offers', 'public');
        } elseif ($request->filled('banner_url')) {
            $bannerPath = $request->input('banner_url');
        }

        Offer::create([
            'title_en' => $request->title_en,
            'title_gu' => $request->title_gu,
            'code' => strtoupper($request->code),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'banner_image' => $bannerPath,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer coupon created successfully!');
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.edit', compact('offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_gu' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:offers,code,' . $offer->id,
            'discount_type' => 'required|in:percentage,flat',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'banner_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_url' => 'nullable|url',
        ]);

        $bannerPath = $offer->banner_image;
        if ($request->hasFile('banner_file')) {
            if ($offer->banner_image && !filter_var($offer->banner_image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($offer->banner_image)) {
                Storage::disk('public')->delete($offer->banner_image);
            }
            $bannerPath = $request->file('banner_file')->store('offers', 'public');
        } elseif ($request->filled('banner_url')) {
            $bannerPath = $request->input('banner_url');
        }

        $offer->update([
            'title_en' => $request->title_en,
            'title_gu' => $request->title_gu,
            'code' => strtoupper($request->code),
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'banner_image' => $bannerPath,
            'description_en' => $request->description_en,
            'description_gu' => $request->description_gu,
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully!');
    }

    public function destroy(Offer $offer)
    {
        if ($offer->banner_image && !filter_var($offer->banner_image, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($offer->banner_image)) {
            Storage::disk('public')->delete($offer->banner_image);
        }
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully.');
    }

    public function toggleStatus(Offer $offer)
    {
        $offer->is_active = !$offer->is_active;
        $offer->save();
        return response()->json(['success' => true, 'is_active' => $offer->is_active]);
    }
}
