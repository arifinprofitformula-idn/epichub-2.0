<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'product_id',
    'title',
    'slug',
    'short_description',
    'description',
    'thumbnail',
    'status',
    'difficulty',
    'estimated_duration_minutes',
    'is_featured',
    'sort_order',
    'published_at',
    'metadata',
])]
class Course extends Model
{
    use SoftDeletes;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query
            ->where('status', CourseStatus::Published)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isPublished(): bool
    {
        if ($this->status !== CourseStatus::Published) {
            return false;
        }

        return $this->published_at === null || $this->published_at->isPast();
    }

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'estimated_duration_minutes' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

