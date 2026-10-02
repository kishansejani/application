<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $totalRevenue = Order::where('payment_status', 'paid')
            ->orWhere('order_status', 'delivered')
            ->sum('total_amount');
            
        $todayRevenue = Order::whereDate('created_at', $today)
            ->where(function($q) {
                $q->where('payment_status', 'paid')->orWhere('order_status', '!=', 'cancelled');
            })->sum('total_amount');

        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock_quantity', '<=', DB::raw('low_stock_threshold'))->get();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $totalCustomers = User::where('role', 'customer')->count();
        $recentOrders = Order::with(['user', 'items'])->latest()->take(7)->get();
        $activeOffers = Offer::where('is_active', true)->count();

        // 7-Day Sales Trend
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayRevenue = Order::whereDate('created_at', $date)
                ->where('order_status', '!=', 'cancelled')
                ->sum('total_amount');
            $salesTrend['labels'][] = $date->format('d M');
            $salesTrend['data'][] = (float) $dayRevenue;
        }

        // Order Status counts
        $statusCounts = [
            'pending' => Order::where('order_status', 'pending')->count(),
            'confirmed' => Order::where('order_status', 'confirmed')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'out_for_delivery' => Order::where('order_status', 'out_for_delivery')->count(),
            'delivered' => Order::where('order_status', 'delivered')->count(),
            'cancelled' => Order::where('order_status', 'cancelled')->count(),
        ];

        return view('admin.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'totalOrders',
            'todayOrders',
            'pendingOrders',
            'totalProducts',
            'lowStockProducts',
            'outOfStockCount',
            'totalCustomers',
            'recentOrders',
            'activeOffers',
            'salesTrend',
            'statusCounts'
        ));
    }
}
