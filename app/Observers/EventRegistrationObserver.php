<?php

namespace App\Observers;

use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayloadBuilder;

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
        $dispatcher = app(NotificationDispatcher::class);
        $payload = app(NotificationPayloadBuilder::class)->forEventRegistration($eventRegistration);

        $dispatcher->notifyMemberEmail(
            eventKey: 'event_registration_confirmed',
            user: $eventRegistration->user,
            payload: $payload,
            notifiable: $eventRegistration,
            fallback: fn () => app(EmailNotificationService::class)->sendEventRegistrationConfirmed($eventRegistration),
        );

        $dispatcher->notifyAdminEmail(
            eventKey: 'admin_event_registration',
            payload: app(NotificationPayloadBuilder::class)->forAdminEventRegistration($eventRegistration),
            notifiable: $eventRegistration,
            fallback: fn () => app(EmailNotificationService::class)->sendAdminEventRegistrationNotification($eventRegistration),
        );

        $eventRegistration->loadMissing(['event', 'user']);

        if ($eventRegistration->event && $eventRegistration->user) {
            $dispatcher->notifyMemberWhatsApp(
                eventKey: 'event_registration_confirmed',
                user: $eventRegistration->user,
                payload: $payload,
                notifiable: $eventRegistration,
                legacyData: [
                    'event_name' => $eventRegistration->event->title,
                    'event_datetime' => $eventRegistration->event->starts_at?->timezone($eventRegistration->event->timezone ?: config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i').' '.($eventRegistration->event->timezone ?: config('app.timezone', 'Asia/Jakarta')),
                    'event_url' => route('my-events.show', $eventRegistration),
                ],
            );

            $dispatcher->notifyAdminWhatsApp(
                eventKey: 'admin_event_registration',
                payload: app(NotificationPayloadBuilder::class)->forAdminEventRegistration($eventRegistration),
                notifiable: $eventRegistration,
                legacyData: [
                    'event_name' => $eventRegistration->event->title,
                    'member_name' => $eventRegistration->user->name,
                ],
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
