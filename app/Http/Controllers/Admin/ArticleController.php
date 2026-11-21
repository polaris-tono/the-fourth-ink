<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\CoverImageService;
use App\Services\EditorialWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;c
use Illuminate\Validation\Rule;
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
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ArticleStatus::class)],
            'type' => ['nullable', Rule::enum(ArticleType::class)],
            'trash' => ['nullable', 'boolean'],
        ]);

        $trash = $request->boolean('trash');
        $adminId = $request->user()->id;

        $articles = Article::query()
            ->with(['author', 'category'])
            ->when($trash, fn($q) => $q->onlyTrashed())
            // Other writers' unsubmitted drafts stay private, even from admins.
            ->where(function ($q) use ($adminId) {
                $q->where('status', '!=', ArticleStatus::Draft->value)
                    ->orWhere('user_id', $adminId);
            })
            ->when($data['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($data['type'] ?? null, fn($q, $t) => $q->where('type', $t))
            ->when(filled($data['q'] ?? null), function ($q) use ($data) {
                $q->where('title', 'like', '%' . addcslashes($data['q'], '%_\\') . '%');
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', [
            'articles' => $articles,
            'statuses' => ArticleStatus::cases(),
            'types' => ArticleType::cases(),
            'trash' => $trash,
            'filters' => [
                'q' => $data['q'] ?? '',
                'status' => $data['status'] ?? '',
                'type' => $data['type'] ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Article::class);

        return view('admin.articles.form', $this->formData(new Article()));
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $this->authorize('create', Article::class);

        // Created through the relationship, so the author is always the logged-in admin.
        $article = $request->user()->articles()->create($request->safe()->only(self::FIELDS));
        $this->covers->apply($request, $article);

        if ($request->input('action') === 'publish_now') {
            $this->workflow->publishByAdmin($article, $request->user());

            return redirect()->route('admin.articles.index')->with('status', 'Article published.');
        }

        return redirect()->route('admin.articles.edit', $article)->with('status', 'Draft saved.');
    }

    public function edit(Request $request, Article $article): View
    {
        $this->guard($article, $request->user());

        return view('admin.articles.form', $this->formData($article));
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $this->guard($article, $request->user());

        $article->update($request->safe()->only(self::FIELDS));
        $this->covers->apply($request, $article);

        if ($request->input('action') === 'publish_now') {
            // Only an admin's own draft can be fast-tracked.
            abort_unless(
                $article->status === ArticleStatus::Draft && $article->user_id === $request->user()->id,
                403
            );
            $this->workflow->publishByAdmin($article, $request->user());

            return redirect()->route('admin.articles.index')->with('status', 'Article published.');
        }

        return redirect()->route('admin.articles.edit', $article)->with('status', 'Changes saved.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $this->guard($article, $request->user());

        $article->delete(); // soft delete: it can be restored from the Trash view

        return redirect()->route('admin.articles.index')->with('status', 'Article moved to the trash.');
    }

    public function restore(Request $request, Article $article): RedirectResponse
    {
        $this->guard($article, $request->user());

        $article->restore();

        return redirect()->route('admin.articles.index', ['trash' => 1])->with('status', 'Article restored.');
    }

    public function feature(Request $request, Article $article): RedirectResponse
    {
        $this->guard($article, $request->user());

        // Only live articles can be featured.
        abort_unless($article->status === ArticleStatus::Published, 422);

        // is_featured is not mass-assignable, so it is set explicitly here.
        $article->forceFill(['is_featured' => !$article->is_featured])->save();

        return back()->with('status', $article->is_featured ? 'Article is now featured.' : 'Article removed from featured.');
    }

    private function guard(Article $article, User $user): void
    {
        $this->authorize('manage', $article);

        // Another writer's unsubmitted draft is private, even from admins.
        abort_if($article->status === ArticleStatus::Draft && $article->user_id !== $user->id, 404);
    }

    private function formData(Article $article): array
    {
        return [
            'article' => $article,
            'categories' => Category::orderBy('sort_order')->get(),
            'types' => ArticleType::cases(),
        ];
    }
}