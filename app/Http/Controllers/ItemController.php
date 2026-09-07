<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ItemController extends Controller
{
    public function show(string $slug, int $id)
    {
        $item = Item::with(['author', 'category.parent'])
            ->where(function ($q) use ($slug, $id) {
                $q->where('id', $id)->orWhere('slug', $slug);
            })
            ->firstOrFail();

        // Load full parent chain for deep subcategories
        if ($item->category) {
            $node = $item->category;
            $guard = 0;
            while ($node && $node->parent_id && $guard < 12) {
                if (! $node->relationLoaded('parent') || ! $node->parent) {
                    $node->load('parent');
                }
                $node = $node->parent;
                $guard++;
            }
        }

        $related = Item::approved()
            ->where('id', '!=', $item->id)
            ->when($item->category_id, function ($q) use ($item) {
                $q->where('category_id', $item->category_id);
            })
            ->latest()
            ->take(4)
            ->get();

        $reviews = collect();
        $myReview = null;

        if (Schema::hasTable('reviews')) {
            $reviews = Review::with('user')
                ->where('item_id', $item->id)
                ->orderByDesc('created_at')
                ->get();

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
                $query->where('tags', 'like', '%'.$tag.'%');
            })
            ->when($attrKey !== '' && $attrVal !== '', function ($query) use ($attrKey, $attrVal) {
                $query->where('attributes', 'like', '%'.$attrKey.'%')
                    ->where('attributes', 'like', '%'.$attrVal.'%');
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('items.search', compact('items', 'q', 'attrKey', 'attrVal', 'tag'));
    }

    public function category(string $slug)
    {
        $category = Category::with('parent')->where('slug', $slug)->firstOrFail();

        $node = $category;
        $guard = 0;
        while ($node && $node->parent_id && $guard < 12) {
            if (! $node->relationLoaded('parent') || ! $node->parent) {
                $node->load('parent');
            }
            $node = $node->parent;
            $guard++;
        }

        // Include items in this category and all descendant subcategories
        $categoryIds = $this->descendantCategoryIds($category);

        $items = Item::approved()
            ->whereIn('category_id', $categoryIds)
            ->latest()
            ->paginate(24);

        return view('items.category', compact('category', 'items'));
    }

    /** @return array<int, int> */
    protected function descendantCategoryIds(Category $category): array
    {
        $ids = [$category->id];
        $queue = [$category->id];

        while ($queue) {
            $parentId = array_shift($queue);
            $children = Category::where('parent_id', $parentId)->pluck('id')->all();
            foreach ($children as $childId) {
                if (! in_array($childId, $ids, true)) {
                    $ids[] = $childId;
                    $queue[] = $childId;
                }
            }
        }

        return $ids;
    }
}
