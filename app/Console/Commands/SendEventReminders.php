<?php

namespace App\Console\Commands;

use App\Enums\EventRegistrationStatus;
use App\Models\EmailNotificationLog;
use App\Models\EventRegistration;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Settings\AppSettingService;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'notifications:send-event-reminders';

    protected $description = 'Kirim reminder event H-1 dan 1 jam sebelum event untuk peserta aktif';

    public function handle(AppSettingService $settings, EmailNotificationService $emailService): int
    {
        if (! (bool) $settings->getMailketing('enable_event_reminder', false)) {
            $this->warn('Event reminder dinonaktifkan di settings.');

            return self::SUCCESS;
        }

        $sent = 0;

        if ((bool) $settings->getMailketing('event_reminder_day_before', true)) {
            $sent += $this->sendReminderBatch(
                emailService: $emailService,
                eventType: 'event_reminder_day_before',
                startFrom: now()->addDay()->subMinutes(30),
                startTo: now()->addDay()->addMinutes(30),
                label: 'H-1',
            );
        }

        if ((bool) $settings->getMailketing('event_reminder_hour_before', true)) {
            $sent += $this->sendReminderBatch(
                emailService: $emailService,
                eventType: 'event_reminder_hour_before',
                startFrom: now()->addHour()->subMinutes(15),
                startTo: now()->addHour()->addMinutes(15),
                label: '1 jam lagi',
            );
        }

        $this->info("Total reminder event terkirim: {$sent}");

        return self::SUCCESS;
    }

    private function sendReminderBatch(
        EmailNotificationService $emailService,
        string $eventType,
        \DateTimeInterface $startFrom,
        \DateTimeInterface $startTo,
        string $label,
    ): int {
        $registrations = EventRegistration::query()
            ->with(['event.product', 'user'])
            ->whereIn('status', [EventRegistrationStatus::Registered, EventRegistrationStatus::Attended])
            ->whereHas('event', function ($query) use ($startFrom, $startTo): void {
                $query
                    ->whereNotNull('starts_at')
                    ->whereBetween('starts_at', [$startFrom, $startTo]);
            })
            ->get();

        $sent = 0;

        foreach ($registrations as $registration) {
            $event = $registration->event;
            $user = $registration->user;

            if (! $event || ! $user || blank($user->email)) {
                continue;
            }

            $alreadySent = EmailNotificationLog::query()
                ->where('event_type', $eventType)
                ->where('notifiable_type', EventRegistration::class)
                ->where('notifiable_id', $registration->id)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $emailService->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Reminder Event EPIC HUB',
                view: 'emails.events.reminder',
                data: [
                    'userName' => $user->name,
                    'eventName' => $event->title,
                    'eventSchedule' => $event->starts_at?->timezone($event->timezone ?: config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i').' '.($event->timezone ?: config('app.timezone', 'Asia/Jakarta')),
                    'eventLocation' => filled($event->zoom_url) ? 'Online / detail akses cek halaman Event Saya' : 'Lihat detail di halaman Event Saya',
                    'reminderLabel' => $label,
                    'myEventUrl' => route('my-events.show', $registration),
                    'myEventsUrl' => route('my-events.index'),
                ],
                eventType: $eventType,
                metadata: ['notifiable' => $registration],
            );

            $sent++;
            $this->info("Reminder {$label} dikirim ke {$user->email} untuk event {$event->title}");
        }

        return $sent;
    }
}
