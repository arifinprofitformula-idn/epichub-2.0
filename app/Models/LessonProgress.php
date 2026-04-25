<?php

namespace App\Models;

use App\Enums\LessonProgressStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'course_id',
    'course_lesson_id',
    'user_product_id',
    'status',
    'completed_at',
    'last_viewed_at',
    'metadata',
])]
class LessonProgress extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', LessonProgressStatus::Completed);
    }

    public function isCompleted(): bool
    {
        return $this->status === LessonProgressStatus::Completed;
    }

    protected function casts(): array
    {
        return [
            'status' => LessonProgressStatus::class,
            'completed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

