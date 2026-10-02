<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get();
        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('sort_order')->get();
        $featuredProducts = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->take(8)
            ->get();

        $discountedProducts = Product::with(['category', 'images'])
            ->where('is_active', true)
            ->whereNotNull('discount_price')
            ->where('discount_price', '>', 0)
            ->take(8)
            ->get();

        $activeOffers = Offer::where('is_active', true)->take(3)->get();
        
        // Calculate current delivery timing slot
        $deliverySlotInfo = Order::determineDeliverySlot();

        return view('frontend.home', compact(
            'sliders',
            'categories',
            'featuredProducts',
            'discountedProducts',
            'activeOffers',
            'deliverySlotInfo'
        ));
    }
}
