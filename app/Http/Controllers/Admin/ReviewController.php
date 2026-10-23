<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleReview;
use App\Services\EditorialWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private EditorialWorkflowService $workflow)
    {
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'statuses' => $this->reviewable(),
            'counts' => $this->counts(),
            'waiting' => Article::with(['author', 'category'])
                ->where('status', ArticleStatus::PendingReview->value)
                ->oldest('submitted_at')
                ->take(5)
                ->get(),
            'activity' => ArticleReview::with(['article', 'actor'])->latest()->take(8)->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $statuses = $this->reviewable();
        $allowed = [...array_map(fn ($s) => $s->value, $statuses), 'all'];

        $data = $request->validate(['status' => ['nullable', Rule::in($allowed)]]);
        $status = $data['status'] ?? ArticleStatus::PendingReview->value;

        $counts = $this->counts();
        $tabs = [];
        foreach ($statuses as $s) {
            $tabs[$s->value] = ['label' => $s->label(), 'count' => $counts[$s->value] ?? 0];
        }
        $tabs['all'] = ['label' => 'All', 'count' => collect($tabs)->sum('count')];

        $articles = Article::with(['author', 'category'])
            ->where('status', '!=', ArticleStatus::Draft->value)   // unsubmitted drafts stay private
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(
                $status === ArticleStatus::PendingReview->value,
                fn ($q) => $q->oldest('submitted_at'),          // oldest waiting first
                fn ($q) => $q->latest('updated_at')
            )
            ->paginate(15)
            ->withQueryString();

        return view('admin.review.index', compact('articles', 'tabs', 'status'));
    }

    public function show(Article $article): View
    {
        // An author's unsubmitted draft is not the editors' business.
        abort_if($article->status === ArticleStatus::Draft, 404);

        return view('admin.review.show', [
            'article' => $article->load(['author', 'category', 'reviews.actor']),
        ]);
    }

    public function transition(Request $request, Article $article): RedirectResponse
    {
        abort_if($article->status === ArticleStatus::Draft, 404);

        $data = $request->validate([
            'to' => ['required', Rule::in(['approved', 'revision_requested', 'rejected', 'published'])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $to = ArticleStatus::from($data['to']);

        // The service checks the allowed transitions, the actor, and the required comment.
        $this->workflow->transition($article, $to, $request->user(), $data['comment'] ?? null);

        return redirect()->route('admin.review.show', $article)
            ->with('status', "Article moved to “{$to->label()}”.");
    }

    /** Every status except Draft. */
    private function reviewable(): array
    {
        return array_values(array_filter(
            ArticleStatus::cases(),
            fn ($s) => $s !== ArticleStatus::Draft
        ));
    }

    private function counts(): array
    {
        return Article::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}