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

        $totalCustomers = User::where(function ($q) { $q->whereIn('role', ['customer', 'user'])->orWhereNull('role'); })->count();
        $newCustomersWeek = User::where(function ($q) { $q->whereIn('role', ['customer', 'user'])->orWhereNull('role'); })
            ->where('created_at', '>=', Carbon::today()->subDays(6))->count();
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

        // Week-over-week comparison & average order value
        $weekRevenue = Order::where('order_status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::today()->subDays(6))->sum('total_amount');
        $prevWeekRevenue = Order::where('order_status', '!=', 'cancelled')
            ->whereBetween('created_at', [Carbon::today()->subDays(13), Carbon::today()->subDays(6)])->sum('total_amount');
        $revenueChange = $prevWeekRevenue > 0 ? round((($weekRevenue - $prevWeekRevenue) / $prevWeekRevenue) * 100, 1) : null;
        $nonCancelled = Order::where('order_status', '!=', 'cancelled');
        $avgOrderValue = (clone $nonCancelled)->count() > 0 ? (float) (clone $nonCancelled)->avg('total_amount') : 0;
        $expressOpen = Order::where('delivery_type', 'two_hours')->whereNotIn('order_status', ['delivered', 'cancelled'])->count();

        // Top selling products (by quantity)
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->select('order_items.product_id', 'order_items.product_name_en as name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.total_price) as revenue'))
            ->groupBy('order_items.product_id', 'order_items.product_name_en')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

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
            'statusCounts',
            'newCustomersWeek',
            'weekRevenue',
            'revenueChange',
            'avgOrderValue',
            'expressOpen',
            'topProducts'
        ));
    }
}
