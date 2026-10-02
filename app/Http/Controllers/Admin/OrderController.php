<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items'])->latest();

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->get();

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('order_status', 'pending')->count(),
            'processing' => Order::whereIn('order_status', ['confirmed', 'processing'])->count(),
            'out_for_delivery' => Order::where('order_status', 'out_for_delivery')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'two_hour_express' => Order::where('delivery_type', 'two_hours')->whereNotIn('order_status', ['delivered', 'cancelled'])->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'address']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string',
            'cancellation_reason' => 'nullable|string',
        ]);

        $oldStatus = $order->order_status;
        $newStatus = $request->order_status;

        DB::transaction(function () use ($order, $newStatus, $oldStatus, $request) {
            // If order was cancelled, restore product stocks
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            // Auto-mark payment as paid if delivered & COD
            $paymentStatus = $order->payment_status;
            if ($newStatus === 'delivered' && $order->payment_method === 'cod') {
                $paymentStatus = 'paid';
            }

            $order->update([
                'order_status' => $newStatus,
                'payment_status' => $paymentStatus,
                'notes' => $request->filled('notes') ? $request->notes : $order->notes,
                'cancellation_reason' => $newStatus === 'cancelled' ? $request->cancellation_reason : null,
            ]);
        });

        return back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst(str_replace('_', ' ', $newStatus)));
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'transaction_id' => 'nullable|string',
        ]);

        $order->update([
            'payment_status' => $request->payment_status,
            'transaction_id' => $request->transaction_id ?? $order->transaction_id,
        ]);

        return back()->with('success', "Payment status updated successfully!");
    }
}
