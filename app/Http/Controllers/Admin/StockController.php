<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name_en')->get();
        $query = Product::with(['category', 'subCategory'])->orderBy('stock_quantity', 'asc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'low') {
                $query->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            } elseif ($request->filter === 'out') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->filter === 'healthy') {
                $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
            }
        }

        $products = $query->get();

        $stats = [
            'total_items' => Product::sum('stock_quantity'),
            'low_stock_count' => Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'out_of_stock_count' => Product::where('stock_quantity', '<=', 0)->count(),
            'total_products' => Product::count(),
        ];

        return view('admin.stock.index', compact('products', 'categories', 'stats'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $product->update([
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? $product->low_stock_threshold,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully!',
                'new_stock' => $product->stock_quantity,
                'is_low' => $product->is_low_stock,
                'is_out' => !$product->is_in_stock,
            ]);
        }

        return back()->with('success', 'Stock updated successfully for ' . $product->name_en);
    }

    public function bulkAdjust(Request $request)
    {
        $request->validate([
            'adjustments' => 'required|array',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.type' => 'required|in:add,subtract,set',
            'adjustments.*.quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->adjustments as $adj) {
                $product = Product::find($adj['product_id']);
                if ($product) {
                    if ($adj['type'] === 'add') {
                        $product->increment('stock_quantity', $adj['quantity']);
                    } elseif ($adj['type'] === 'subtract') {
                        $product->decrement('stock_quantity', min($product->stock_quantity, $adj['quantity']));
                    } elseif ($adj['type'] === 'set') {
                        $product->update(['stock_quantity' => $adj['quantity']]);
                    }
                }
            }
        });

        return back()->with('success', 'Bulk inventory updated successfully!');
    }
}
