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
                $like = '%'.$q.'%';
                $query->where(function ($w) use ($like) {
                    $w->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('description', 'like', $like);
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

    /**
     * Filter items whose attributes JSON contains key + value.
     * Uses simple LIKE matching so it works on MySQL and SQLite without fragile JSON path quoting.
     */
    protected function applyAttributeFilter($query, string $key, string $value): void
    {
        $keyNeedle = '%'.$this->likeEscape($key).'%';
        $valNeedle = '%'.$this->likeEscape($value).'%';

        $query->where(function ($w) use ($keyNeedle, $valNeedle) {
            $w->where('attributes', 'like', $keyNeedle)
                ->where('attributes', 'like', $valNeedle);
        });
    }

    protected function applyJsonListContains($query, string $column, string $value): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                $query->whereJsonContains($column, $value);

                return;
            } catch (\Throwable $e) {
                // fall through to LIKE
            }
        }

        $query->where($column, 'like', '%'.$this->likeEscape($value).'%');
    }

    protected function likeEscape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
