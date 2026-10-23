<?php

namespace App\Http\Controllers\Site;

use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\ExternalArticle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category): View
    {
        $data = $request->validate([
            'type' => ['nullable', Rule::enum(ArticleType::class)],
        ]);
        $type = $data['type'] ?? null;

        $articles = Article::published()
            ->with(['category', 'author'])
            ->where('category_id', $category->id)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $external = ExternalArticle::with('category')
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('category', [
            'category' => $category,
            'articles' => $articles,
            'external' => $external,
            'type' => $type,
            'types' => ArticleType::cases(),
        ]);
    }
}