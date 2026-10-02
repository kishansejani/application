<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOrderController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login')->with('warning', 'Please login to view your order history.');
        }

        $orders = Order::with('items')->where('user_id', Auth::id())->latest()->paginate(10);
        return view('frontend.orders.index', compact('orders'));
    }

    public function show($orderNumber)
    {
        $order = Order::with(['items.product', 'address'])->where('order_number', $orderNumber);
        
        if (Auth::check()) {
            $order = $order->where(function($q) {
                $q->where('user_id', Auth::id())->orWhere('customer_phone', Auth::user()->phone);
            });
        }
        
        $order = $order->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }

    public function invoice($orderNumber)
    {
        $order = Order::with(['items.product', 'address', 'user'])->where('order_number', $orderNumber)->firstOrFail();
        return view('frontend.orders.invoice', compact('order'));
    }

    public function track(Request $request)
    {
        $order = null;
        if ($request->filled('order_number')) {
            $order = Order::with(['items', 'address'])->where('order_number', trim($request->order_number))->first();
            if (!$order) {
                return back()->with('error', 'No order found with this order number.');
            }
        }
        return view('frontend.orders.track', compact('order'));
    }
}
