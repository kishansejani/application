<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cartItems = CartItem::with('product.category')->where('user_id', $user->id)->get();

        $subtotal = $cartItems->sum(fn($i) => $i->subtotal);
        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $slotInfo = Order::determineDeliverySlot();

        $items = $cartItems->map(function ($item) {
            return [
                'cart_item_id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->localized_name,
                'price' => (float) $item->product->effective_price,
                'mrp' => (float) $item->product->price,
                'unit' => $item->product->unit,
                'quantity' => $item->quantity,
                'max_stock' => $item->product->stock_quantity,
                'thumbnail_url' => $item->product->thumbnail_url,
                'item_total' => (float) $item->subtotal,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'items' => $items,
                'count' => $cartItems->sum('quantity'),
                'subtotal' => (float) $subtotal,
                'delivery_charge' => (float) $deliveryCharge,
                'free_delivery_threshold' => 499,
                'delivery_slot' => app()->getLocale() === 'gu' ? $slotInfo['slot_gu'] : $slotInfo['slot_en'],
                'delivery_type' => $slotInfo['type'],
            ],
        ]);
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
            return response()->json(['status' => false, 'message' => "Only {$product->stock_quantity} in stock."], 422);
        }

        $cartItem = CartItem::where('user_id', $request->user()->id)->where('product_id', $product->id)->first();
        if ($cartItem) {
            $newQty = min($cartItem->quantity + $qty, $product->stock_quantity);
            $cartItem->quantity = $newQty;
            $cartItem->save();
        } else {
            CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'quantity' => $qty,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart.',
            'cart_count' => CartItem::where('user_id', $request->user()->id)->sum('quantity'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cartItem = CartItem::where('user_id', $request->user()->id)->where('product_id', $request->product_id)->first();
        if (!$cartItem) {
            return response()->json(['status' => false, 'message' => 'Item not found in cart.'], 404);
        }

        if ($request->quantity <= 0) {
            $cartItem->delete();
            $msg = 'Item removed from cart';
        } else {
            $product = $cartItem->product;
            if ($request->quantity > $product->stock_quantity) {
                return response()->json(['status' => false, 'message' => "Only {$product->stock_quantity} available in stock."], 422);
            }
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            $msg = 'Cart updated';
        }

        return response()->json([
            'status' => true,
            'message' => $msg,
            'cart_count' => CartItem::where('user_id', $request->user()->id)->sum('quantity'),
        ]);
    }

    public function remove(Request $request, $productId)
    {
        CartItem::where('user_id', $request->user()->id)->where('product_id', $productId)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => CartItem::where('user_id', $request->user()->id)->sum('quantity'),
        ]);
    }
}
