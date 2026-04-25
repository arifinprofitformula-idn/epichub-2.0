<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EventCatalogController
{
    public function index(Request $request): View
    {
        $events = Event::query()
            ->published()
            ->withCount(['activeRegistrations as active_registrations_count'])
            ->orderByDesc('is_featured')
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event): View
    {
        $event->loadMissing(['product']);

        abort_if($event->status === EventStatus::Draft, 404);
        abort_if($event->status === EventStatus::Closed, 404);
        abort_if($event->published_at !== null && $event->published_at->isFuture(), 404);

        return view('events.show', [
            'event' => $event,
        ]);
    }
}

