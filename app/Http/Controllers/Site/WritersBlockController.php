<?php

namespace App\Http\Controllers\Site;

use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WritersBlockController extends Controller
{
    public function index(Request $request): View
    {
        $allowed = array_map(fn ($t) => $t->value, ArticleType::contributorTypes());

        $data = $request->validate([
            'type' => ['nullable', Rule::in($allowed)],
        ]);
        $type = $data['type'] ?? null;

        $articles = Article::published()
            ->with(['category', 'author'])
            ->whereIn('type', $type ? [$type] : $allowed)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('writers-block', [
            'articles' => $articles,
            'type' => $type,
            'types' => ArticleType::contributorTypes(),
        ]);
    }
}