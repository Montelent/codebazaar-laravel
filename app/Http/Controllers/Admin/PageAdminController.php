<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageAdminController extends Controller
{
    public function index()
    {
        $pages = CmsPage::orderBy('title')->paginate(40);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new CmsPage]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        CmsPage::create($data);
        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(CmsPage $page)
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, CmsPage $page)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $page->update($data);
        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(CmsPage $page)
    {
        $page->delete();
        return back()->with('success', 'Page deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:300',
        ]);
    }
}
