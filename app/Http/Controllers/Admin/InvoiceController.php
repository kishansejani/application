<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'address']);
        
        if (empty($order->invoice_number)) {
            $order->invoice_number = 'INV-' . date('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();
        }

        return view('admin.invoices.show', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load(['items.product', 'user', 'address']);
        
        if (empty($order->invoice_number)) {
            $order->invoice_number = 'INV-' . date('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();
        }

        return view('admin.invoices.print', compact('order'));
    }
}
