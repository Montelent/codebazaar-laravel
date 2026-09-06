<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;

class AuthorController extends Controller
{
    public function show(string $username)
    {
        $author = User::where('username', $username)->orWhere('id', $username)->firstOrFail();
        $items = Item::approved()->where('author_id', $author->id)->orderByDesc('created_at')->paginate(24);

        return view('author.show', compact('author', 'items'));
    }
}
