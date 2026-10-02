<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $sliders = Slider::where('is_active', true)->orderBy('sort_order')->get()->map(function ($s) use ($locale) {
            return [
                'id' => $s->id,
                'title' => $s->localized_title,
                'subtitle' => $s->localized_subtitle,
                'badge' => $s->localized_badge,
                'image_url' => $s->image_url,
                'link_type' => $s->link_type,
                'target_id' => $s->target_id,
            ];
        });

        $categories = Category::with('subCategories')->where('is_active', true)->orderBy('sort_order')->get()->map(function ($c) use ($locale) {
            return [
                'id' => $c->id,
                'name' => $c->localized_name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'image_url' => $c->image_url,
                'subcategories' => $c->subCategories->map(fn($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->localized_name,
                    'slug' => $sub->slug,
                    'image_url' => $sub->image_url,
                ]),
            ];
        });

        $featuredProducts = Product::with('category')->where('is_active', true)->where('is_featured', true)->take(8)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->localized_name,
                'slug' => $p->slug,
                'category_name' => $p->category ? $p->category->localized_name : '',
                'unit' => $p->unit,
                'price' => (float) $p->price,
                'discount_price' => $p->discount_price ? (float) $p->discount_price : null,
                'effective_price' => (float) $p->effective_price,
                'discount_percent' => $p->discount_percent,
                'thumbnail_url' => $p->thumbnail_url,
                'is_in_stock' => $p->is_in_stock,
                'stock_quantity' => $p->stock_quantity,
            ];
        });

        $offers = Offer::where('is_active', true)->get()->map(function ($o) {
            return [
                'id' => $o->id,
                'title' => $o->localized_title,
                'code' => $o->code,
                'discount_type' => $o->discount_type,
                'discount_value' => (float) $o->discount_value,
                'min_order_amount' => (float) $o->min_order_amount,
                'banner_url' => $o->banner_url,
                'description' => $o->localized_description,
            ];
        });

        $slot = Order::determineDeliverySlot();

        return response()->json([
            'status' => true,
            'data' => [
                'delivery_banner' => [
                    'type' => $slot['type'],
                    'slot' => $locale === 'gu' ? $slot['slot_gu'] : $slot['slot_en'],
                    'promise_text' => $locale === 'gu'
                        ? 'બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં ડિલિવરી મેળવો!'
                        : 'Order before 12 PM for 2-Hour Express Delivery!',
                ],
                'sliders' => $sliders,
                'categories' => $categories,
                'featured_products' => $featuredProducts,
                'offers' => $offers,
            ],
        ]);
    }
}
