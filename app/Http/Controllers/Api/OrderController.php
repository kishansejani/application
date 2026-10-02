<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')->where('user_id', $request->user()->id)->latest()->paginate(15);

        $items = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_number' => $order->invoice_number,
                'total_amount' => (float) $order->total_amount,
                'order_status' => $order->order_status,
                'status_localized' => $order->localized_status,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'delivery_slot' => $order->delivery_slot,
                'delivery_type' => $order->delivery_type,
                'created_at' => $order->created_at->format('d M Y, h:i A'),
                'items_count' => $order->items->count(),
                'first_item_name' => $order->items->first()?->localized_name,
                'first_item_image' => $order->items->first()?->product_image,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $items,
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
        ]);
    }

    public function show(Request $request, $orderNumber)
    {
        $order = Order::with(['items', 'address'])->where('order_number', $orderNumber)->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_number' => $order->invoice_number,
                'order_status' => $order->order_status,
                'status_localized' => $order->localized_status,
                'delivery_type' => $order->delivery_type,
                'delivery_slot' => $order->delivery_slot,
                'estimated_delivery_at' => $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d M Y, h:i A') : null,
                'delivery_address' => $order->delivery_address,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'subtotal' => (float) $order->subtotal,
                'discount_amount' => (float) $order->discount_amount,
                'delivery_charge' => (float) $order->delivery_charge,
                'total_amount' => (float) $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('d M Y, h:i A'),
                'invoice_url' => route('order.invoice', $order->order_number),
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->localized_name,
                    'unit' => $item->product_unit,
                    'price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'total' => (float) $item->total_price,
                    'image' => $item->product_image,
                ]),
            ],
        ]);
    }
}
