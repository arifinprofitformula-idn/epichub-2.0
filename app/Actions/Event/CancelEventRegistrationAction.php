<?php

namespace App\Actions\Event;

use App\Enums\EventRegistrationStatus;
use App\Models\EventRegistration;
use App\Models\User;

class CancelEventRegistrationAction
{
    public function execute(EventRegistration $registration, User $actor): EventRegistration
    {
        $registration->update([
            'status' => EventRegistrationStatus::Cancelled,
            'cancelled_at' => now(),
            'cancelled_by' => $actor->id,
        ]);

        return $registration->refresh();
    }
}

