<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        // Only published articles are ever reachable. Anything else is a 404,
        // even if someone guesses the URL.
        $article = Article::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Count one view per visitor session, so refreshing doesn't inflate "trending".
        $viewed = $request->session()->get('viewed_articles', []);
        if (! in_array($article->id, $viewed, true)) {
            $article->increment('views');
            $request->session()->put('viewed_articles', [...$viewed, $article->id]);
        }

        $related = Article::published()
            ->with(['category', 'author'])
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('articles.show', compact('article', 'related'));
    }
}