<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::with('product.category')->where('user_id', $request->user()->id)->latest()->get();

        $items = $wishlists->filter(fn($w) => $w->product !== null)->map(function ($w) {
            $p = $w->product;
            return [
                'wishlist_id' => $w->id,
                'product_id' => $p->id,
                'name' => $p->localized_name,
                'category_name' => $p->category ? $p->category->localized_name : '',
                'unit' => $p->unit,
                'price' => (float) $p->price,
                'effective_price' => (float) $p->effective_price,
                'thumbnail_url' => $p->thumbnail_url,
                'is_in_stock' => $p->is_in_stock,
            ];
        })->values();

        return response()->json(['status' => true, 'data' => $items]);
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wish = Wishlist::where('user_id', $request->user()->id)->where('product_id', $request->product_id)->first();
        if ($wish) {
            $wish->delete();
            $inWish = false;
            $msg = 'Removed from wishlist.';
        } else {
            Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);
            $inWish = true;
            $msg = 'Added to wishlist.';
        }

        return response()->json([
            'status' => true,
            'message' => $msg,
            'is_in_wishlist' => $inWish,
            'wishlist_count' => Wishlist::where('user_id', $request->user()->id)->count(),
        ]);
    }
}
