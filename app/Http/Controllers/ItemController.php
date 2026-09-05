<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function show(string $slug, int $id)
    {
        $item = Item::with(['author', 'category'])
            ->where(function ($q) use ($slug, $id) {
                $q->where('id', $id)->orWhere('slug', $slug);
            })
            ->firstOrFail();

        $related = Item::approved()
            ->where('id', '!=', $item->id)
            ->when($item->category_id, fn ($q) => $q->where('category_id', $item->category_id))
            ->latest()
            ->take(4)
            ->get();

        return view('items.show', compact('item', 'related'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', $request->get('term', '')));
        $items = Item::approved()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('items.search', compact('items', 'q'));
    }

    public function category(string $slug)
    {
        $category = \App\Models\Category::where('slug', $slug)->firstOrFail();
        $items = Item::approved()->where('category_id', $category->id)->latest()->paginate(24);

        return view('items.category', compact('category', 'items'));
    }
}
