<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthorController extends Controller
{
    public function show(string $username)
    {
        $author = User::where('username', $username)->orWhere('id', $username)->firstOrFail();
        $items = Item::approved()->where('author_id', $author->id)->orderByDesc('created_at')->paginate(24);

        $followers = 0;
        $following = 0;
        $isFollowing = false;
        if (Schema::hasTable('follows')) {
            $followers = DB::table('follows')->where('following_id', $author->id)->count();
            $following = DB::table('follows')->where('follower_id', $author->id)->count();
            if (auth()->check()) {
                $isFollowing = DB::table('follows')
                    ->where('follower_id', auth()->id())
                    ->where('following_id', $author->id)
                    ->exists();
            }
        }

        $collections = collect();
        if (Schema::hasTable('collections')) {
            $collections = Collection::where('user_id', $author->id)
                ->where('is_public', true)
                ->withCount('items')
                ->orderByDesc('updated_at')
                ->take(12)
                ->get();
        }

        return view('author.show', compact(
            'author', 'items', 'followers', 'following', 'isFollowing', 'collections'
        ));
    }
}
