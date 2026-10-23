<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::RevisionRequested => 'Revision Requested',
            self::Approved => 'Approved',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
        };
    }

    /** The single source of truth for which status changes are legal. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingReview],
            self::PendingReview => [self::Approved, self::RevisionRequested, self::Rejected],
            self::RevisionRequested => [self::PendingReview],
            self::Approved => [self::Published],
            self::Published => [self::Approved],
            self::Rejected => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /** Admin must explain these two decisions. */
    public function requiresComment(): bool
    {
        return in_array($this, [self::RevisionRequested, self::Rejected], true);
    }

    /** Authors may only edit while the article is with them. */
    public function isEditableByAuthor(): bool
    {
        return in_array($this, [self::Draft, self::RevisionRequested], true);
    }
}