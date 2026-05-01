<?php

namespace App\Observers;

use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;

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
