<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalArticle extends Model
{
    protected $fillable = [
        'category_id', 'source_name', 'title', 'description',
        'url', 'url_hash', 'image_url', 'published_at', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}