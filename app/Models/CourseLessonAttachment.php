<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'course_lesson_id',
    'title',
    'description',
    'source_type',
    'file_path',
    'external_url',
    'button_label',
    'open_in_new_tab',
    'original_name',
    'mime_type',
    'size',
    'disk',
    'is_downloadable',
    'is_active',
    'sort_order',
])]
class CourseLessonAttachment extends Model
{
    public const SOURCE_UPLOAD = 'upload';

    public const SOURCE_EXTERNAL_URL = 'external_url';

    protected ?string $previousFilePath = null;

    protected ?string $previousDisk = null;

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeDownloadable(Builder $query): void
    {
        $query->where('is_downloadable', true);
    }

    public function isUpload(): bool
    {
        return ($this->source_type ?: self::SOURCE_UPLOAD) === self::SOURCE_UPLOAD;
    }

    public function isExternalUrl(): bool
    {
        return $this->source_type === self::SOURCE_EXTERNAL_URL;
    }

    public function getDisplayButtonLabelAttribute(): string
    {
        $customLabel = trim((string) ($this->button_label ?? ''));

        if ($customLabel !== '') {
            return $customLabel;
        }

        return $this->isExternalUrl() ? 'Buka Link' : 'Download File';
    }

    protected static function booted(): void
    {
        static::updating(function (self $attachment): void {
            if ($attachment->isDirty('file_path') || $attachment->isDirty('disk')) {
                $attachment->previousFilePath = $attachment->getOriginal('file_path');
                $attachment->previousDisk = $attachment->getOriginal('disk') ?: 'local';
            }
        });

        static::saving(function (self $attachment): void {
            $attachment->source_type = $attachment->source_type ?: self::SOURCE_UPLOAD;

            if ($attachment->isExternalUrl()) {
                $attachment->disk = $attachment->disk ?: 'local';
                $attachment->mime_type = null;
                $attachment->size = null;
                $attachment->original_name = null;

                return;
            }

            $disk = $attachment->disk ?: 'local';
            $path = $attachment->file_path;

            if (! $path || ! Storage::disk($disk)->exists($path)) {
                return;
            }

            $attachment->mime_type = Storage::disk($disk)->mimeType($path) ?: $attachment->mime_type;
            $attachment->size = Storage::disk($disk)->size($path) ?: $attachment->size;
            $attachment->original_name = $attachment->original_name ?: basename($path);
        });

        static::updated(function (self $attachment): void {
            if ($attachment->previousFilePath && Storage::disk($attachment->previousDisk ?: 'local')->exists($attachment->previousFilePath)) {
                Storage::disk($attachment->previousDisk ?: 'local')->delete($attachment->previousFilePath);
            }
        });

        static::deleted(function (self $attachment): void {
            if (! $attachment->isUpload()) {
                return;
            }

            $disk = $attachment->disk ?: 'local';
            $path = $attachment->file_path;

            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'source_type' => 'string',
            'size' => 'integer',
            'open_in_new_tab' => 'boolean',
            'is_downloadable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
