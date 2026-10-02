<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Address;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function getDeliverySlot()
    {
        $slot = Order::determineDeliverySlot();
        $locale = app()->getLocale();

        return response()->json([
            'status' => true,
            'data' => [
                'type' => $slot['type'],
                'slot_text' => $locale === 'gu' ? $slot['slot_gu'] : $slot['slot_en'],
                'slot_en' => $slot['slot_en'],
                'slot_gu' => $slot['slot_gu'],
                'cutoff_time' => '12:00 PM',
                'rule_description' => $locale === 'gu' 
                    ? 'બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં મેળવો. ૧૨ વાગ્યા પછી આવતીકાલે મળશે.'
                    : 'Orders before 12:00 PM are delivered within 2 hours. Orders after 12:00 PM deliver next day.',
            ],
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();
        $cartItems = CartItem::where('user_id', $user->id)->get();
        $subtotal = $cartItems->sum(fn($i) => $i->subtotal);

        $offer = Offer::where('code', strtoupper(trim($request->code)))->where('is_active', true)->first();

        if (!$offer) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired coupon code.'], 422);
        }

        if (!$offer->isValidForAmount($subtotal)) {
            return response()->json([
                'status' => false,
                'message' => "This coupon requires a minimum cart amount of ₹{$offer->min_order_amount}.",
            ], 422);
        }

        $discount = $offer->calculateDiscount($subtotal);
        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $deliveryCharge);

        return response()->json([
            'status' => true,
            'message' => "Coupon '{$offer->code}' applied successfully!",
            'data' => [
                'code' => $offer->code,
                'discount' => (float) $discount,
                'subtotal' => (float) $subtotal,
                'delivery_charge' => (float) $deliveryCharge,
                'total' => (float) $total,
            ],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cod,upi,card',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $address = Address::where('id', $request->address_id)->where('user_id', $user->id)->firstOrFail();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty.'], 422);
        }

        // Check stock
        foreach ($cartItems as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return response()->json([
                    'status' => false,
                    'message' => "Product '{$item->product->localized_name}' has only {$item->product->stock_quantity} in stock.",
                ], 422);
            }
        }

        $subtotal = $cartItems->sum(fn($i) => $i->subtotal);
        $discount = 0.0;
        $couponCode = null;

        if ($request->filled('coupon_code')) {
            $offer = Offer::where('code', strtoupper(trim($request->coupon_code)))->where('is_active', true)->first();
            if ($offer && $offer->isValidForAmount($subtotal)) {
                $discount = $offer->calculateDiscount($subtotal);
                $couponCode = $offer->code;
                $offer->increment('used_count');
            }
        }

        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $deliveryCharge);
        $slotInfo = Order::determineDeliverySlot();

        $order = DB::transaction(function () use ($user, $address, $cartItems, $subtotal, $discount, $couponCode, $deliveryCharge, $total, $slotInfo, $request) {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'customer_name' => $address->recipient_name ?: $user->name,
                'customer_phone' => $address->recipient_phone ?: $user->phone,
                'customer_email' => $user->email,
                'address_id' => $address->id,
                'delivery_address' => $address->full_address,
                'delivery_city' => $address->city,
                'delivery_pincode' => $address->pincode,
                'delivery_lat' => $address->latitude,
                'delivery_lng' => $address->longitude,
                'delivery_type' => $slotInfo['type'],
                'delivery_slot' => app()->getLocale() === 'gu' ? $slotInfo['slot_gu'] : $slotInfo['slot_en'],
                'estimated_delivery_at' => $slotInfo['estimated_at'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $couponCode,
                'delivery_charge' => $deliveryCharge,
                'tax_amount' => 0.00,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'paid',
                'transaction_id' => $request->payment_method !== 'cod' ? ('TXN-' . strtoupper(uniqid())) : null,
                'order_status' => 'pending',
                'notes' => $request->notes,
            ]);

            $order->invoice_number = 'INV-' . date('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name_en' => $item->product->name_en,
                    'product_name_gu' => $item->product->name_gu,
                    'product_unit' => $item->product->unit,
                    'product_image' => $item->product->thumbnail,
                    'unit_price' => $item->product->effective_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->subtotal,
                ]);

                $item->product->decrement('stock_quantity', $item->quantity);
            }

            CartItem::where('user_id', $user->id)->delete();

            return $order;
        });

        return response()->json([
            'status' => true,
            'message' => 'Order placed successfully!',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_number' => $order->invoice_number,
                'total_amount' => (float) $order->total_amount,
                'delivery_slot' => $order->delivery_slot,
                'estimated_delivery_at' => $order->estimated_delivery_at ? $order->estimated_delivery_at->toDateTimeString() : null,
                'delivery_type' => $order->delivery_type,
            ],
        ]);
    }
}
