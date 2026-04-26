<?php

namespace App\Actions\Affiliates;

use App\Enums\AffiliateCommissionType;
use App\Enums\CommissionStatus;
use App\Enums\OrderStatus;
use App\Enums\ReferralOrderStatus;
use App\Models\Commission;
use App\Models\Order;
use Illuminate\Support\Collection;

class CreateCommissionsForOrderAction
{
    /**
     * @return Collection<int, Commission>
     */
    public function execute(Order $order): Collection
    {
        $order->loadMissing([
            'items.product',
            'referralOrder.epiChannel',
            'user.epiChannel',
            'referrerEpiChannel',
        ]);

        if (! in_array($order->status, [OrderStatus::Paid], true)) {
            return collect();
        }

        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            return collect();
        }

        $referralOrder = $order->referralOrder;

        if (! $referralOrder) {
            return collect();
        }

        if (in_array($referralOrder->status, [ReferralOrderStatus::Cancelled, ReferralOrderStatus::Refunded], true)) {
            return collect();
        }

        $channel = $referralOrder->epiChannel;

        if (! $channel) {
            return collect();
        }

        if (! $channel->isActive()) {
            return collect();
        }

        if ($channel->user_id === $order->user_id) {
            return collect();
        }

        if ($order->user?->epiChannel && $order->user->epiChannel->epic_code === $channel->epic_code) {
            return collect();
        }

        $results = collect();
        $converted = false;
        $isHouseChannel = $channel->isHouseChannel();

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            if (! $product->is_affiliate_enabled) {
                continue;
            }

            if (! $product->affiliate_commission_type || ! $product->affiliate_commission_value) {
                continue;
            }

            $type = $product->affiliate_commission_type instanceof AffiliateCommissionType
                ? $product->affiliate_commission_type
                : AffiliateCommissionType::tryFrom((string) $product->affiliate_commission_type);

            if (! $type) {
                continue;
            }

            $value = (float) $product->affiliate_commission_value;
            $base = (float) $item->subtotal_amount;

            $amount = $type === AffiliateCommissionType::Percentage
                ? round($base * ($value / 100), 2)
                : round($value * (int) $item->quantity, 2);

            if ($amount <= 0) {
                continue;
            }

            $converted = true;

            if ($isHouseChannel) {
                continue;
            }

            $commission = Commission::query()->firstOrCreate(
                [
                    'order_item_id' => $item->id,
                    'epi_channel_id' => $channel->id,
                ],
                [
                    'referral_order_id' => $referralOrder->id,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'buyer_user_id' => $order->user_id,
                    'commission_type' => $type,
                    'commission_value' => $value,
                    'base_amount' => $base,
                    'commission_amount' => $amount,
                    'status' => CommissionStatus::Pending,
                    'metadata' => [
                        'referral_source' => $order->referral_source,
                        'is_house_commission' => false,
                    ],
                ],
            );

            $results->push($commission);
        }

        if ($converted && $referralOrder->status === ReferralOrderStatus::Pending) {
            $referralOrder->update([
                'status' => ReferralOrderStatus::Converted,
                'metadata' => array_merge($referralOrder->metadata ?? [], [
                    'house_channel' => $isHouseChannel,
                    'commission_skipped_for_house' => $isHouseChannel,
                ]),
            ]);
        }

        return $results;
    }
}

