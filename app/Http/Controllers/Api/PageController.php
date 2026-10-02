<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'slug' => $page->slug,
                'title' => $page->localized_title,
                'content' => $page->localized_content,
            ],
        ]);
    }
}
