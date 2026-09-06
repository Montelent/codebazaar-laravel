<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('pages.show', compact('page'));
    }
}
