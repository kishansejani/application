<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Address;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    private function getCartItems()
    {
        if (Auth::check()) {
            return CartItem::with('product')->where('user_id', Auth::id())->get();
        }
        $sessionId = Session::getId();
        return CartItem::with('product')->where('session_id', $sessionId)->get();
    }

    public function index(Request $request)
    {
        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty. Please add items to checkout.');
        }

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $deliverySlotInfo = Order::determineDeliverySlot();

        $addresses = Auth::check() ? Auth::user()->addresses : collect();
        $defaultAddress = Auth::check() ? Auth::user()->addresses()->where('is_default', true)->first() : null;

        $appliedCoupon = Session::get('checkout_coupon');
        $discount = 0.0;
        if ($appliedCoupon) {
            $offer = Offer::where('code', $appliedCoupon)->where('is_active', true)->first();
            if ($offer && $offer->isValidForAmount($subtotal)) {
                $discount = $offer->calculateDiscount($subtotal);
            } else {
                Session::forget('checkout_coupon');
                $appliedCoupon = null;
            }
        }

        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $deliveryCharge);

        return view('frontend.checkout.index', compact(
            'cartItems',
            'subtotal',
            'discount',
            'appliedCoupon',
            'deliveryCharge',
            'total',
            'deliverySlotInfo',
            'addresses',
            'defaultAddress'
        ));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $code = strtoupper(trim($request->coupon_code));
        $offer = Offer::where('code', $code)->where('is_active', true)->first();

        $cartItems = $this->getCartItems();
        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code.',
            ], 422);
        }

        if (!$offer->isValidForAmount($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => "This coupon requires a minimum order value of ₹{$offer->min_order_amount}.",
            ], 422);
        }

        $discount = $offer->calculateDiscount($subtotal);
        Session::put('checkout_coupon', $code);

        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $deliveryCharge);

        return response()->json([
            'success' => true,
            'message' => "Coupon '{$code}' applied! You saved ₹" . number_format($discount, 2),
            'discount' => (float) $discount,
            'discount_formatted' => '₹' . number_format($discount, 2),
            'total' => (float) $total,
            'total_formatted' => '₹' . number_format($total, 2),
            'coupon_code' => $code,
        ]);
    }

    public function removeCoupon()
    {
        Session::forget('checkout_coupon');
        return response()->json(['success' => true, 'message' => 'Coupon removed']);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'customer_email' => 'nullable|email',
            'payment_method' => 'required|in:cod,upi,card',
            
            // Address details
            'selected_address_id' => 'nullable|exists:addresses,id',
            'house_no' => 'required_without:selected_address_id|nullable|string',
            'street_address' => 'required_without:selected_address_id|nullable|string',
            'landmark' => 'nullable|string',
            'city' => 'required_without:selected_address_id|nullable|string',
            'pincode' => 'required_without:selected_address_id|nullable|regex:/^[0-9]{6}$/',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => __('messages.validation.name_required') ?: 'Please enter your full name.',
            'customer_phone.required' => __('messages.validation.phone_required') ?: 'Please enter your 10-digit mobile number.',
            'customer_phone.regex' => __('messages.validation.phone_invalid') ?: 'Please enter a valid 10-digit mobile number.',
            'house_no.required_without' => __('messages.validation.house_required') ?: 'Please enter your House / Flat / Building number.',
            'street_address.required_without' => __('messages.validation.street_required') ?: 'Please enter your Street / Area / Locality.',
            'city.required_without' => __('messages.validation.city_required') ?: 'Please enter your City.',
            'pincode.required_without' => __('messages.validation.pincode_required') ?: 'Please enter a valid 6-digit Pincode.',
            'pincode.regex' => __('messages.validation.pincode_invalid') ?: 'Pincode must be exactly 6 digits.',
        ]);

        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Sorry, '{$item->product->localized_name}' only has {$item->product->stock_quantity} units available.");
            }
        }

        $subtotal = $cartItems->sum(fn($item) => $item->subtotal);
        $appliedCoupon = Session::get('checkout_coupon');
        $discount = 0.0;
        if ($appliedCoupon) {
            $offer = Offer::where('code', $appliedCoupon)->where('is_active', true)->first();
            if ($offer && $offer->isValidForAmount($subtotal)) {
                $discount = $offer->calculateDiscount($subtotal);
                $offer->increment('used_count');
            }
        }

        $deliveryCharge = $subtotal >= 499 ? 0 : 40;
        $total = max(0, $subtotal - $discount + $deliveryCharge);

        // Calculate delivery slot according to rule:
        // "If before 12 pm then deliver 2 hours else next day"
        $slotInfo = Order::determineDeliverySlot();

        $order = DB::transaction(function () use ($request, $cartItems, $subtotal, $discount, $appliedCoupon, $deliveryCharge, $total, $slotInfo) {
            $user = Auth::user();
            $addressId = null;
            $fullAddress = '';

            if ($request->filled('selected_address_id') && Auth::check()) {
                $addr = Address::where('id', $request->selected_address_id)->where('user_id', Auth::id())->first();
                if ($addr) {
                    $addressId = $addr->id;
                    $fullAddress = $addr->full_address;
                    $city = $addr->city;
                    $pincode = $addr->pincode;
                    $lat = $addr->latitude;
                    $lng = $addr->longitude;
                }
            }

            if (empty($fullAddress)) {
                $fullAddress = trim("{$request->house_no}, {$request->street_address}" . ($request->landmark ? ", Near {$request->landmark}" : "") . ", {$request->city}, Gujarat - {$request->pincode}");
                $city = $request->city;
                $pincode = $request->pincode;
                $lat = $request->latitude;
                $lng = $request->longitude;

                // Save address if user is logged in
                if (Auth::check()) {
                    $savedAddr = Address::create([
                        'user_id' => Auth::id(),
                        'type' => $request->address_type ?? 'Home',
                        'recipient_name' => $request->customer_name,
                        'recipient_phone' => $request->customer_phone,
                        'house_no' => $request->house_no,
                        'street_address' => $request->street_address,
                        'landmark' => $request->landmark,
                        'city' => $city,
                        'pincode' => $pincode,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'formatted_address' => $fullAddress,
                        'is_default' => Address::where('user_id', Auth::id())->count() === 0,
                    ]);
                    $addressId = $savedAddr->id;
                }
            }

            // Create Order
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email ?? (Auth::user()?->email),
                'address_id' => $addressId,
                'delivery_address' => $fullAddress,
                'delivery_city' => $city ?? 'Ahmedabad',
                'delivery_pincode' => $pincode ?? '380001',
                'delivery_lat' => $lat ?? null,
                'delivery_lng' => $lng ?? null,
                'delivery_type' => $slotInfo['type'],
                'delivery_slot' => app()->getLocale() === 'gu' ? $slotInfo['slot_gu'] : $slotInfo['slot_en'],
                'estimated_delivery_at' => $slotInfo['estimated_at'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $appliedCoupon,
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

            // Insert Order Items and reduce product stock
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

                // Decrement stock
                $item->product->decrement('stock_quantity', $item->quantity);
            }

            // Clear Cart & Coupon Session
            if (Auth::check()) {
                CartItem::where('user_id', Auth::id())->delete();
            } else {
                CartItem::where('session_id', Session::getId())->delete();
            }
            Session::forget('checkout_coupon');

            return $order;
        });

        return redirect()->route('order.confirmed', $order->order_number)->with('success', __('messages.order_confirmed'));
    }

    public function confirmed($orderNumber)
    {
        $order = Order::with(['items.product', 'user', 'address'])->where('order_number', $orderNumber)->firstOrFail();
        return view('frontend.checkout.confirmed', compact('order'));
    }
}
