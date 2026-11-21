<?php

namespace App\Http\Controllers\Contributor;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contributor\ArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Services\CoverImageService;
use App\Services\EditorialWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const FIELDS = ['category_id', 'type', 'title', 'excerpt', 'body'];

    public function __construct(
        private EditorialWorkflowService $workflow,
        private CoverImageService $covers,
    ) {
    }

    public function index(Request $request): View
    {
        // Always scoped to the logged-in user's own articles.
        $articles = $request->user()->articles()
            ->with(['category', 'reviews'])
            ->latest('updated_at')
            ->paginate(10);

        return view('contributor.index', compact('articles'));
    }

    public function create(): View
    {
        $this->authorize('create', Article::class);

        return view('contributor.form', $this->formData(new Article()));
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $this->authorize('create', Article::class);

        // Created through the relationship, so user_id is always the logged-in user.
        $article = $request->user()->articles()->create($request->safe()->only(self::FIELDS));
        $this->covers->apply($request, $article);

        return $this->finish($request, $article, 'Draft saved.');
    }

    public function show(Article $article): View
    {
        $this->authorize('view', $article);

        return view('contributor.show', [
            'article' => $article->load(['category', 'reviews.actor']),
        ]);
    }

    public function edit(Article $article): View
    {
        $this->authorize('update', $article);

        return view('contributor.form', $this->formData($article->load('reviews')));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $article->update($request->safe()->only(self::FIELDS));
        $this->covers->apply($request, $article);

        return $this->finish($request, $article, 'Changes saved.');
    }

    public function submit(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('submit', $article);

        $this->workflow->transition($article, ArticleStatus::PendingReview, $request->user());

        return redirect()->route('contributor.dashboard')
            ->with('status', 'Submitted for editorial review.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        $article->delete(); // soft delete

        return redirect()->route('contributor.dashboard')->with('status', 'Draft deleted.');
    }

    /** After saving: either stay a draft, or go on to submit for review. */
    private function finish(Request $request, Article $article, string $savedMessage): RedirectResponse
    {
        if ($request->input('action') === 'submit') {
            $this->authorize('submit', $article);
            $this->workflow->transition($article, ArticleStatus::PendingReview, $request->user());

            return redirect()->route('contributor.articles.show', $article)
                ->with('status', 'Submitted for editorial review.');
        }

        return redirect()->route('contributor.articles.show', $article)->with('status', $savedMessage);
    }

    private function formData(Article $article): array
    {
        return [
            'article' => $article,
            'categories' => Category::orderBy('sort_order')->get(),
            'types' => ArticleType::contributorTypes(),
        ];
    }
}