<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Review;
use App\Support\Seo;
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

        $seo = Seo::make($item->seoPayload());

        return view('items.show', compact('item', 'related', 'reviews', 'myReview', 'seo'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', $request->get('term', '')));
        $attrKey = trim((string) $request->get('attr', ''));
        $attrVal = trim((string) $request->get('val', ''));
        $tag = trim((string) $request->get('tag', ''));
        $sort = $request->get('sort', 'newest');
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        $view = $request->get('view', 'list');

        $query = Item::approved()->with(['category', 'author']);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }
        if ($tag !== '') {
            $query->where('tags', 'like', '%'.$tag.'%');
        }
        if ($attrKey !== '' && $attrVal !== '') {
            $query->where('attributes', 'like', '%'.$attrKey.'%')
                ->where('attributes', 'like', '%'.$attrVal.'%');
        }

        $this->applyPriceFilter($query, $priceMin, $priceMax);
        $this->applySort($query, $sort);

        $items = $query->paginate(24)->withQueryString();

        $sidebarCategories = Category::query()
            ->whereNull('parent_id')
            ->withCount(['items' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

        $title = $q !== '' ? 'Search: '.$q : 'Browse items';
        $seo = Seo::make([
            'title' => $title,
            'description' => $q !== ''
                ? 'Search results for '.$q.' on '.Seo::siteName()
                : 'Browse digital items on '.Seo::siteName(),
            'canonical' => route('search'),
            'robots' => $q !== '' ? 'noindex, follow' : 'index, follow',
        ]);

        return view('items.search', compact(
            'items', 'q', 'attrKey', 'attrVal', 'tag', 'sort', 'priceMin', 'priceMax', 'view', 'sidebarCategories', 'seo'
        ));
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::with(['parent', 'children'])->where('slug', $slug)->firstOrFail();

        $node = $category;
        $guard = 0;
        while ($node && $node->parent_id && $guard < 12) {
            if (! $node->relationLoaded('parent') || ! $node->parent) {
                $node->load('parent');
            }
            $node = $node->parent;
            $guard++;
        }

        $categoryIds = $this->descendantCategoryIds($category);
        $sort = $request->get('sort', 'newest');
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        $view = $request->get('view', 'list');
        $sub = trim((string) $request->get('sub', ''));

        if ($sub !== '') {
            $subCat = Category::where('slug', $sub)->where('parent_id', $category->id)->first();
            if ($subCat) {
                $categoryIds = $this->descendantCategoryIds($subCat);
            }
        }

        $query = Item::approved()
            ->with(['category', 'author'])
            ->whereIn('category_id', $categoryIds);

        $this->applyPriceFilter($query, $priceMin, $priceMax);
        $this->applySort($query, $sort);

        $items = $query->paginate(24)->withQueryString();

        $children = Category::query()
            ->where('parent_id', $category->id)
            ->withCount(['items' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

        $sidebarCategories = $children->isNotEmpty()
            ? $children
            : Category::query()
                ->whereNull('parent_id')
                ->withCount(['items' => fn ($q) => $q->where('status', 'approved')])
                ->orderBy('name')
                ->get();

        $seoTitle = $category->seo_title ?? $category->name;
        $seoDesc = $category->seo_description
            ?? (($category->description ?: $category->name.' items on '.Seo::siteName()));

        $seo = Seo::make([
            'title' => $seoTitle,
            'description' => $seoDesc,
            'canonical' => $category->canonical_url ?: route('category', $category->slug),
            'robots' => $category->robots ?? null,
        ]);

        return view('items.category', compact(
            'category', 'items', 'sidebarCategories', 'children', 'sort', 'priceMin', 'priceMax', 'view', 'sub', 'seo'
        ));
    }

    protected function applyPriceFilter($query, $priceMin, $priceMax): void
    {
        if ($priceMin !== null && $priceMin !== '') {
            $query->where(function ($q) use ($priceMin) {
                $q->where('sale_price_regular', '>=', (float) $priceMin)
                    ->orWhere(function ($q2) use ($priceMin) {
                        $q2->whereNull('sale_price_regular')->where('regular_price', '>=', (float) $priceMin);
                    });
            });
        }
        if ($priceMax !== null && $priceMax !== '') {
            $query->where(function ($q) use ($priceMax) {
                $q->where(function ($q2) use ($priceMax) {
                    $q2->whereNotNull('sale_price_regular')->where('sale_price_regular', '<=', (float) $priceMax);
                })->orWhere(function ($q2) use ($priceMax) {
                    $q2->whereNull('sale_price_regular')->where('regular_price', '<=', (float) $priceMax);
                });
            });
        }
    }

    protected function applySort($query, string $sort): void
    {
        match ($sort) {
            'popular', 'sales' => $query->orderByDesc('sales_count'),
            'price_asc' => $query->orderByRaw('COALESCE(sale_price_regular, regular_price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price_regular, regular_price) DESC'),
            'rating' => $query->orderByDesc('rating_avg'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };
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
