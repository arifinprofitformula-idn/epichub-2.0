<?php

namespace App\Filament\Pages;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Services\Notifications\NotificationShortcodeRegistry;
use App\Services\Notifications\NotificationTemplateService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class NotificationSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $navigationLabel = 'Pengaturan Notifikasi';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Settings;

    protected static ?int $navigationSort = 83;

    protected string $view = 'filament.pages.notification-settings';

    // ── Tab event yang ditampilkan di UI ──────────────────────────────────────

    public const TABS = [
        ['key' => 'user_registered',            'label' => 'Pendaftaran'],
        ['key' => 'password_reset_requested',    'label' => 'Reset Password'],
        ['key' => 'order_created',               'label' => 'Order Baru'],
        ['key' => 'payment_submitted',           'label' => 'Bukti Pembayaran'],
        ['key' => 'payment_approved',            'label' => 'Order Lunas'],
        ['key' => 'payment_rejected',            'label' => 'Pembayaran Ditolak'],
        ['key' => 'order_expired',               'label' => 'Order Expired'],
        ['key' => 'access_granted',              'label' => 'Akses Produk'],
        ['key' => 'event_registration_confirmed','label' => 'Registrasi Event'],
        ['key' => 'course_enrolled',             'label' => 'Course Enrollment'],
        ['key' => 'affiliate_commission_created','label' => 'Komisi Affiliate'],
        ['key' => 'commission_payout_paid',      'label' => 'Payout'],
    ];

    // ── State ────────────────────────────────────────────────────────────────

    /** Active event tab. */
    public string $activeEvent = 'user_registered';

    /**
     * Template state keyed by [event_key][target_key][field].
     * @var array<string, array<string, array<string, mixed>>>
     */
    public array $templates = [];

    /**
     * Validation results per [event_key][target_key].
     * @var array<string, array<string, array<string, string[]>>>
     */
    public array $validationResults = [];

    // ── Bootstrap ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->loadAllTemplates();
        $this->activeEvent = self::TABS[0]['key'];
    }

    private function loadAllTemplates(): void
    {
        $service = app(NotificationTemplateService::class);

        foreach (self::TABS as $tab) {
            $eventKey  = $tab['key'];
            $templates = $service->getTemplatesByEvent($eventKey);

            foreach ($templates as $template) {
                $this->templates[$eventKey][$template->target_key] = [
                    'email_enabled'    => $template->email_enabled,
                    'whatsapp_enabled' => $template->whatsapp_enabled,
                    'email_subject'    => $template->email_subject ?? '',
                    'email_body'       => $template->email_body ?? '',
                    'whatsapp_body'    => $template->whatsapp_body ?? '',
                    'target_label'     => $template->target_label,
                ];
            }
        }
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveAll')
                ->label('Simpan Semua Pengaturan')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('primary')
                ->action('saveAll'),
        ];
    }

    // ── Livewire Methods ─────────────────────────────────────────────────────

    public function setActiveEvent(string $eventKey): void
    {
        $this->activeEvent = $eventKey;
        $this->validationResults = [];
    }

    public function saveAll(): void
    {
        $service  = app(NotificationTemplateService::class);
        $registry = app(NotificationShortcodeRegistry::class);

        $hasInvalid    = false;
        $hasDeprecated = false;
        $allWarnings   = [];

        foreach ($this->templates as $eventKey => $targets) {
            foreach ($targets as $targetKey => $fields) {
                $normalizedFields = [
                    ...$fields,
                    'email_subject' => $registry->normalizeContent((string) ($fields['email_subject'] ?? ''), $eventKey, $targetKey),
                    'email_body' => $registry->normalizeContent((string) ($fields['email_body'] ?? ''), $eventKey, $targetKey),
                    'whatsapp_body' => $registry->normalizeContent((string) ($fields['whatsapp_body'] ?? ''), $eventKey, $targetKey),
                ];

                $this->templates[$eventKey][$targetKey] = $normalizedFields;

                $validation = $service->validateTemplateFields($eventKey, $targetKey, $normalizedFields);
                $this->validationResults[$eventKey][$targetKey] = $validation;

                if (! empty($validation['invalid'])) {
                    $hasInvalid = true;
                    $allWarnings = array_merge($allWarnings, $validation['invalid']);
                    continue;
                }

                if (! empty($validation['deprecated'])) {
                    $hasDeprecated = true;
                }

                $service->saveTemplate([
                    'event_key'       => $eventKey,
                    'target_key'      => $targetKey,
                    'email_enabled'   => $normalizedFields['email_enabled'] ?? true,
                    'whatsapp_enabled'=> $normalizedFields['whatsapp_enabled'] ?? true,
                    'email_subject'   => $normalizedFields['email_subject'] ?? '',
                    'email_body'      => $normalizedFields['email_body'] ?? '',
                    'whatsapp_body'   => $normalizedFields['whatsapp_body'] ?? '',
                ]);
            }
        }

        if ($hasInvalid) {
            Notification::make()
                ->title('Template disimpan dengan peringatan')
                ->body('Ditemukan shortcode tidak valid. Silakan periksa bagian yang ditandai merah.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        if ($hasDeprecated) {
            Notification::make()
                ->title('Template berhasil disimpan')
                ->body('Ada shortcode alias lama yang digunakan. Disarankan untuk memperbarui ke shortcode canonical.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Template berhasil disimpan')
            ->success()
            ->send();
    }

    public function resetTarget(string $eventKey, string $targetKey): void
    {
        $service = app(NotificationTemplateService::class);
        $success = $service->resetToDefault($eventKey, $targetKey);

        if (! $success) {
            Notification::make()
                ->title('Gagal reset')
                ->body("Template {$eventKey} / {$targetKey} tidak ditemukan.")
                ->danger()
                ->send();

            return;
        }

        // Reload template dari DB
        $template = $service->getTemplate($eventKey, $targetKey);

        if ($template) {
            $this->templates[$eventKey][$targetKey] = [
                'email_enabled'    => $template->email_enabled,
                'whatsapp_enabled' => $template->whatsapp_enabled,
                'email_subject'    => $template->email_subject ?? '',
                'email_body'       => $template->email_body ?? '',
                'whatsapp_body'    => $template->whatsapp_body ?? '',
                'target_label'     => $template->target_label,
            ];
        }

        unset($this->validationResults[$eventKey][$targetKey]);

        Notification::make()
            ->title('Reset berhasil')
            ->body("Template {$targetKey} dikembalikan ke default.")
            ->success()
            ->send();
    }

    public function resetEventTab(string $eventKey): void
    {
        $service = app(NotificationTemplateService::class);
        $count   = $service->resetEventToDefault($eventKey);

        if ($count === 0) {
            Notification::make()
                ->title('Tidak ada template untuk direset')
                ->warning()
                ->send();

            return;
        }

        // Reload semua template event ini
        $templates = $service->getTemplatesByEvent($eventKey);
        foreach ($templates as $template) {
            $this->templates[$eventKey][$template->target_key] = [
                'email_enabled'    => $template->email_enabled,
                'whatsapp_enabled' => $template->whatsapp_enabled,
                'email_subject'    => $template->email_subject ?? '',
                'email_body'       => $template->email_body ?? '',
                'whatsapp_body'    => $template->whatsapp_body ?? '',
                'target_label'     => $template->target_label,
            ];
        }

        unset($this->validationResults[$eventKey]);

        Notification::make()
            ->title('Reset tab berhasil')
            ->body("Semua template {$eventKey} dikembalikan ke default.")
            ->success()
            ->send();
    }

    // ── View data ────────────────────────────────────────────────────────────

    protected function getViewData(): array
    {
        $registry = app(NotificationShortcodeRegistry::class);

        $shortcodesForEvent = collect($this->templates[$this->activeEvent] ?? [])
            ->keys()
            ->flatMap(fn (string $targetKey) => $registry->forTarget($this->activeEvent, $targetKey))
            ->unique('key')
            ->values()
            ->all();
        $allAliases         = $registry->aliases();

        $activeTargets = $this->templates[$this->activeEvent] ?? [];

        return [
            'tabs'               => self::TABS,
            'activeEvent'        => $this->activeEvent,
            'activeTargets'      => $activeTargets,
            'shortcodesForEvent' => $shortcodesForEvent,
            'allAliases'         => $allAliases,
            'validationResults'  => $this->validationResults,
        ];
    }

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['super_admin', 'admin']);
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('super_admin') || $user->hasRole('admin');
        }

        if (isset($user->role)) {
            return in_array($user->role, ['super_admin', 'admin'], true);
        }

        return false;
    }

    public function getHeading(): string
    {
        return '';
    }
}
