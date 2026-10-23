<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Enums\Role;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    private function owns(User $user, Article $article): bool
    {
        return $user->id === $article->user_id;
    }

    public function view(User $user, Article $article): bool
    {
        return $this->owns($user, $article) || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Contributor, Role::Admin);
    }

    /** Authors can edit only while the article is with them (draft / revision requested). */
    public function update(User $user, Article $article): bool
    {
        return $this->owns($user, $article) && $article->status->isEditableByAuthor();
    }

    public function submit(User $user, Article $article): bool
    {
        return $this->owns($user, $article)
            && $article->status->canTransitionTo(ArticleStatus::PendingReview);
    }

    /** Only unsubmitted drafts can be deleted by their author. */
    public function delete(User $user, Article $article): bool
    {
        return $this->owns($user, $article) && $article->status === ArticleStatus::Draft;
    }
        /** Admins can manage any article (edit, delete, feature). */
    public function manage(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }
}