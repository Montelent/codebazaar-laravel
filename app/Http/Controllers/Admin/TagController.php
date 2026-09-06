<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = SiteSetting::getValue('tags', [
            'React', 'Laravel', 'WordPress', 'Vue', 'PHP', 'HTML', 'SaaS', 'Dashboard', 'Next.js', 'Tailwind',
        ]);
        if (! is_array($tags)) {
            $tags = [];
        }
        $tags = array_values(array_unique(array_map('strval', $tags)));

        // Also surface tags used on products
        $used = [];
        Item::query()->select('tags')->whereNotNull('tags')->orderByDesc('id')->limit(500)->get()
            ->each(function ($item) use (&$used) {
                if (is_array($item->tags)) {
                    foreach ($item->tags as $t) {
                        $used[$t] = ($used[$t] ?? 0) + 1;
                    }
                }
            });

        return view('admin.tags.index', compact('tags', 'used'));
    }

    public function update(Request $request)
    {
        $raw = (string) $request->input('tags_text', '');
        $tags = array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $raw)))));
        SiteSetting::setValue('tags', $tags, 'taxonomy');

        return back()->with('success', count($tags).' tags saved.');
    }

    public function destroy(Request $request)
    {
        $tag = trim((string) $request->input('tag', ''));
        $tags = SiteSetting::getValue('tags', []);
        if (! is_array($tags)) {
            $tags = [];
        }
        $tags = array_values(array_filter($tags, fn ($t) => (string) $t !== $tag));
        SiteSetting::setValue('tags', $tags, 'taxonomy');

        return back()->with('success', 'Tag removed from master list.');
    }
}
