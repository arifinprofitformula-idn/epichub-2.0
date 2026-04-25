<?php

namespace App\Actions\Event;

use App\Enums\EventStatus;
use App\Enums\ProductType;
use App\Models\Event;
use App\Models\Product;
use RuntimeException;

class CheckEventCheckoutEligibilityAction
{
    public function execute(Product $product): Event
    {
        $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

        if ($type !== ProductType::Event->value) {
            throw new RuntimeException('Produk bukan event.');
        }

        $event = Event::query()
            ->withCount(['activeRegistrations as active_registrations_count'])
            ->where('product_id', $product->id)
            ->first();

        if (! $event) {
            throw new RuntimeException('Event belum tersedia.');
        }

        if (! in_array($event->status, [EventStatus::Published, EventStatus::Ongoing], true)) {
            throw new RuntimeException('Event belum tersedia untuk pendaftaran.');
        }

        if ($event->status === EventStatus::Closed || $event->status === EventStatus::Completed) {
            throw new RuntimeException('Event sudah ditutup.');
        }

        if ($event->published_at !== null && $event->published_at->isFuture()) {
            throw new RuntimeException('Event belum dipublish.');
        }

        if ($event->isFull()) {
            throw new RuntimeException('Kuota event sudah penuh.');
        }

        return $event;
    }
}

