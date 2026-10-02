<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::where('is_active', true)->get()->map(function ($o) {
            return [
                'id' => $o->id,
                'title' => $o->localized_title,
                'code' => $o->code,
                'discount_type' => $o->discount_type,
                'discount_value' => (float) $o->discount_value,
                'min_order_amount' => (float) $o->min_order_amount,
                'max_discount_amount' => (float) $o->max_discount_amount,
                'banner_url' => $o->banner_url,
                'description' => $o->localized_description,
                'valid_to' => $o->valid_to ? $o->valid_to->format('d M Y') : null,
            ];
        });

        return response()->json(['status' => true, 'data' => $offers]);
    }
}
