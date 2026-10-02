<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login')->with('warning', 'Please login to view your wishlist.');
        }

        $wishlists = Wishlist::with('product.category')->where('user_id', Auth::id())->latest()->get();
        return view('frontend.wishlist.index', compact('wishlists'));
    }

    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'require_login' => true,
                'message' => 'Please login to save items to your wishlist.',
            ], 401);
        }

        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ]);
            $inWishlist = true;
            $message = 'Added to wishlist!';
        }

        $count = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist,
            'wishlist_count' => $count,
            'message' => $message,
        ]);
    }
}
