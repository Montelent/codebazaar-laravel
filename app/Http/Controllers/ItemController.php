<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $reviews = collect();
        $myReview = null;
        if (Schema::hasTable('reviews')) {
            $reviews = Review::with('user')->where('item_id', $item->id)->orderByDesc('created_at')->get();
            if (auth()->check()) {
                $myReview = $reviews->firstWhere('user_id', auth()->id());
            }
        }

        return view('items.show', compact('item', 'related', 'reviews', 'myReview'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', $request->get('term', '')));
        $attrKey = trim((string) $request->get('attr', ''));
        $attrVal = trim((string) $request->get('val', ''));
        $tag = trim((string) $request->get('tag', ''));

        $items = Item::approved()
            ->with(['category', 'author'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($tag !== '', function ($query) use ($tag) {
                $this->applyJsonListContains($query, 'tags', $tag);
            })
            ->when($attrKey !== '' && $attrVal !== '', function ($query) use ($attrKey, $attrVal) {
                $this->applyAttributeFilter($query, $attrKey, $attrVal);
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('items.search', compact('items', 'q', 'attrKey', 'attrVal', 'tag'));
    }

    public function category(string $slug)
    {
        $category = \App\Models\Category::where('slug', $slug)->firstOrFail();
        $items = Item::approved()->where('category_id', $category->id)->latest()->paginate(24);

        return view('items.category', compact('category', 'items'));
    }

    /** Filter items whose attributes JSON has key → value (value may be in an array). */
    protected function applyAttributeFilter($query, string $key, string $value): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // attributes is object: { "Compatible Browsers": ["Chrome", "Firefox"] }
            $path = '$."'.str_replace(['\', '"'], ['\\', '\"'], $key).'"';
            $query->where(function ($w) use ($path, $value) {
                $w->whereRaw('JSON_CONTAINS(JSON_EXTRACT(attributes, ?), JSON_QUOTE(?))', [$path, $value])
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(attributes, ?)) = ?', [$path, $value]);
            });

            return;
        }

        // SQLite / others: tolerant LIKE match on serialized JSON
        $query->where(function ($w) use ($key, $value) {
            $w->where('attributes', 'like', '%"'.addcslashes($key, '%_\').'"%')
                ->where('attributes', 'like', '%"'.addcslashes($value, '%_\').'"%');
        });
    }

    protected function applyJsonListContains($query, string $column, string $value): void
    {
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $query->whereJsonContains($column, $value);

            return;
        }
        $query->where($column, 'like', '%"'.addcslashes($value, '%_\').'"%');
    }
}
