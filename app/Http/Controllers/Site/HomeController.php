<?php

namespace App\Http\Controllers\Site;

use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ExternalArticle;
use App\Services\NewsApiService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(NewsApiService $news): View
    {
        // Pulls fresh headlines when the stored ones are older than the refresh interval.
        // It never throws: if the news service is down, the last saved headlines are shown.
        $news->refreshIfStale();

        $published = fn () => Article::published()->with(['category', 'author']);

        $featured = $published()->featured()->latest('published_at')->take(3)->get();

        $latest = $published()
            ->whereNotIn('id', $featured->pluck('id'))
            ->latest('published_at')
            ->take(8)
            ->get();

        // If nothing is marked featured yet, promote the newest stories.
        if ($featured->isEmpty()) {
            $featured = $latest->splice(0, 3);
        }

        $trending = $published()->orderByDesc('views')->take(5)->get();

        $writers = $published()
            ->whereIn('type', array_map(fn ($t) => $t->value, ArticleType::contributorTypes()))
            ->latest('published_at')
            ->take(3)
            ->get();

        $external = ExternalArticle::with('category')->latest('published_at')->take(6)->get();

        return view('home', compact('featured', 'latest', 'trending', 'writers', 'external'));
    }
}