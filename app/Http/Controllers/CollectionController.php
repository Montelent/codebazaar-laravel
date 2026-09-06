<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('collections')) {
            return view('account.collections', ['collections' => collect(), 'needsMigrate' => true]);
        }

        $collections = Collection::where('user_id', $request->user()->id)
            ->withCount('items')
            ->orderByDesc('updated_at')
            ->get();

        return view('account.collections', compact('collections'));
    }

    public function store(Request $request)
    {
        $this->ensureTables();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',
        ]);

        $slug = Str::slug($data['name']) ?: 'collection';
        $base = $slug;
        $i = 1;
        while (Collection::where('user_id', $request->user()->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        Collection::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return back()->with('success', 'Collection created.');
    }

    public function show(string $username, string $slug)
    {
        $this->ensureTables();
        $user = \App\Models\User::where('username', $username)->firstOrFail();
        $collection = Collection::where('user_id', $user->id)->where('slug', $slug)->firstOrFail();

        if (! $collection->is_public && (! auth()->check() || auth()->id() !== $user->id)) {
            abort(403);
        }

        $items = $collection->items()->approved()->orderByDesc('collection_items.created_at')->paginate(24);

        return view('collections.show', compact('collection', 'user', 'items'));
    }

    public function addItem(Request $request)
    {
        $this->ensureTables();
        $data = $request->validate([
            'collection_id' => 'required|exists:collections,id',
            'item_id' => 'required|exists:items,id',
        ]);

        $collection = Collection::where('id', $data['collection_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $collection->items()->syncWithoutDetaching([$data['item_id']]);

        return back()->with('success', 'Added to collection.');
    }

    public function removeItem(Request $request, Collection $collection, Item $item)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }
        $collection->items()->detach($item->id);

        return back()->with('success', 'Removed from collection.');
    }

    public function destroy(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403);
        }
        $collection->delete();

        return back()->with('success', 'Collection deleted.');
    }

    protected function ensureTables(): void
    {
        if (! Schema::hasTable('collections')) {
            abort(503, 'Run Admin → Run DB migrations first.');
        }
    }
}
