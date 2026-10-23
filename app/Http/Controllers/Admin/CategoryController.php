<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('articles')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? ((int) Category::max('sort_order') + 1),
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        // The slug is not changed, so existing links keep working.
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Trashed articles still point at the category, so they count too.
        $inUse = $category->articles()->withTrashed()->exists() || $category->externalArticles()->exists();

        if ($inUse) {
            return back()->withErrors([
                'category' => "“{$category->name}” still has articles, so it can't be deleted. Move or remove them first.",
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}