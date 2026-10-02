<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Offer;
use Illuminate\Http\Request;

class PageViewController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('frontend.pages.show', compact('page'));
    }

    public function offers()
    {
        $offers = Offer::where('is_active', true)->get();
        return view('frontend.pages.offers', compact('offers'));
    }
}
