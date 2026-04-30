<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\ProductType;
use App\Filament\Resources\UserProducts\UserProductResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AccessLog;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\OmsIntegrationLog;
use App\Models\User;
use App\Models\UserProduct;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.view-user';

    protected ?Collection $courseProgressRowsCache = null;

    protected ?Collection $latestOrdersCache = null;

    protected ?Collection $latestPaymentsCache = null;

    protected ?Collection $latestUserProductsCache = null;

    protected ?Collection $latestEventsCache = null;

    protected ?Collection $latestOmsLogsCache = null;

    protected ?Collection $latestAccessLogsCache = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'roles.permissions',
            'epiChannel',
            'referrerEpiChannel.user',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Profil'),

            Action::make('edit_roles')
                ->label('Edit Role')
                ->icon('heroicon-o-shield-check')
                ->visible(fn (): bool => UserResource::canManageRoles() && UserResource::canModifyRolesForRecord($this->getRecord()))
                ->fillForm(fn (): array => [
                    'roles' => $this->getRecord()->roles->pluck('name')->all(),
                ])
                ->form([
                    \Filament\Forms\Components\Select::make('roles')
                        ->label('Role')
                        ->multiple()
                        ->options(UserResource::assignableRolesForCurrentActor())
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new RuntimeException('Unauthorized.');
                    }

                    try {
                        UserResource::syncRolesForUser($this->getRecord(), (array) ($data['roles'] ?? []), $actor);
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title('Perubahan role ditolak')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->load('roles.permissions');

                    Notification::make()
                        ->title('Role pengguna diperbarui')
                        ->success()
                        ->send();
                }),

            Action::make('send_reset_password')
                ->label('Kirim Reset Password')
                ->icon('heroicon-o-envelope')
                ->visible(fn (): bool => UserResource::canManageUsers())
                ->requiresConfirmation()
                ->action(function (): void {
                    $actor = auth()->user();

                    if (! $actor instanceof User) {
                        throw new RuntimeException('Unauthorized.');
                    }

                    try {
                        UserResource::sendResetPasswordLink($this->getRecord(), $actor);
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title('Gagal mengirim reset password')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Link reset password dikirim')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummaryStats(): array
    {
        $user = $this->getRecord();
        $courseProgressRows = $this->getCourseProgressRows();
        $latestCourseProgress = $courseProgressRows
            ->sortByDesc(fn (array $row) => $row['last_studied_at']?->timestamp ?? 0)
            ->first();
        $latestOmsLog = $this->getLatestOmsLogs()->first();

        return [
            'total_orders' => $user->orders()->count(),
            'total_paid_payments' => $user->payments()->success()->count(),
            'total_paid_amount' => (float) $user->payments()->success()->sum('amount'),
            'total_active_products' => $user->userProducts()->active()->count(),
            'total_owned_courses' => $user->userProducts()
                ->active()
                ->whereHas('product', fn (Builder $query) => $query->where('product_type', ProductType::Course->value))
                ->distinct('product_id')
                ->count('product_id'),
            'latest_course_progress' => $latestCourseProgress,
            'total_commission_amount' => (float) ($user->epiChannel?->commissions()->sum('commission_amount') ?? 0),
            'total_payout_amount' => (float) ($user->epiChannel?->commissionPayouts()->sum('total_amount') ?? 0),
            'epi_channel_status' => $user->epiChannel?->status?->label() ?? 'Belum',
            'locked_referrer' => $user->referralLockStatusLabel(),
            'last_activity_at' => $user->accessLogs()->max('created_at'),
            'oms_status' => $latestOmsLog?->status?->label() ?? 'Belum ada log',
        ];
    }

    public function getLatestOrders(): Collection
    {
        return $this->latestOrdersCache ??= $this->getRecord()
            ->orders()
            ->with(['referrerEpiChannel'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getLatestPayments(): Collection
    {
        return $this->latestPaymentsCache ??= $this->getRecord()
            ->payments()
            ->with('order')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getLatestUserProducts(): Collection
    {
        return $this->latestUserProductsCache ??= $this->getRecord()
            ->userProducts()
            ->with(['product.course', 'order', 'sourceProduct'])
            ->latest('granted_at')
            ->limit(15)
            ->get();
    }

    public function getCourseProgressRows(): Collection
    {
        if ($this->courseProgressRowsCache instanceof Collection) {
            return $this->courseProgressRowsCache;
        }

        $user = $this->getRecord();

        $courseUserProducts = $user->userProducts()
            ->active()
            ->whereHas('product', fn (Builder $query) => $query->where('product_type', ProductType::Course->value))
            ->with(['product.course'])
            ->get();

        $courseIds = $courseUserProducts
            ->map(fn (UserProduct $userProduct) => $userProduct->product?->course?->id)
            ->filter()
            ->unique()
            ->values();

        if ($courseIds->isEmpty()) {
            return $this->courseProgressRowsCache = collect();
        }

        $lessonTotals = CourseLesson::query()
            ->whereIn('course_id', $courseIds)
            ->accessibleToLearner()
            ->selectRaw('course_id, count(*) as total')
            ->groupBy('course_id')
            ->pluck('total', 'course_id');

        $progressByCourse = LessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->groupBy('course_id');

        return $this->courseProgressRowsCache = $courseUserProducts
            ->unique(fn (UserProduct $userProduct): ?int => $userProduct->product?->course?->id)
            ->map(function (UserProduct $userProduct) use ($lessonTotals, $progressByCourse): ?array {
                $course = $userProduct->product?->course;

                if (! $course) {
                    return null;
                }

                $courseProgress = $progressByCourse->get($course->id, collect());
                $completedLessons = $courseProgress->filter(fn (LessonProgress $progress): bool => $progress->isCompleted())->count();
                $totalLessons = (int) ($lessonTotals[$course->id] ?? 0);
                $lastStudiedAt = $courseProgress
                    ->map(fn (LessonProgress $progress) => $progress->last_viewed_at ?? $progress->completed_at)
                    ->filter()
                    ->sortDesc()
                    ->first();

                return [
                    'course_title' => $course->title,
                    'course_id' => $course->id,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'progress_percent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
                    'last_studied_at' => $lastStudiedAt,
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $row) => $row['last_studied_at']?->timestamp ?? 0)
            ->values();
    }

    public function getLatestEventRegistrations(): Collection
    {
        return $this->latestEventsCache ??= $this->getRecord()
            ->eventRegistrations()
            ->with(['event', 'userProduct.product'])
            ->latest('registered_at')
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function getReferralOverview(): array
    {
        $user = $this->getRecord();
        $channel = $user->epiChannel;

        return [
            'referrer_name' => $user->referrerEpiChannel?->user?->name,
            'referrer_epic_code' => $user->referrerEpiChannel?->epic_code,
            'locked_at' => $user->referral_locked_at,
            'referral_source' => $user->referral_source,
            'epic_code' => $channel?->epic_code,
            'epi_channel_status' => $channel?->status?->label() ?? 'Belum',
            'referral_visits_count' => $channel?->referralVisits()->count() ?? 0,
            'referral_orders_count' => $channel?->referralOrders()->count() ?? 0,
            'commissions_count' => $channel?->commissions()->count() ?? 0,
            'commissions_total' => (float) ($channel?->commissions()->sum('commission_amount') ?? 0),
            'payouts_count' => $channel?->commissionPayouts()->count() ?? 0,
            'payouts_total' => (float) ($channel?->commissionPayouts()->sum('total_amount') ?? 0),
        ];
    }

    public function getLatestOmsLogs(): Collection
    {
        if ($this->latestOmsLogsCache instanceof Collection) {
            return $this->latestOmsLogsCache;
        }

        $user = $this->getRecord();
        $epicCode = $user->epiChannel?->epic_code;

        return $this->latestOmsLogsCache = OmsIntegrationLog::query()
            ->where(function (Builder $query) use ($user, $epicCode): void {
                $query->where('email', $user->email);

                if (filled($epicCode)) {
                    $query->orWhere('epic_code', $epicCode);
                }
            })
            ->latest('processed_at')
            ->latest('id')
            ->limit(10)
            ->get();
    }

    public function getLatestAccessLogs(): Collection
    {
        return $this->latestAccessLogsCache ??= $this->getRecord()
            ->accessLogs()
            ->with(['product', 'userProduct', 'order', 'actor'])
            ->latest('created_at')
            ->limit(20)
            ->get();
    }

    public function getOmsSummary(): array
    {
        /** @var OmsIntegrationLog|null $latestLog */
        $latestLog = $this->getLatestOmsLogs()->first();

        return [
            'status' => $latestLog?->status?->label() ?? 'Belum ada log',
            'epic_code' => $latestLog?->epic_code ?? $this->getRecord()->epiChannel?->epic_code,
            'last_sync_at' => $latestLog?->processed_at,
            'last_error' => $latestLog?->error_message,
            'sync_action_available' => false,
        ];
    }

    public function getUserProductsIndexUrl(): string
    {
        return UserProductResource::getUrl('index', [
            'tableSearch' => $this->getRecord()->email,
        ]);
    }

    public function getActivitySubjectLabel(AccessLog $log): string
    {
        if ($log->order?->order_number) {
            return 'Order '.$log->order->order_number;
        }

        if ($log->product?->title) {
            return $log->product->title;
        }

        if ($log->userProduct?->product?->title) {
            return $log->userProduct->product->title;
        }

        return (string) ($log->metadata['lesson_title'] ?? $log->metadata['attachment_title'] ?? '-');
    }
}
