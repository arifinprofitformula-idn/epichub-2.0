<?php

namespace App\Actions\Access;

use App\Enums\ProductType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\UserProduct;
use Illuminate\Support\Collection;

class ResolveProductDeliveryAction
{
    /**
     * @return array{
     *   userProduct: UserProduct,
     *   product: \App\Models\Product|null,
     *   type: string|null,
     *   files: Collection<int, \App\Models\ProductFile>,
     *   childUserProducts: Collection<int, UserProduct>,
     *   event: Event|null,
     *   eventRegistration: EventRegistration|null,
     *   placeholderTitle: string|null,
     *   placeholderMessage: string|null
     * }
     */
    public function execute(UserProduct $userProduct): array
    {
        $userProduct->loadMissing([
            'product.files' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'product.event',
            'order',
            'sourceProduct',
        ]);

        $product = $userProduct->product;

        $type = null;

        if ($product !== null) {
            $type = $product->product_type instanceof ProductType
                ? $product->product_type->value
                : (string) $product->product_type;
        }

        $files = $product?->files ?? collect();

        $childUserProducts = collect();

        if ($type === ProductType::Bundle->value) {
            $childUserProducts = UserProduct::query()
                ->where('user_id', $userProduct->user_id)
                ->where('source_product_id', $userProduct->product_id)
                ->active()
                ->with([
                    'product.files' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                ])
                ->latest('granted_at')
                ->get();
        }

        $placeholderTitle = null;
        $placeholderMessage = null;

        $event = null;
        $eventRegistration = null;

        if ($type === ProductType::Event->value) {
            $event = $product?->event;

            if ($event) {
                $eventRegistration = EventRegistration::query()
                    ->where('event_id', $event->id)
                    ->where('user_id', $userProduct->user_id)
                    ->active()
                    ->first();
            }
        }

        if ($type === ProductType::Course->value) {
            $placeholderTitle = 'Catatan';
            $placeholderMessage = 'Konten pembelajaran akan tersedia pada Sprint Course.';
        } elseif ($type === ProductType::Membership->value) {
            $placeholderTitle = 'Catatan';
            $placeholderMessage = 'Manajemen membership akan tersedia pada Sprint Membership.';
        }

        return [
            'userProduct' => $userProduct,
            'product' => $product,
            'type' => $type,
            'files' => $files,
            'childUserProducts' => $childUserProducts,
            'event' => $event,
            'eventRegistration' => $eventRegistration,
            'placeholderTitle' => $placeholderTitle,
            'placeholderMessage' => $placeholderMessage,
        ];
    }
}

