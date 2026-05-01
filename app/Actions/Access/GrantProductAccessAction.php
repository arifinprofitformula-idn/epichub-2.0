<?php

namespace App\Actions\Access;

use App\Actions\Event\RegisterUserForEventAction;
use App\Enums\AccessLogAction;
use App\Enums\ProductType;
use App\Enums\UserProductStatus;
use App\Models\AccessLog;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GrantProductAccessAction
{
    public function __construct(
        protected RegisterUserForEventAction $registerUserForEvent,
    ) {}

    public function execute(
        User $user,
        Product $product,
        ?Order $order = null,
        ?OrderItem $orderItem = null,
        ?Product $sourceProduct = null,
        ?User $actor = null,
        AccessLogAction $logAction = AccessLogAction::ManualGrant,
        array $metadata = [],
        ?Carbon $grantedAt = null,
    ): UserProduct {
        if ($order === null) {
            $existingManual = UserProduct::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->whereNull('order_id')
                ->active()
                ->first();

            if ($existingManual) {
                $this->maybeRegisterEvent($user, $product, $existingManual, $sourceProduct, $actor);

                return $existingManual;
            }
        }

        $now = $grantedAt ?? now();

        $userProduct = UserProduct::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $order?->id,
            ],
            [
                'order_item_id' => $orderItem?->id,
                'source_product_id' => $sourceProduct?->id,
                'access_type' => $this->accessTypeValue($product),
                'status' => UserProductStatus::Active,
                'granted_by' => $actor?->id,
                'granted_at' => $now,
                'metadata' => $metadata,
            ],
        );

        if ($userProduct->status !== UserProductStatus::Active || $userProduct->isExpired() || $userProduct->revoked_at !== null) {
            $userProduct->update([
                'status' => UserProductStatus::Active,
                'expires_at' => null,
                'revoked_by' => null,
                'revoked_at' => null,
                'revoke_reason' => null,
                'granted_by' => $actor?->id,
                'granted_at' => $now,
            ]);
        }

        $this->log(
            action: $logAction,
            user: $user,
            product: $product,
            userProduct: $userProduct,
            order: $order,
            actor: $actor,
            metadata: $metadata,
            createdAt: $now,
        );

        $this->maybeRegisterEvent($user, $product, $userProduct, $sourceProduct, $actor, $order);

        // Kirim email access_granted hanya untuk manual grant (bukan dari order payment — itu sudah ditangani oleh payment_approved)
        if ($order === null && $logAction === AccessLogAction::ManualGrant) {
            $this->sendAccessGrantedEmail($user, $product, $userProduct);
        }

        return $userProduct->refresh();
    }

    protected function log(
        AccessLogAction $action,
        User $user,
        Product $product,
        UserProduct $userProduct,
        ?Order $order,
        ?User $actor,
        array $metadata,
        \DateTimeInterface $createdAt,
    ): void {
        AccessLog::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'user_product_id' => $userProduct->id,
            'order_id' => $order?->id,
            'action' => $action,
            'actor_id' => $actor?->id,
            'metadata' => $metadata,
            'created_at' => $createdAt,
        ]);
    }

    protected function accessTypeValue(Product $product): ?string
    {
        $accessType = $product->access_type;

        if ($accessType instanceof \BackedEnum) {
            return $accessType->value;
        }

        $value = (string) $accessType;

        return $value !== '' ? $value : null;
    }

    protected function sendAccessGrantedEmail(User $user, Product $product, UserProduct $userProduct): void
    {
        try {
            $type = $product->product_type instanceof ProductType
                ? $product->product_type
                : ProductType::tryFrom((string) $product->product_type);

            $typeLabel = match ($type) {
                ProductType::Course  => 'Kelas Online',
                ProductType::Event   => 'Event',
                ProductType::Bundle  => 'Bundle',
                ProductType::Digital => 'Produk Digital',
                default              => 'Produk',
            };

            $accessUrl = match ($type) {
                ProductType::Course => url('/kelas-saya'),
                ProductType::Event  => url('/event-saya'),
                default             => url('/produk-saya'),
            };

            app(EmailNotificationService::class)->sendTransactionalEmail(
                recipient: ['email' => $user->email, 'name' => $user->name],
                subject: 'Akses Produk Anda Sudah Aktif',
                view: 'emails.products.access-granted',
                data: [
                    'userName'      => $user->name,
                    'productName'   => $product->name,
                    'productType'   => $typeLabel,
                    'accessUrl'     => $accessUrl,
                    'myProductsUrl' => url('/produk-saya'),
                ],
                eventType: 'access_granted',
                metadata: ['notifiable' => $userProduct],
            );

            app(WhatsAppNotificationService::class)->sendToUser(
                user: $user,
                message: app(WhatsAppMessageTemplateService::class)->render('access_granted', [
                    'product_name' => $product->name,
                    'produk_saya_url' => url('/produk-saya'),
                ]),
                eventType: 'access_granted',
                metadata: ['notifiable' => $userProduct],
            );
        } catch (\Throwable $e) {
            Log::error('GrantProductAccessAction: gagal kirim access granted notification', ['error' => $e->getMessage()]);
        }
    }

    protected function maybeRegisterEvent(
        User $user,
        Product $product,
        UserProduct $userProduct,
        ?Product $sourceProduct,
        ?User $actor,
        ?Order $order = null,
    ): void {
        if ($order !== null) {
            return;
        }

        $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

        if ($type !== ProductType::Event->value) {
            return;
        }

        $event = Event::query()->where('product_id', $product->id)->first();

        if (! $event) {
            return;
        }

        $this->registerUserForEvent->execute(
            user: $user,
            event: $event,
            userProduct: $userProduct,
            order: null,
            orderItem: null,
            sourceProduct: $sourceProduct,
            actor: $actor,
        );
    }
}
