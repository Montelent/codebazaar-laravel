<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = DB::table('wishlist_items')->where('user_id', $user->id)->pluck('item_id');
        $items = Item::whereIn('id', $ids)->orderByDesc('created_at')->get();

        return view('account.wishlist', compact('items'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['item_id' => 'required|exists:items,id']);
        $userId = $request->user()->id;
        $itemId = (int) $request->item_id;

        $exists = DB::table('wishlist_items')->where('user_id', $userId)->where('item_id', $itemId)->first();
        if ($exists) {
            DB::table('wishlist_items')->where('id', $exists->id)->delete();
            return back()->with('success', 'Removed from wishlist.');
        }
        DB::table('wishlist_items')->insert([
            'user_id' => $userId,
            'item_id' => $itemId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Added to wishlist.');
    }
}
