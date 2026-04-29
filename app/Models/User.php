<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Actions\Support\NormalizeWhatsappNumberAction;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'legacy_epic_id', 'legacy_source', 'legacy_user_id', 'legacy_import_batch_id', 'legacy_imported_at', 'email', 'email_verified_at', 'password', 'must_reset_password', 'profile_photo_path', 'whatsapp_number', 'referrer_epi_channel_id', 'referral_locked_at', 'referral_source'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_reset_password' => 'boolean',
            'referral_locked_at' => 'datetime',
            'legacy_imported_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (blank($this->profile_photo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function normalizedWhatsappNumber(?string $value = null): ?string
    {
        return app(NormalizeWhatsappNumberAction::class)->execute($value ?? $this->whatsapp_number);
    }

    public function getWhatsappNumberForUrlAttribute(): ?string
    {
        return $this->normalizedWhatsappNumber();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * @return HasMany<UserProduct, $this>
     */
    public function userProducts(): HasMany
    {
        return $this->hasMany(UserProduct::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasManyThrough<Payment, Order, $this>
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Order::class, 'user_id', 'order_id');
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * @return HasMany<AccessLog, $this>
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    /**
     * @return HasOne<EpiChannel, $this>
     */
    public function epiChannel(): HasOne
    {
        return $this->hasOne(EpiChannel::class);
    }

    /**
     * @return BelongsTo<EpiChannel, $this>
     */
    public function referrerEpiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class, 'referrer_epi_channel_id');
    }

    /**
     * @return HasMany<ReferralOrder, $this>
     */
    public function referralOrders(): HasMany
    {
        return $this->hasMany(ReferralOrder::class, 'buyer_user_id');
    }

    /**
     * @return HasMany<Commission, $this>
     */
    public function commissionsAsBuyer(): HasMany
    {
        return $this->hasMany(Commission::class, 'buyer_user_id');
    }

    /**
     * @return HasMany<OmsIntegrationLog, $this>
     */
    public function omsIntegrationLogsByEmail(): HasMany
    {
        return $this->hasMany(OmsIntegrationLog::class, 'email', 'email');
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * @return HasMany<LegacyV1User, $this>
     */
    public function legacyV1MatchedUsers(): HasMany
    {
        return $this->hasMany(LegacyV1User::class, 'matched_user_id');
    }

    /**
     * @return HasMany<LegacyV1User, $this>
     */
    public function legacyV1ImportedUsers(): HasMany
    {
        return $this->hasMany(LegacyV1User::class, 'imported_user_id');
    }

    /**
     * @return HasMany<LegacyV1UserMapping, $this>
     */
    public function legacyV1UserMappings(): HasMany
    {
        return $this->hasMany(LegacyV1UserMapping::class);
    }

    /**
     * @return HasMany<LegacyV1Order, $this>
     */
    public function legacyV1Orders(): HasMany
    {
        return $this->hasMany(LegacyV1Order::class);
    }

    /**
     * @return HasMany<LegacyV1Payment, $this>
     */
    public function legacyV1Payments(): HasMany
    {
        return $this->hasMany(LegacyV1Payment::class);
    }

    /**
     * @return HasMany<LegacyV1Commission, $this>
     */
    public function legacyV1Commissions(): HasMany
    {
        return $this->hasMany(LegacyV1Commission::class);
    }

    /**
     * @return HasMany<LegacyV1Payout, $this>
     */
    public function legacyV1Payouts(): HasMany
    {
        return $this->hasMany(LegacyV1Payout::class);
    }

    public function hasLockedReferrer(): bool
    {
        return $this->referrer_epi_channel_id !== null;
    }

    public function referralLockStatusLabel(): string
    {
        if (! $this->hasLockedReferrer()) {
            return 'Perlu dicek';
        }

        if ($this->referrerEpiChannel?->isHouseChannel()) {
            return 'House Channel';
        }

        return 'Locked';
    }
}
