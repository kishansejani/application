<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    private function getCartQuery()
    {
        if (Auth::check()) {
            return CartItem::with('product')->where('user_id', Auth::id());
        }
        $sessionId = Session::getId();
        return CartItem::with('product')->where('session_id', $sessionId);
    }

    public function index()
    {
        $cartItems = $this->getCartQuery()->get();
        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $deliverySlotInfo = Order::determineDeliverySlot();

        return view('frontend.cart.index', compact('cartItems', 'subtotal', 'deliverySlotInfo'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty = $request->quantity ?? 1;

        if ($product->stock_quantity < $qty) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, only ' . $product->stock_quantity . ' items available in stock.',
            ], 422);
        }

        $userId = Auth::id();
        $sessionId = Session::getId();

        $cartItem = CartItem::where(function ($q) use ($userId, $sessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('session_id', $sessionId);
            }
        })->where('product_id', $product->id)->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $qty;
            if ($newQty > $product->stock_quantity) {
                $newQty = $product->stock_quantity;
            }
            $cartItem->quantity = $newQty;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'product_id' => $product->id,
                'quantity' => $qty,
            ]);
        }

        $cartCount = $this->getCartQuery()->sum('quantity');
        $cartSubtotal = $this->getCartQuery()->get()->sum(fn($item) => $item->subtotal);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.added_to_cart'),
                'cart_count' => $cartCount,
                'cart_subtotal' => number_format($cartSubtotal, 2),
                'item_name' => $product->localized_name,
            ]);
        }

        return back()->with('success', __('messages.added_to_cart'));
    }

    public function updateQuantity(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        if ($request->quantity <= 0) {
            $cartItem->delete();
            $msg = 'Item removed from cart';
        } else {
            $product = $cartItem->product;
            if ($request->quantity > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$product->stock_quantity} units available in stock.",
                ], 422);
            }
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            $msg = 'Cart updated successfully';
        }

        $cartItems = $this->getCartQuery()->get();
        $cartCount = $cartItems->sum('quantity');
        $cartSubtotal = $cartItems->sum(fn($item) => $item->subtotal);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'cart_count' => $cartCount,
            'cart_subtotal' => number_format($cartSubtotal, 2),
            'item_subtotal' => $cartItem->exists ? number_format($cartItem->subtotal, 2) : 0,
        ]);
    }

    public function remove(CartItem $cartItem)
    {
        $cartItem->delete();

        if (request()->wantsJson() || request()->ajax()) {
            $cartItems = $this->getCartQuery()->get();
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cartItems->sum('quantity'),
                'cart_subtotal' => number_format($cartItems->sum(fn($item) => $item->subtotal), 2),
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function getDrawerData()
    {
        $cartItems = $this->getCartQuery()->get();
        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $deliverySlotInfo = Order::determineDeliverySlot();

        $items = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->localized_name,
                'price' => (float) $item->product->effective_price,
                'quantity' => $item->quantity,
                'unit' => $item->product->unit,
                'thumbnail' => $item->product->thumbnail_url,
                'subtotal' => (float) $item->subtotal,
            ];
        });

        return response()->json([
            'items' => $items,
            'count' => $cartItems->sum('quantity'),
            'subtotal' => (float) $subtotal,
            'formatted_subtotal' => '₹' . number_format($subtotal, 2),
            'delivery_slot' => app()->getLocale() === 'gu' ? $deliverySlotInfo['slot_gu'] : $deliverySlotInfo['slot_en'],
            'delivery_type' => $deliverySlotInfo['type'],
        ]);
    }
}
