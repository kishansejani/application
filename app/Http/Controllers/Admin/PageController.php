<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();
        $stats = [
            'total' => Page::count(),
            'active' => Page::where('is_active', true)->count(),
            'draft' => Page::where('is_active', false)->count(),
            'policy' => Page::whereIn('slug', ['privacy-policy', 'terms-and-conditions', 'return-policy', 'legal-information'])->count(),
        ];
        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_gu' => 'required|string|max:255',
            'content_en' => 'required|string',
            'content_gu' => 'required|string',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_title_gu' => 'nullable|string|max:255',
            'meta_description_en' => 'nullable|string',
            'meta_description_gu' => 'nullable|string',
        ]);

        $page->update([
            'title_en' => $request->title_en,
            'title_gu' => $request->title_gu,
            'content_en' => $request->content_en,
            'content_gu' => $request->content_gu,
            'meta_title_en' => $request->meta_title_en,
            'meta_title_gu' => $request->meta_title_gu,
            'meta_description_en' => $request->meta_description_en,
            'meta_description_gu' => $request->meta_description_gu,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title_en}' updated successfully!");
    }
}
