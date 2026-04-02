<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'content', 'category_id', 'published_at'])]
class News extends Model
{
    use HasFactory, Searchable;

    protected array $searchable = ['title', 'content'];

    protected array $searchableRelations = [
        'category' => ['name', 'slug'],
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (News $news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
        });

        static::updating(function (News $news) {
            if ($news->isDirty('title') && ! $news->isDirty('slug')) {
                $news->slug = Str::slug($news->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->whereDate('published_at', '<=', today());
    }

    public function scopeInCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (! $categorySlug) {
            return $query;
        }

        return $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->content), 150);
    }
}
