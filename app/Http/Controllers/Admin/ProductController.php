<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->orderByDesc('created_at')->paginate(30);

        return view('admin.products.index', compact('items'));
    }

    public function create()
    {
        return view('admin.products.form', $this->formData(new Item));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: Str::slug($data['title']));
        $data = $this->applyFree($data);
        $data['author_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'approved';
        Item::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Item $product)
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function update(Request $request, Item $product)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: Str::slug($data['title']), $product->id);
        $data = $this->applyFree($data);
        $product->update($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product saved.');
    }

    public function destroy(Item $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    protected function formData(Item $item): array
    {
        $categories = Category::orderBy('name')->get();
        $attrSchema = [];
        if ($item->category_id) {
            $cat = $categories->firstWhere('id', $item->category_id);
            $attrSchema = $cat?->attribute_schema ?? [];
        }
        $tagPresets = SiteSetting::getValue('tags', ['React', 'Laravel', 'WordPress', 'Vue', 'PHP', 'HTML', 'SaaS', 'Dashboard']);

        return compact('item', 'categories', 'attrSchema', 'tagPresets');
    }

    protected function applyFree(array $data): array
    {
        if (! empty($data['is_free'])) {
            $data['regular_price'] = 0;
            $data['extended_price'] = 0;
            $data['sale_price_regular'] = null;
            $data['sale_price_extended'] = null;
            $data['is_free'] = true;
        } else {
            $data['is_free'] = false;
        }

        return $data;
    }

    protected function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'item';
        $try = $base;
        $i = 1;
        while (Item::where('slug', $try)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $try = $base.'-'.$i++;
        }

        return $try;
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:160',
            'description' => 'nullable|string',
            'regular_price' => 'nullable|numeric|min:0',
            'extended_price' => 'nullable|numeric|min:0',
            'sale_price_regular' => 'nullable|numeric|min:0',
            'sale_price_extended' => 'nullable|numeric|min:0',
            'is_free' => 'nullable|boolean',
            'thumbnail_url' => 'nullable|string|max:1000',
            'demo_url' => 'nullable|string|max:1000',
            'main_file_url' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:pending,approved,rejected',
            'features_text' => 'nullable|string',
            'gallery_text' => 'nullable|string',
            'tags_text' => 'nullable|string',
            'attributes_json' => 'nullable|string',
        ]);

        $data['is_free'] = $request->boolean('is_free');
        $data['is_featured'] = $request->boolean('is_featured');

        $data['features'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('features_text', '')))));
        $data['gallery_urls'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('gallery_text', '')))));
        $data['tags'] = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*|\r\n|\r|\n/', (string) $request->input('tags_text', '')))));

        $attrs = [];
        if ($request->filled('attributes_json')) {
            $decoded = json_decode($request->input('attributes_json'), true);
            if (is_array($decoded)) {
                $attrs = $decoded;
            }
        }
        // Also accept attr[key][] form fields
        foreach ($request->input('attr', []) as $key => $vals) {
            $attrs[$key] = is_array($vals) ? array_values(array_filter($vals)) : [$vals];
        }
        $data['attributes'] = $attrs;

        unset($data['features_text'], $data['gallery_text'], $data['tags_text'], $data['attributes_json']);

        return $data;
    }
}
