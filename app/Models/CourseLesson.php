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
    'status',
    'is_required',
    'is_preview',
    'is_active',
    'published_at',
    'available_from',
    'metadata',
])]
class CourseLesson extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

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

    public function attachments(): HasMany
    {
        return $this->hasMany(CourseLessonAttachment::class, 'course_lesson_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeAccessibleToLearner(Builder $query): void
    {
        $query
            ->where('is_active', true)
            ->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    public function isAvailable(): bool
    {
        return $this->isAvailableNow();
    }

    public function isPublished(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (($this->status ?? self::STATUS_PUBLISHED) !== self::STATUS_PUBLISHED) {
            return false;
        }

        return $this->published_at === null || $this->published_at->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->available_from !== null && $this->available_from->isFuture();
    }

    public function isAvailableNow(): bool
    {
        return $this->isPublished() && ! $this->isScheduled();
    }

    public function isRequired(): bool
    {
        return (bool) ($this->is_required ?? true);
    }

    protected function casts(): array
    {
        return [
            'lesson_type' => CourseLessonType::class,
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
            'status' => 'string',
            'is_required' => 'boolean',
            'is_preview' => 'boolean',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'available_from' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

