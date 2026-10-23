<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReview extends Model
{
    protected $fillable = ['article_id', 'actor_id', 'from_status', 'to_status', 'comment'];

    protected function casts(): array
    {
        return [
            'from_status' => ArticleStatus::class,
            'to_status' => ArticleStatus::class,
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}