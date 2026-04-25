<?php

namespace App\Models;

use App\Enums\CourseLessonType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'course_id',
    'course_section_id',
    'title',
    'slug',
    'lesson_type',
    'short_description',
    'content',
    'video_url',
    'attachment_path',
    'external_url',
    'duration_minutes',
    'sort_order',
    'is_preview',
    'is_active',
    'published_at',
    'metadata',
])]
class CourseLesson extends Model
{
    use SoftDeletes;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class, 'course_lesson_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->published_at === null || $this->published_at->isPast();
    }

    protected function casts(): array
    {
        return [
            'lesson_type' => CourseLessonType::class,
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
            'is_preview' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

