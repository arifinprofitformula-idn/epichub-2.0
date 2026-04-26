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
    'video_provider',
    'video_id',
    'video_title',
    'video_description',
    'show_video',
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

    protected static function booted(): void
    {
        static::saving(function (self $lesson): void {
            if ($lesson->isDirty('video_url')) {
                if (filled($lesson->video_url)) {
                    $lesson->video_id = static::parseYoutubeVideoId((string) $lesson->video_url);

                    if (blank($lesson->video_provider)) {
                        $lesson->video_provider = 'youtube';
                    }
                } else {
                    $lesson->video_id = null;
                }
            }
        });
    }

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

    /**
     * Returns true when this lesson should display an embedded video player.
     * Covers both the legacy video_embed lesson type and the new show_video toggle.
     */
    public function hasVideo(): bool
    {
        if ($this->lesson_type === CourseLessonType::VideoEmbed && filled($this->video_url)) {
            return true;
        }

        return (bool) $this->show_video && filled($this->video_url);
    }

    public function isYoutubeVideo(): bool
    {
        return ($this->video_provider ?? 'youtube') === 'youtube';
    }

    /**
     * Returns a safe YouTube embed URL built from the stored video_id,
     * falling back to parsing video_url if video_id is not yet populated.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $videoId = filled($this->video_id)
            ? $this->video_id
            : static::parseYoutubeVideoId((string) ($this->video_url ?? ''));

        if (blank($videoId)) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.rawurlencode($videoId).'?rel=0&playsinline=1&controls=1';
    }

    /**
     * Parses a YouTube URL and returns the video ID only — never raw HTML.
     *
     * Supported formats:
     *   https://www.youtube.com/watch?v=VIDEO_ID
     *   https://youtu.be/VIDEO_ID
     *   https://www.youtube.com/embed/VIDEO_ID
     *   https://www.youtube.com/shorts/VIDEO_ID
     *   https://m.youtube.com/watch?v=VIDEO_ID
     */
    public static function parseYoutubeVideoId(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $allowedHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];

        if ($host === 'youtu.be') {
            $segments = $path !== '' ? explode('/', $path) : [];
            $id = $segments[0] ?? null;

            return (is_string($id) && $id !== '') ? $id : null;
        }

        if (in_array($host, $allowedHosts, true)) {
            if (isset($query['v']) && is_string($query['v']) && $query['v'] !== '') {
                return $query['v'];
            }

            $segments = $path === '' ? [] : explode('/', $path);

            if (count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live', 'v'], true)) {
                $id = $segments[1] ?? null;

                return (is_string($id) && $id !== '') ? $id : null;
            }
        }

        return null;
    }

    /**
     * Returns true when the given URL belongs to an allowed YouTube domain.
     * Rejects javascript:, data:, file: and non-YouTube HTTP(S) URLs.
     */
    public static function isAllowedYoutubeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'];

        return in_array($host, $allowed, true);
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
            'show_video' => 'boolean',
            'published_at' => 'datetime',
            'available_from' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
