<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->latest()->paginate(30);
        return view('admin.products.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.form', ['item' => new Item(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        if (! empty($data['is_free'])) {
            $data['regular_price'] = 0;
            $data['extended_price'] = 0;
            $data['sale_price_regular'] = null;
        }
        $data['author_id'] = $request->user()->id;
        $data['status'] = 'approved';
        Item::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Item $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.form', ['item' => $product, 'categories' => $categories]);
    }

    public function update(Request $request, Item $product)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        if (! empty($data['is_free'])) {
            $data['regular_price'] = 0;
            $data['extended_price'] = 0;
            $data['sale_price_regular'] = null;
            $data['is_free'] = true;
        }
        $product->update($data);
        return redirect()->route('admin.products.edit', $product)->with('success', 'Product saved.');
    }

    public function destroy(Item $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'regular_price' => 'nullable|numeric|min:0',
            'extended_price' => 'nullable|numeric|min:0',
            'sale_price_regular' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'thumbnail_url' => 'nullable|string|max:500',
            'demo_url' => 'nullable|string|max:500',
            'main_file_url' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'is_featured' => 'nullable|boolean',
        ]);
        $data['is_free'] = $request->boolean('is_free');
        $data['is_featured'] = $request->boolean('is_featured');
        if ($request->filled('features_text')) {
            $data['features'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $request->features_text))));
        }
        if ($request->filled('gallery_text')) {
            $data['gallery_urls'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $request->gallery_text))));
        }
        return $data;
    }
}
