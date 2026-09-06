<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, int $itemId)
    {
        $item = Item::findOrFail($itemId);
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = Review::updateOrCreate(
            [
                'item_id' => $item->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? '',
            ]
        );

        $this->recalculateItemRating($item);

        return back()->with('success', $review->wasRecentlyCreated ? 'Review submitted.' : 'Review updated.');
    }

    public function destroy(Request $request, Review $review)
    {
        $item = $review->item;
        if ($request->user()->id !== $review->user_id && ! $request->user()->isAdmin()) {
            abort(403);
        }
        $review->delete();
        if ($item) {
            $this->recalculateItemRating($item);
        }

        return back()->with('success', 'Review removed.');
    }

    protected function recalculateItemRating(Item $item): void
    {
        $stats = Review::where('item_id', $item->id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as cnt')
            ->first();

        $item->update([
            'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->cnt ?? 0),
        ]);
    }
}
