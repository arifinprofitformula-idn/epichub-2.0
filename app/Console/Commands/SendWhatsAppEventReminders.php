<?php

namespace App\Console\Commands;

use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Models\WhatsAppNotificationLog;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class SendWhatsAppEventReminders extends Command
{
    protected $signature = 'whatsapp:send-event-reminders';

    protected $description = 'Kirim reminder event via WhatsApp untuk peserta terdaftar';

    public function handle(
        AppSettingService $settings,
        WhatsAppNotificationService $service,
        WhatsAppMessageTemplateService $templates,
    ): int {
        if (! (bool) $settings->getDripSender('enable_whatsapp_event_reminder', false)) {
            $this->warn('WhatsApp event reminder dinonaktifkan.');

            return self::SUCCESS;
        }

        $sent = 0;

        if ((bool) $settings->getDripSender('event_reminder_day_before', true)) {
            $sent += $this->sendBatch(
                service: $service,
                templates: $templates,
                eventType: 'event_reminder_day_before',
                startFrom: now()->addDay()->subMinutes(30),
                startTo: now()->addDay()->addMinutes(30),
            );
        }

        if ((bool) $settings->getDripSender('event_reminder_hour_before', true)) {
            $sent += $this->sendBatch(
                service: $service,
                templates: $templates,
                eventType: 'event_reminder_hour_before',
                startFrom: now()->addHour()->subMinutes(15),
                startTo: now()->addHour()->addMinutes(15),
            );
        }

        $this->info("Reminder event WhatsApp diproses: {$sent}");

        return self::SUCCESS;
    }

    private function sendBatch(
        WhatsAppNotificationService $service,
        WhatsAppMessageTemplateService $templates,
        string $eventType,
        \DateTimeInterface $startFrom,
        \DateTimeInterface $startTo,
    ): int {
        $sent = 0;

        $registrations = EventRegistration::query()
            ->with(['event', 'user'])
            ->whereIn('status', [EventRegistrationStatus::Registered, EventRegistrationStatus::Attended])
            ->whereHas('event', fn ($query) => $query->whereBetween('starts_at', [$startFrom, $startTo]))
            ->get();

        foreach ($registrations as $registration) {
            $event = $registration->event;
            $user = $registration->user;

            if (! $event || ! $user) {
                continue;
            }

            $alreadySent = WhatsAppNotificationLog::query()
                ->where('event_type', $eventType)
                ->where('notifiable_type', EventRegistration::class)
                ->where('notifiable_id', $registration->id)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $message = $templates->render($eventType, [
                'event_name' => $event->title,
                'event_datetime' => $event->starts_at?->timezone($event->timezone ?: config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i').' '.($event->timezone ?: config('app.timezone', 'Asia/Jakarta')),
                'event_url' => route('my-events.show', $registration),
            ]);

            $service->sendToUser($user, $message, $eventType, ['notifiable' => $registration]);
            $sent++;
        }

        return $sent;
    }
}
