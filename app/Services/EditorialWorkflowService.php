<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleReview;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditorialWorkflowService
{
    /**
     * Move an article to a new status. Every status change in the whole
     * application goes through this one method.
     */
    public function transition(Article $article, ArticleStatus $to, User $actor, ?string $comment = null): Article
    {
        return DB::transaction(function () use ($article, $to, $actor, $comment) {
            // Lock the row so two people can't change the same article at the same moment.
            $locked = Article::query()->whereKey($article->getKey())->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            // 1. Is this move allowed by the status rules?
            if (! $from->canTransitionTo($to)) {
                throw ValidationException::withMessages([
                    'status' => "An article that is “{$from->label()}” cannot be moved to “{$to->label()}”.",
                ]);
            }

            // 2. Is this person allowed to make this move?
            $this->assertActorAllowed($locked, $to, $actor);

            // 3. Rejections and revision requests must be explained.
            $comment = filled($comment) ? trim($comment) : null;
            if ($to->requiresComment() && $comment === null) {
                throw ValidationException::withMessages([
                    'comment' => 'Please explain this decision to the writer.',
                ]);
            }

            // 4. Apply the change.
            $locked->status = $to;
            if ($to === ArticleStatus::PendingReview) {
                $locked->submitted_at = now();
            }
            if ($to === ArticleStatus::Published) {
                $locked->published_at = now();
            }
            $locked->save();

            // 5. Record it (feedback + audit trail).
            ArticleReview::create([
                'article_id' => $locked->id,
                'actor_id' => $actor->id,
                'from_status' => $from,
                'to_status' => $to,
                'comment' => $comment,
            ]);

            return $locked;
        });
    }
        /**
     * "Publish now" for an admin's own draft. It does not skip the rules: it walks
     * Draft → Pending → Approved → Published, so the history shows every step.
     */
    public function publishByAdmin(Article $article, User $admin): Article
    {
        return DB::transaction(function () use ($article, $admin) {
            foreach ([ArticleStatus::PendingReview, ArticleStatus::Approved, ArticleStatus::Published] as $step) {
                $article = $this->transition($article, $step, $admin);
            }

            return $article;
        });
    }

    /** Authors may only submit their own work; every other move is for admins. */
    private function assertActorAllowed(Article $article, ArticleStatus $to, User $actor): void
    {
        $allowed = $to === ArticleStatus::PendingReview
            ? $actor->id === $article->user_id
            : $actor->isAdmin();

        if (! $allowed) {
            throw new AuthorizationException('You are not allowed to make this change.');
        }
    }
}