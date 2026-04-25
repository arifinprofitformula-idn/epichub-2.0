<?php

namespace App\Actions\Affiliates;

use App\Enums\AffiliateCommissionType;
use App\Enums\CommissionStatus;
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
        ]);

        $referralOrder = $order->referralOrder;

        if (! $referralOrder) {
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
                ],
            );

            $results->push($commission);
        }

        if ($results->count() > 0 && $referralOrder->status === ReferralOrderStatus::Pending) {
            $referralOrder->update([
                'status' => ReferralOrderStatus::Converted,
            ]);
        }

        return $results;
    }
}

