<?php

namespace App\Actions\Event;

use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\EventRegistrationStatus;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegisterUserForEventAction
{
    public function __construct(
        protected LogAccessAction $logAccess,
    ) {
    }

    public function execute(
        User $user,
        Event $event,
        ?UserProduct $userProduct = null,
        ?Order $order = null,
        ?OrderItem $orderItem = null,
        ?Product $sourceProduct = null,
        ?User $actor = null,
    ): EventRegistration {
        return DB::transaction(function () use ($user, $event, $userProduct, $order, $orderItem, $sourceProduct, $actor): EventRegistration {
            $event = Event::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($event->status, [EventStatus::Published, EventStatus::Ongoing], true)) {
                throw new RuntimeException('Event tidak tersedia untuk registrasi.');
            }

            if ($event->status === EventStatus::Closed || $event->status === EventStatus::Completed) {
                throw new RuntimeException('Event sudah ditutup.');
            }

            if ($event->published_at !== null && $event->published_at->isFuture()) {
                throw new RuntimeException('Event belum dipublish.');
            }

            $existing = EventRegistration::query()
                ->withTrashed()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing && ! $existing->isCancelled()) {
                return $existing;
            }

            $activeCount = EventRegistration::query()
                ->where('event_id', $event->id)
                ->whereIn('status', [EventRegistrationStatus::Registered, EventRegistrationStatus::Attended])
                ->count();

            if ($event->quota !== null && $activeCount >= $event->quota) {
                throw new RuntimeException('Kuota event sudah penuh.');
            }

            $now = now();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update([
                    'user_product_id' => $userProduct?->id ?? $existing->user_product_id,
                    'order_id' => $order?->id ?? $existing->order_id,
                    'order_item_id' => $orderItem?->id ?? $existing->order_item_id,
                    'source_product_id' => $sourceProduct?->id ?? $existing->source_product_id,
                    'status' => EventRegistrationStatus::Registered,
                    'registered_at' => $existing->registered_at ?? $now,
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                ]);

                $registration = $existing->refresh();
            } else {
                $registration = EventRegistration::query()->create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'user_product_id' => $userProduct?->id,
                    'order_id' => $order?->id,
                    'order_item_id' => $orderItem?->id,
                    'source_product_id' => $sourceProduct?->id,
                    'status' => EventRegistrationStatus::Registered,
                    'registered_at' => $now,
                ]);
            }

            $this->logAccess->execute(
                action: AccessLogAction::EventRegistered,
                user: $user,
                userProduct: $userProduct,
                product: $event->product,
                order: $order,
                actor: $actor,
                metadata: [
                    'event_id' => $event->id,
                    'event_registration_id' => $registration->id,
                ],
            );

            return $registration;
        });
    }
}

