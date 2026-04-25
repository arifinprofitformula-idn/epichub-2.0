<?php

namespace App\Actions\Event;

use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Models\User;

class MarkEventAttendedAction
{
    public function __construct(
        protected LogAccessAction $logAccess,
    ) {
    }

    public function execute(EventRegistration $registration, User $actor, ?string $ipAddress = null, ?string $userAgent = null): EventRegistration
    {
        $registration->loadMissing(['event.product', 'userProduct', 'user']);

        $now = now();

        $registration->update([
            'status' => EventRegistrationStatus::Attended,
            'attended_at' => $now,
            'checked_in_by' => $actor->id,
        ]);

        $this->logAccess->execute(
            action: AccessLogAction::EventAttended,
            user: $registration->user,
            userProduct: $registration->userProduct,
            product: $registration->event?->product,
            order: $registration->order,
            actor: $actor,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: [
                'event_id' => $registration->event_id,
                'event_registration_id' => $registration->id,
            ],
        );

        return $registration->refresh();
    }
}

