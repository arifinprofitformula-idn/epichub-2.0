<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Event\ResolveEventAccessAction;
use App\Enums\AccessLogAction;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MyEventController extends Controller
{
    public function __construct(
        protected ResolveEventAccessAction $resolveEventAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function index(Request $request): View
    {
        $registrations = EventRegistration::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->with(['event'])
            ->latest('registered_at')
            ->paginate(12);

        return view('my-events.index', [
            'registrations' => $registrations,
        ]);
    }

    public function show(Request $request, EventRegistration $eventRegistration): View
    {
        try {
            $resolved = $this->resolveEventAccess->execute(
                user: $request->user(),
                registration: $eventRegistration,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->logAccess->execute(
                action: AccessLogAction::AccessDenied,
                user: $request->user(),
                userProduct: $eventRegistration->userProduct,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: [
                    'reason' => 'event_access_denied',
                    'event_registration_id' => $eventRegistration->id,
                ],
            );

            abort(404);
        }

        return view('my-events.show', $resolved);
    }
}

