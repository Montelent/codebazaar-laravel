<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Support\Seo;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $seo = Seo::make($page->seoPayload());

        return view('pages.show', compact('page', 'seo'));
    }
}
