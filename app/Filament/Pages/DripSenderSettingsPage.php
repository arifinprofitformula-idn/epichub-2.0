<?php

namespace App\Filament\Pages;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\WhatsAppNotificationLogs\WhatsAppNotificationLogResource;
use App\Models\DripsenderList;
use App\Models\WhatsAppNotificationLog;
use App\Services\DripSender\DripSenderClient;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use App\Services\Settings\AppSettingService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class DripSenderSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'WhatsApp - DripSender';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Settings;

    protected static ?int $navigationSort = 81;

    protected string $view = 'filament.pages.dripsender-settings';

    public bool $enable_dripsender = false;

    public string $dripsender_api_key = '';

    public string $dripsender_base_url = 'https://api.dripsender.id';

    public string $dripsender_default_country_code = '62';

    public string $dripsender_admin_phone_numbers = '';

    public string $dripsender_default_footer = 'EPIC HUB';

    public bool $dripsender_enable_logs = true;

    public bool $dripsender_enable_queue = false;

    public string $dripsender_test_phone = '';

    public string $dripsender_default_list_id = '';

    public string $dripsender_customer_list_id = '';

    public string $dripsender_epi_channel_list_id = '';

    public string $dripsender_event_participant_list_id = '';

    public string $dripsender_course_student_list_id = '';

    public bool $whatsapp_notify_user_registered = true;

    public bool $whatsapp_notify_password_reset = true;

    public bool $whatsapp_notify_order_created = true;

    public bool $whatsapp_notify_payment_submitted = true;

    public bool $whatsapp_notify_payment_approved = true;

    public bool $whatsapp_notify_payment_rejected = true;

    public bool $whatsapp_notify_access_granted = true;

    public bool $whatsapp_notify_event_registration = true;

    public bool $whatsapp_notify_course_enrollment = true;

    public bool $whatsapp_notify_commission_created = true;

    public bool $whatsapp_notify_payout_paid = true;

    public bool $whatsapp_notify_admin_order_created = true;

    public bool $whatsapp_notify_admin_payment_submitted = true;

    public bool $whatsapp_notify_admin_event_registration = true;

    public bool $whatsapp_notify_admin_payout_paid = true;

    public bool $enable_whatsapp_payment_reminder = false;

    public int $payment_reminder_after_hours = 24;

    public bool $enable_whatsapp_event_reminder = false;

    public bool $event_reminder_day_before = true;

    public bool $event_reminder_hour_before = true;

    public function mount(): void
    {
        $settings = app(AppSettingService::class);

        foreach ([
            'enable_dripsender' => false,
            'dripsender_base_url' => 'https://api.dripsender.id',
            'dripsender_default_country_code' => '62',
            'dripsender_admin_phone_numbers' => '',
            'dripsender_default_footer' => 'EPIC HUB',
            'dripsender_enable_logs' => true,
            'dripsender_enable_queue' => false,
            'dripsender_test_phone' => '',
            'dripsender_default_list_id' => '',
            'dripsender_customer_list_id' => '',
            'dripsender_epi_channel_list_id' => '',
            'dripsender_event_participant_list_id' => '',
            'dripsender_course_student_list_id' => '',
            'whatsapp_notify_user_registered' => true,
            'whatsapp_notify_password_reset' => true,
            'whatsapp_notify_order_created' => true,
            'whatsapp_notify_payment_submitted' => true,
            'whatsapp_notify_payment_approved' => true,
            'whatsapp_notify_payment_rejected' => true,
            'whatsapp_notify_access_granted' => true,
            'whatsapp_notify_event_registration' => true,
            'whatsapp_notify_course_enrollment' => true,
            'whatsapp_notify_commission_created' => true,
            'whatsapp_notify_payout_paid' => true,
            'whatsapp_notify_admin_order_created' => true,
            'whatsapp_notify_admin_payment_submitted' => true,
            'whatsapp_notify_admin_event_registration' => true,
            'whatsapp_notify_admin_payout_paid' => true,
            'enable_whatsapp_payment_reminder' => false,
            'payment_reminder_after_hours' => 24,
            'enable_whatsapp_event_reminder' => false,
            'event_reminder_day_before' => true,
            'event_reminder_hour_before' => true,
        ] as $key => $default) {
            $this->{$key} = $settings->getDripSender($key, $default);
        }

        $this->dripsender_api_key = '';
    }

    public function getHeading(): string
    {
        return '';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function save(): void
    {
        $settings = app(AppSettingService::class);

        foreach ([
            'enable_dripsender' => ['value' => $this->enable_dripsender, 'type' => 'boolean'],
            'dripsender_base_url' => ['value' => $this->dripsender_base_url],
            'dripsender_default_country_code' => ['value' => $this->dripsender_default_country_code],
            'dripsender_admin_phone_numbers' => ['value' => $this->dripsender_admin_phone_numbers],
            'dripsender_default_footer' => ['value' => $this->dripsender_default_footer],
            'dripsender_enable_logs' => ['value' => $this->dripsender_enable_logs, 'type' => 'boolean'],
            'dripsender_enable_queue' => ['value' => $this->dripsender_enable_queue, 'type' => 'boolean'],
            'dripsender_test_phone' => ['value' => $this->dripsender_test_phone],
            'dripsender_default_list_id' => ['value' => $this->dripsender_default_list_id],
            'dripsender_customer_list_id' => ['value' => $this->dripsender_customer_list_id],
            'dripsender_epi_channel_list_id' => ['value' => $this->dripsender_epi_channel_list_id],
            'dripsender_event_participant_list_id' => ['value' => $this->dripsender_event_participant_list_id],
            'dripsender_course_student_list_id' => ['value' => $this->dripsender_course_student_list_id],
            'whatsapp_notify_user_registered' => ['value' => $this->whatsapp_notify_user_registered, 'type' => 'boolean'],
            'whatsapp_notify_password_reset' => ['value' => $this->whatsapp_notify_password_reset, 'type' => 'boolean'],
            'whatsapp_notify_order_created' => ['value' => $this->whatsapp_notify_order_created, 'type' => 'boolean'],
            'whatsapp_notify_payment_submitted' => ['value' => $this->whatsapp_notify_payment_submitted, 'type' => 'boolean'],
            'whatsapp_notify_payment_approved' => ['value' => $this->whatsapp_notify_payment_approved, 'type' => 'boolean'],
            'whatsapp_notify_payment_rejected' => ['value' => $this->whatsapp_notify_payment_rejected, 'type' => 'boolean'],
            'whatsapp_notify_access_granted' => ['value' => $this->whatsapp_notify_access_granted, 'type' => 'boolean'],
            'whatsapp_notify_event_registration' => ['value' => $this->whatsapp_notify_event_registration, 'type' => 'boolean'],
            'whatsapp_notify_course_enrollment' => ['value' => $this->whatsapp_notify_course_enrollment, 'type' => 'boolean'],
            'whatsapp_notify_commission_created' => ['value' => $this->whatsapp_notify_commission_created, 'type' => 'boolean'],
            'whatsapp_notify_payout_paid' => ['value' => $this->whatsapp_notify_payout_paid, 'type' => 'boolean'],
            'whatsapp_notify_admin_order_created' => ['value' => $this->whatsapp_notify_admin_order_created, 'type' => 'boolean'],
            'whatsapp_notify_admin_payment_submitted' => ['value' => $this->whatsapp_notify_admin_payment_submitted, 'type' => 'boolean'],
            'whatsapp_notify_admin_event_registration' => ['value' => $this->whatsapp_notify_admin_event_registration, 'type' => 'boolean'],
            'whatsapp_notify_admin_payout_paid' => ['value' => $this->whatsapp_notify_admin_payout_paid, 'type' => 'boolean'],
            'enable_whatsapp_payment_reminder' => ['value' => $this->enable_whatsapp_payment_reminder, 'type' => 'boolean'],
            'payment_reminder_after_hours' => ['value' => $this->payment_reminder_after_hours, 'type' => 'integer'],
            'enable_whatsapp_event_reminder' => ['value' => $this->enable_whatsapp_event_reminder, 'type' => 'boolean'],
            'event_reminder_day_before' => ['value' => $this->event_reminder_day_before, 'type' => 'boolean'],
            'event_reminder_hour_before' => ['value' => $this->event_reminder_hour_before, 'type' => 'boolean'],
        ] as $key => $config) {
            $settings->setDripSender($key, $config['value'], false, $config['type'] ?? null);
        }

        if (filled($this->dripsender_api_key)) {
            $settings->setDripSender('dripsender_api_key', $this->dripsender_api_key, true);
            $this->dripsender_api_key = '';
        }

        Notification::make()->title('Pengaturan DripSender tersimpan')->success()->send();
    }

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
            Action::make('sendTestWhatsApp')
                ->label('Send Test WhatsApp')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('warning')
                ->action('runSendTestWhatsApp'),
            Action::make('viewLogs')
                ->label('View Logs')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->url(WhatsAppNotificationLogResource::getUrl('index')),
        ];
    }

    public function runTestConnection(): void
    {
        $client = app(DripSenderClient::class);
        $result = $client->getLists();

        if ($result['success']) {
            $count = $this->syncDripsenderLists($result['lists'] ?? []);

            Notification::make()
                ->title("Koneksi berhasil - {$count} list ditemukan")
                ->success()
                ->send();

            return;
        }

        Notification::make()->title('Koneksi gagal')->body($result['message'])->danger()->send();
    }

    public function runSyncLists(): void
    {
        $client = app(DripSenderClient::class);
        $result = $client->getLists();

        if ($result['success']) {
            $count = $this->syncDripsenderLists($result['lists'] ?? []);

            Notification::make()
                ->title("Sync selesai - {$count} list disimpan")
                ->success()
                ->send();

            return;
        }

        Notification::make()->title('Sync gagal')->body($result['message'])->danger()->send();
    }

    public function runSendTestWhatsApp(): void
    {
        $phone = (string) app(AppSettingService::class)->getDripSender('dripsender_test_phone', '');

        if (blank($phone)) {
            Notification::make()
                ->title('Test phone belum diisi')
                ->body('Isi field "DripSender Test Phone" terlebih dahulu lalu simpan pengaturan.')
                ->warning()
                ->send();

            return;
        }

        $message = app(WhatsAppMessageTemplateService::class)->render('test_whatsapp', [
            'sent_at' => Carbon::now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i'),
        ]);

        $log = app(WhatsAppNotificationService::class)->sendToPhone($phone, $message, 'test_whatsapp', [
            'recipient_name' => 'Admin Test',
        ]);

        $log?->refresh();

        $notification = Notification::make()
            ->title($log?->status === 'sent' ? 'Test WhatsApp berhasil dikirim' : 'Test WhatsApp diproses')
            ->body($log?->error_message ?: "Tujuan: {$phone}");

        if ($log?->status === 'sent') {
            $notification->success()->send();

            return;
        }

        if ($log?->status === 'pending') {
            $notification->info()->send();

            return;
        }

        $notification->danger()->send();
    }

    protected function getViewData(): array
    {
        $settings = app(AppSettingService::class);

        return [
            'apiKeyStored' => filled($settings->getDripSender('dripsender_api_key', '')),
            'dripsenderLists' => Schema::hasTable('dripsender_lists') ? DripsenderList::query()->orderBy('list_name')->get() : collect(),
            'recentLogs' => Schema::hasTable('whatsapp_notification_logs') ? WhatsAppNotificationLog::query()->latest()->limit(5)->get() : collect(),
            'isEnabled' => $this->enable_dripsender,
        ];
    }

    /** @param array<int, array<string, mixed>> $lists */
    private function syncDripsenderLists(array $lists): int
    {
        $count = 0;
        $now = now();

        foreach ($lists as $item) {
            $listId = (string) ($item['id'] ?? $item['list_id'] ?? '');

            if ($listId === '') {
                continue;
            }

            DripsenderList::query()->updateOrCreate(
                ['list_id' => $listId],
                [
                    'list_name' => (string) ($item['name'] ?? $item['list_name'] ?? ''),
                    'contact_count' => data_get($item, 'contact_count') ?? data_get($item, 'total_contact'),
                    'raw_payload' => $item,
                    'synced_at' => $now,
                ],
            );

            $count++;
        }

        return $count;
    }
}
