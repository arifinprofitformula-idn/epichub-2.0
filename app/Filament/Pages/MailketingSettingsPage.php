<?php

namespace App\Filament\Pages;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Models\AppSetting;
use App\Models\MailketingList;
use App\Models\EmailNotificationLog;
use App\Services\Mailketing\MailketingClient;
use App\Services\Settings\AppSettingService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class MailketingSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Mailketing';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Settings;

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.mailketing-settings';

    // ── Form state ──────────────────────────────────────────────────────────
    public bool $enable_mailketing         = false;
    public string $mailketing_api_token    = '';
    public string $mailketing_from_name    = '';
    public string $mailketing_from_email   = '';
    public string $mailketing_reply_to_email       = '';
    public string $admin_notification_email        = '';
    public string $mailketing_default_list_id      = '';
    public string $mailketing_customer_list_id     = '';
    public string $mailketing_epi_channel_list_id  = '';
    public string $mailketing_event_participant_list_id = '';
    public string $mailketing_course_student_list_id    = '';
    public bool   $enable_email_logs   = true;
    public bool   $enable_email_queue  = false;
    public string $test_recipient_email = '';

    // Notification toggles
    public bool $notify_user_registered         = true;
    public bool $notify_password_reset          = true;
    public bool $notify_order_created           = true;
    public bool $notify_payment_submitted       = true;
    public bool $notify_payment_approved        = true;
    public bool $notify_payment_rejected        = true;
    public bool $notify_access_granted          = true;
    public bool $notify_admin_order_created     = true;
    public bool $notify_admin_payment_submitted = true;
    public bool $notify_event_registration      = true;
    public bool $notify_admin_event_registration = true;
    public bool $notify_course_enrollment       = true;
    public bool $notify_commission_created      = true;
    public bool $notify_payout_paid             = true;
    public bool $notify_admin_payout_paid       = true;

    public function mount(): void
    {
        $settings = app(AppSettingService::class);

        $this->enable_mailketing        = (bool) $settings->getMailketing('enable_mailketing', false);
        $this->mailketing_from_name     = (string) $settings->getMailketing('mailketing_from_name', '');
        $this->mailketing_from_email    = (string) $settings->getMailketing('mailketing_from_email', '');
        $this->mailketing_reply_to_email = (string) $settings->getMailketing('mailketing_reply_to_email', '');
        $this->admin_notification_email  = (string) $settings->getMailketing('admin_notification_email', '');
        $this->mailketing_default_list_id           = (string) $settings->getMailketing('mailketing_default_list_id', '');
        $this->mailketing_customer_list_id          = (string) $settings->getMailketing('mailketing_customer_list_id', '');
        $this->mailketing_epi_channel_list_id       = (string) $settings->getMailketing('mailketing_epi_channel_list_id', '');
        $this->mailketing_event_participant_list_id = (string) $settings->getMailketing('mailketing_event_participant_list_id', '');
        $this->mailketing_course_student_list_id    = (string) $settings->getMailketing('mailketing_course_student_list_id', '');
        $this->enable_email_logs   = (bool) $settings->getMailketing('enable_email_logs', true);
        $this->enable_email_queue  = (bool) $settings->getMailketing('enable_email_queue', false);
        $this->test_recipient_email = (string) $settings->getMailketing('test_recipient_email', '');

        $this->notify_user_registered         = (bool) $settings->getMailketing('notify_user_registered', true);
        $this->notify_password_reset          = (bool) $settings->getMailketing('notify_password_reset', true);
        $this->notify_order_created           = (bool) $settings->getMailketing('notify_order_created', true);
        $this->notify_payment_submitted       = (bool) $settings->getMailketing('notify_payment_submitted', true);
        $this->notify_payment_approved        = (bool) $settings->getMailketing('notify_payment_approved', true);
        $this->notify_payment_rejected        = (bool) $settings->getMailketing('notify_payment_rejected', true);
        $this->notify_access_granted          = (bool) $settings->getMailketing('notify_access_granted', true);
        $this->notify_admin_order_created     = (bool) $settings->getMailketing('notify_admin_order_created', true);
        $this->notify_admin_payment_submitted = (bool) $settings->getMailketing('notify_admin_payment_submitted', true);
        $this->notify_event_registration      = (bool) $settings->getMailketing('notify_event_registration', true);
        $this->notify_admin_event_registration = (bool) $settings->getMailketing('notify_admin_event_registration', true);
        $this->notify_course_enrollment       = (bool) $settings->getMailketing('notify_course_enrollment', true);
        $this->notify_commission_created      = (bool) $settings->getMailketing('notify_commission_created', true);
        $this->notify_payout_paid             = (bool) $settings->getMailketing('notify_payout_paid', true);
        $this->notify_admin_payout_paid       = (bool) $settings->getMailketing('notify_admin_payout_paid', true);

        // Token tidak ditampilkan penuh — hanya ditampilkan sebagai placeholder masked di view
        // Field mailketing_api_token sengaja dibiarkan kosong di form state agar tidak bocor ke UI
        $this->mailketing_api_token = '';
    }

    public function getHeading(): string
    {
        return '';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    // ── Save action ─────────────────────────────────────────────────────────

    public function save(): void
    {
        $settings = app(AppSettingService::class);

        $settings->setMailketing('enable_mailketing',  $this->enable_mailketing,  false, 'boolean');
        $settings->setMailketing('mailketing_from_name',  $this->mailketing_from_name);
        $settings->setMailketing('mailketing_from_email', $this->mailketing_from_email);
        $settings->setMailketing('mailketing_reply_to_email', $this->mailketing_reply_to_email);
        $settings->setMailketing('admin_notification_email',  $this->admin_notification_email);
        $settings->setMailketing('mailketing_default_list_id',           $this->mailketing_default_list_id);
        $settings->setMailketing('mailketing_customer_list_id',          $this->mailketing_customer_list_id);
        $settings->setMailketing('mailketing_epi_channel_list_id',       $this->mailketing_epi_channel_list_id);
        $settings->setMailketing('mailketing_event_participant_list_id', $this->mailketing_event_participant_list_id);
        $settings->setMailketing('mailketing_course_student_list_id',    $this->mailketing_course_student_list_id);
        $settings->setMailketing('enable_email_logs',  $this->enable_email_logs,  false, 'boolean');
        $settings->setMailketing('enable_email_queue', $this->enable_email_queue, false, 'boolean');
        $settings->setMailketing('test_recipient_email', $this->test_recipient_email);

        $settings->setMailketing('notify_user_registered',         $this->notify_user_registered,         false, 'boolean');
        $settings->setMailketing('notify_password_reset',          $this->notify_password_reset,          false, 'boolean');
        $settings->setMailketing('notify_order_created',           $this->notify_order_created,           false, 'boolean');
        $settings->setMailketing('notify_payment_submitted',       $this->notify_payment_submitted,       false, 'boolean');
        $settings->setMailketing('notify_payment_approved',        $this->notify_payment_approved,        false, 'boolean');
        $settings->setMailketing('notify_payment_rejected',        $this->notify_payment_rejected,        false, 'boolean');
        $settings->setMailketing('notify_access_granted',          $this->notify_access_granted,          false, 'boolean');
        $settings->setMailketing('notify_admin_order_created',     $this->notify_admin_order_created,     false, 'boolean');
        $settings->setMailketing('notify_admin_payment_submitted', $this->notify_admin_payment_submitted, false, 'boolean');
        $settings->setMailketing('notify_event_registration',      $this->notify_event_registration,      false, 'boolean');
        $settings->setMailketing('notify_admin_event_registration', $this->notify_admin_event_registration, false, 'boolean');
        $settings->setMailketing('notify_course_enrollment',       $this->notify_course_enrollment,       false, 'boolean');
        $settings->setMailketing('notify_commission_created',      $this->notify_commission_created,      false, 'boolean');
        $settings->setMailketing('notify_payout_paid',             $this->notify_payout_paid,             false, 'boolean');
        $settings->setMailketing('notify_admin_payout_paid',       $this->notify_admin_payout_paid,       false, 'boolean');

        // Simpan token hanya jika diisi (tidak kosong)
        if (filled($this->mailketing_api_token)) {
            $settings->setMailketing('mailketing_api_token', $this->mailketing_api_token, true);
            $this->mailketing_api_token = '';
        }

        Notification::make()
            ->title('Pengaturan tersimpan')
            ->success()
            ->send();
    }

    // ── Header actions ───────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('primary')
                ->action('save'),

            Action::make('testConnection')
                ->label('Test Connection')
                ->icon(Heroicon::OutlinedWifi)
                ->color('info')
                ->action('runTestConnection'),

            Action::make('syncLists')
                ->label('Sync Lists')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('success')
                ->action('runSyncLists'),

            Action::make('sendTestEmail')
                ->label('Send Test Email')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('warning')
                ->action('runSendTestEmail'),
        ];
    }

    // ── Action handlers ──────────────────────────────────────────────────────

    public function runTestConnection(): void
    {
        $client = app(MailketingClient::class);

        if (! $client->isEnabled()) {
            Notification::make()->title('Mailketing tidak diaktifkan')->warning()->send();
            return;
        }

        $result = $client->getLists();

        if ($result['success']) {
            $count = count($result['lists'] ?? []);
            $this->syncMailketingLists($result['lists'] ?? []);

            Notification::make()
                ->title("Koneksi berhasil — {$count} list ditemukan")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Koneksi gagal')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function runSyncLists(): void
    {
        $client = app(MailketingClient::class);

        if (! $client->isEnabled()) {
            Notification::make()->title('Mailketing tidak diaktifkan')->warning()->send();
            return;
        }

        $result = $client->getLists();

        if ($result['success']) {
            $count = $this->syncMailketingLists($result['lists'] ?? []);

            Notification::make()
                ->title("Sync selesai — {$count} list disimpan")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Sync gagal')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function runSendTestEmail(): void
    {
        $client   = app(MailketingClient::class);
        $settings = app(AppSettingService::class);

        if (! $client->isEnabled()) {
            Notification::make()->title('Mailketing tidak diaktifkan')->warning()->send();
            return;
        }

        $recipient = (string) $settings->getMailketing('test_recipient_email', '');

        if (blank($recipient)) {
            Notification::make()
                ->title('Email penerima test belum diisi')
                ->body('Isi field "Test Recipient Email" terlebih dahulu dan simpan pengaturan.')
                ->warning()
                ->send();
            return;
        }

        $result = $client->sendEmail([
            'recipient'  => $recipient,
            'subject'    => 'Test Email EPIC HUB',
            'content'    => $this->buildTestEmailContent(),
            'event_type' => 'test_email',
        ]);

        if ($result['success']) {
            Notification::make()
                ->title('Test email berhasil dikirim')
                ->body("Terkirim ke: {$recipient}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Test email gagal')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function syncMailketingLists(array $lists): int
    {
        $now = now();
        foreach ($lists as $item) {
            if (empty($item['list_id'])) {
                continue;
            }
            MailketingList::updateOrCreate(
                ['list_id' => (string) $item['list_id']],
                [
                    'list_name'   => $item['list_name'] ?? '',
                    'raw_payload' => $item,
                    'synced_at'   => $now,
                ],
            );
        }
        return count($lists);
    }

    private function buildTestEmailContent(): string
    {
        $appName = config('app.name', 'EPIC HUB');
        $time    = Carbon::now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i');

        return <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
  <h2 style="color: #f59e0b;">Test Email dari {$appName}</h2>
  <p>Ini adalah email test yang dikirim dari sistem <strong>{$appName}</strong> melalui Mailketing.</p>
  <p>Jika Anda menerima email ini, berarti integrasi Mailketing berfungsi dengan baik.</p>
  <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">
  <p style="color: #6b7280; font-size: 12px;">Dikirim pada: {$time}</p>
</div>
HTML;
    }

    // ── View data ─────────────────────────────────────────────────────────────

    protected function getViewData(): array
    {
        $settings      = app(AppSettingService::class);
        $tokenStored   = filled($settings->getMailketing('mailketing_api_token', ''));
        $listsQuery    = Schema::hasTable('mailketing_lists')
            ? MailketingList::orderBy('list_name')->get()
            : collect();
        $recentLogs    = Schema::hasTable('email_notification_logs')
            ? EmailNotificationLog::latest()->limit(5)->get()
            : collect();

        return [
            'tokenStored'       => $tokenStored,
            'mailketingLists'   => $listsQuery,
            'recentLogs'        => $recentLogs,
            'isEnabled'         => $this->enable_mailketing,
        ];
    }
}
