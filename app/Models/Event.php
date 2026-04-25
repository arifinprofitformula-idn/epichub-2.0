<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventRegistrationStatus;
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
    'description',
    'banner',
    'speaker_name',
    'speaker_title',
    'speaker_bio',
    'starts_at',
    'ends_at',
    'timezone',
    'quota',
    'zoom_url',
    'zoom_meeting_id',
    'zoom_passcode',
    'replay_url',
    'status',
    'is_featured',
    'sort_order',
    'published_at',
    'metadata',
])]
class Event extends Model
{
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class)->whereIn('status', [
            EventRegistrationStatus::Registered,
            EventRegistrationStatus::Attended,
        ]);
    }

    public function scopePublished(Builder $query): void
    {
        $query
            ->whereIn('status', [EventStatus::Published, EventStatus::Ongoing])
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->whereNotNull('starts_at')->where('starts_at', '>', now());
    }

    public function isPublished(): bool
    {
        if (! in_array($this->status, [EventStatus::Published, EventStatus::Ongoing], true)) {
            return false;
        }

        return $this->published_at === null || $this->published_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === EventStatus::Completed;
    }

    public function isFull(): bool
    {
        if ($this->quota === null) {
            return false;
        }

        $count = $this->active_registrations_count ?? null;

        if (is_int($count)) {
            return $count >= $this->quota;
        }

        return $this->activeRegistrations()->count() >= $this->quota;
    }

    public function remainingSeats(): ?int
    {
        if ($this->quota === null) {
            return null;
        }

        $count = $this->active_registrations_count ?? null;

        $registered = is_int($count) ? $count : $this->activeRegistrations()->count();

        return max(0, $this->quota - $registered);
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'quota' => 'integer',
            'status' => EventStatus::class,
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

