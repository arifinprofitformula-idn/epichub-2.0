<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Event\ResolveEventAccessAction;
use App\Enums\AccessLogAction;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MyEventAccessController extends Controller
{
    public function __construct(
        protected ResolveEventAccessAction $resolveEventAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function join(Request $request, EventRegistration $eventRegistration): RedirectResponse
    {
        try {
            $resolved = $this->resolveEventAccess->execute(
                user: $request->user(),
                registration: $eventRegistration,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->deny($request, $eventRegistration, 'event_join_denied');
            abort(404);
        }

        if (! $resolved['canJoin']) {
            $this->deny($request, $eventRegistration, 'event_join_not_allowed');
            abort(404);
        }

        $event = $resolved['event'];

        $this->logAccess->execute(
            action: AccessLogAction::EventJoinClicked,
            user: $request->user(),
            userProduct: $eventRegistration->userProduct,
            product: $event->product,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'event_id' => $event->id,
                'event_registration_id' => $eventRegistration->id,
            ],
        );

        return redirect()->away((string) $event->zoom_url);
    }

    public function replay(Request $request, EventRegistration $eventRegistration): RedirectResponse
    {
        try {
            $resolved = $this->resolveEventAccess->execute(
                user: $request->user(),
                registration: $eventRegistration,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->deny($request, $eventRegistration, 'event_replay_denied');
            abort(404);
        }

        if (! $resolved['canViewReplay']) {
            $this->deny($request, $eventRegistration, 'event_replay_not_allowed');
            abort(404);
        }

        $event = $resolved['event'];

        $this->logAccess->execute(
            action: AccessLogAction::EventReplayOpened,
            user: $request->user(),
            userProduct: $eventRegistration->userProduct,
            product: $event->product,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'event_id' => $event->id,
                'event_registration_id' => $eventRegistration->id,
            ],
        );

        return redirect()->away((string) $event->replay_url);
    }

    protected function deny(Request $request, EventRegistration $registration, string $reason): void
    {
        $this->logAccess->execute(
            action: AccessLogAction::AccessDenied,
            user: $request->user(),
            userProduct: $registration->userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'reason' => $reason,
                'event_registration_id' => $registration->id,
            ],
        );
    }
}

