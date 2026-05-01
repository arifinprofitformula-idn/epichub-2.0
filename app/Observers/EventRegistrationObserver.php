<?php

namespace App\Observers;

use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;

class EventRegistrationObserver
{
    public bool $afterCommit = true;

    public function created(EventRegistration $eventRegistration): void
    {
        if (! $this->shouldNotify($eventRegistration)) {
            return;
        }

        $this->notify($eventRegistration);
    }

    public function updated(EventRegistration $eventRegistration): void
    {
        if (! $eventRegistration->wasChanged('status')) {
            return;
        }

        $originalStatus = $eventRegistration->getOriginal('status');

        if ($this->normalizeStatus($originalStatus) === EventRegistrationStatus::Registered->value) {
            return;
        }

        if (! $this->shouldNotify($eventRegistration)) {
            return;
        }

        $this->notify($eventRegistration);
    }

    private function notify(EventRegistration $eventRegistration): void
    {
        $service = app(EmailNotificationService::class);

        $service->sendEventRegistrationConfirmed($eventRegistration);
        $service->sendAdminEventRegistrationNotification($eventRegistration);

        $eventRegistration->loadMissing(['event', 'user']);

        if ($eventRegistration->event && $eventRegistration->user) {
            app(WhatsAppNotificationService::class)->sendToUser(
                user: $eventRegistration->user,
                message: app(WhatsAppMessageTemplateService::class)->render('event_registration_confirmed', [
                    'event_name' => $eventRegistration->event->title,
                    'event_datetime' => $eventRegistration->event->starts_at?->timezone($eventRegistration->event->timezone ?: config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i').' '.($eventRegistration->event->timezone ?: config('app.timezone', 'Asia/Jakarta')),
                    'event_url' => route('my-events.show', $eventRegistration),
                ]),
                eventType: 'event_registration_confirmed',
                metadata: ['notifiable' => $eventRegistration],
            );

            app(WhatsAppNotificationService::class)->sendAdminAlert(
                message: app(WhatsAppMessageTemplateService::class)->render('admin_event_registration', [
                    'event_name' => $eventRegistration->event->title,
                    'member_name' => $eventRegistration->user->name,
                ]),
                eventType: 'admin_event_registration',
                metadata: ['notifiable' => $eventRegistration],
            );

            app(MailketingSubscriberService::class)->addEventParticipantToList(
                $eventRegistration->user,
                $eventRegistration->event,
            );
        }
    }

    private function shouldNotify(EventRegistration $eventRegistration): bool
    {
        return $this->normalizeStatus($eventRegistration->status) === EventRegistrationStatus::Registered->value;
    }

    private function normalizeStatus(mixed $status): ?string
    {
        if ($status instanceof EventRegistrationStatus) {
            return $status->value;
        }

        $value = (string) $status;

        return $value !== '' ? $value : null;
    }
}
