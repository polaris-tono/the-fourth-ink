<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    // status, published_at, is_featured, views are NOT fillable:
    // they can only change through the editorial workflow / admin actions.
    protected $fillable = ['category_id', 'type', 'title', 'excerpt', 'body'];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'type' => ArticleType::class,
            'is_featured' => 'boolean',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (empty($article->slug)) {
                $base = Str::slug($article->title) ?: 'article';
                do {
                    $slug = $base . '-' . Str::lower(Str::random(6));
                } while (static::withTrashed()->where('slug', $slug)->exists());
                $article->slug = $slug;
            }
            $article->status ??= ArticleStatus::Draft;
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ArticleReview::class)->latest();
    }

    /** Only these are ever visible to the public. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published->value)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
        /** Markdown → safe HTML. Raw HTML is stripped, unsafe links blocked (XSS protection). */
    public function bodyHtml(): string
    {
        return Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}