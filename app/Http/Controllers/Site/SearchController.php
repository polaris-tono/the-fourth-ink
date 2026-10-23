<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\ExternalArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'exists:categories,slug'],
        ]);

        $q = trim($data['q'] ?? '');
        $categorySlug = $data['category'] ?? null;
        $articles = null;
        $external = collect();

        if ($q !== '') {
            // Escape LIKE wildcards so a user typing % or _ searches for the literal character.
            $like = '%' . addcslashes($q, '%_\\') . '%';

            $articles = Article::published()
                ->with(['category', 'author'])
                ->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('body', 'like', $like);
                })
                ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
                ->latest('published_at')
                ->paginate(10)
                ->withQueryString();

            $external = ExternalArticle::with('category')
                ->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)->orWhere('description', 'like', $like);
                })
                ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
                ->latest('published_at')
                ->take(10)
                ->get();
        }

        return view('search', [
            'q' => $q,
            'categorySlug' => $categorySlug,
            'categories' => Category::orderBy('sort_order')->get(),
            'articles' => $articles,
            'external' => $external,
        ]);
    }
}