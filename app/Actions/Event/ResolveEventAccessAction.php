<?php

namespace App\Actions\Event;

use App\Actions\Access\CheckProductAccessAction;
use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\EventRegistrationStatus;
use App\Enums\EventStatus;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class ResolveEventAccessAction
{
    public function __construct(
        protected CheckProductAccessAction $checkProductAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    /**
     * @return array{
     *   event: \App\Models\Event,
     *   registration: EventRegistration,
     *   canJoin: bool,
     *   canViewReplay: bool
     * }
     */
    public function execute(User $user, EventRegistration $registration, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $registration->loadMissing(['event.product', 'userProduct']);

        if ($registration->user_id !== $user->id) {
            throw new RuntimeException('Akses tidak valid.');
        }

        if ($registration->status === EventRegistrationStatus::Cancelled) {
            throw new RuntimeException('Registrasi dibatalkan.');
        }

        $event = $registration->event;

        if (! $event) {
            throw new RuntimeException('Event tidak ditemukan.');
        }

        if ($registration->user_product_id !== null) {
            if (! $registration->userProduct) {
                throw new RuntimeException('Akses tidak aktif.');
            }

            $validatedUserProduct = $this->checkProductAccess->execute($user, $registration->userProduct);

            if (! $validatedUserProduct) {
                throw new RuntimeException('Akses tidak aktif.');
            }
        }

        $hasZoomUrl = filled($event->zoom_url) && $this->isSafeHttpUrl((string) $event->zoom_url);
        $eventJoinAllowed = ! in_array($event->status, [EventStatus::Closed, EventStatus::Completed, EventStatus::Draft], true);

        $canJoin = $registration->isActive() && $hasZoomUrl && $eventJoinAllowed;

        $hasReplay = filled($event->replay_url) && $this->isSafeHttpUrl((string) $event->replay_url);
        $canViewReplay = $registration->isActive() && $event->status === EventStatus::Completed && $hasReplay;

        $this->logAccess->execute(
            action: AccessLogAction::EventAccessed,
            user: $user,
            userProduct: $registration->userProduct,
            product: $event->product,
            actor: $user,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: [
                'event_id' => $event->id,
                'event_registration_id' => $registration->id,
            ],
        );

        return [
            'event' => $event,
            'registration' => $registration,
            'canJoin' => $canJoin,
            'canViewReplay' => $canViewReplay,
        ];
    }

    protected function isSafeHttpUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return ! Str::contains($url, ["\r", "\n"]);
    }
}

