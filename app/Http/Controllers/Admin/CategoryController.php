<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('name')->paginate(40);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.form', ['category' => new Category, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        if (! empty($data['attribute_schema_json'])) {
            $data['attribute_schema'] = json_decode($data['attribute_schema_json'], true);
        }
        unset($data['attribute_schema_json']);
        $data = $this->stripMissingSeoColumns($data);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        if (array_key_exists('attribute_schema_json', $data)) {
            $data['attribute_schema'] = $data['attribute_schema_json']
                ? json_decode($data['attribute_schema_json'], true)
                : null;
            unset($data['attribute_schema_json']);
        }
        $data = $this->stripMissingSeoColumns($data);
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate(array_merge([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'attribute_schema_json' => 'nullable|string',
        ], Seo::rules()));
    }

    protected function stripMissingSeoColumns(array $data): array
    {
        foreach (array_keys(Seo::rules()) as $seoKey) {
            if (! Schema::hasColumn('categories', $seoKey)) {
                unset($data[$seoKey]);
            }
        }

        return $data;
    }
}
