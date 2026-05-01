<?php

namespace App\Actions\Contributors;

use App\Enums\ContributorCommissionBase;
use App\Enums\ContributorCommissionStatus;
use App\Enums\ContributorCommissionType;
use App\Enums\OrderStatus;
use App\Models\ContributorCommission;
use App\Models\Order;
use Illuminate\Support\Collection;

class CreateContributorCommissionsForOrderAction
{
    /**
     * @return Collection<int, ContributorCommission>
     */
    public function execute(Order $order): Collection
    {
        $order->loadMissing(['items.product']);

        if ($order->status !== OrderStatus::Paid) {
            return collect();
        }

        $results = collect();

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            if (! $product->is_contributor_commission_enabled) {
                continue;
            }

            if (! $product->contributor_user_id) {
                continue;
            }

            // Jangan buat komisi jika kontributor adalah pembeli itu sendiri
            if ($product->contributor_user_id === $order->user_id) {
                continue;
            }

            $type = $product->contributor_commission_type instanceof ContributorCommissionType
                ? $product->contributor_commission_type
                : ContributorCommissionType::tryFrom((string) $product->contributor_commission_type);

            if (! $type) {
                continue;
            }

            $value = (float) $product->contributor_commission_value;

            if ($value <= 0) {
                continue;
            }

            $base = $this->resolveBaseAmount($product, $item, $order);

            if ($base <= 0) {
                continue;
            }

            $amount = $type === ContributorCommissionType::Percent
                ? round($base * ($value / 100), 2)
                : round($value, 2);

            // Cap: komisi tidak boleh melebihi base_amount
            $amount = min($amount, $base);

            if ($amount <= 0) {
                continue;
            }

            $commissionBase = $product->contributor_commission_base instanceof ContributorCommissionBase
                ? $product->contributor_commission_base
                : ContributorCommissionBase::tryFrom((string) ($product->contributor_commission_base ?? 'gross'))
                    ?? ContributorCommissionBase::Gross;

            // firstOrCreate memastikan idempotent — tidak ada duplikasi
            $commission = ContributorCommission::query()->firstOrCreate(
                [
                    'order_item_id' => $item->id,
                    'contributor_user_id' => $product->contributor_user_id,
                ],
                [
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'buyer_user_id' => $order->user_id,
                    'commission_type' => $type,
                    'commission_value' => $value,
                    'base_amount' => $base,
                    'commission_amount' => $amount,
                    'commission_base' => $commissionBase,
                    'status' => ContributorCommissionStatus::Approved,
                    'approved_at' => now(),
                    'metadata' => [
                        'product_title' => $product->title,
                        'order_number' => $order->order_number ?? $order->id,
                        'item_subtotal' => (float) $item->subtotal_amount,
                        'commission_base_used' => $commissionBase->value,
                        'calculated_at' => now()->toIso8601String(),
                    ],
                ],
            );

            $results->push($commission);
        }

        return $results;
    }

    private function resolveBaseAmount(\App\Models\Product $product, \App\Models\OrderItem $item, Order $order): float
    {
        $base = $product->contributor_commission_base instanceof ContributorCommissionBase
            ? $product->contributor_commission_base
            : ContributorCommissionBase::tryFrom((string) ($product->contributor_commission_base ?? 'gross'))
                ?? ContributorCommissionBase::Gross;

        return match ($base) {
            ContributorCommissionBase::Gross => (float) $item->subtotal_amount,
            // Untuk tahap awal, net_after_discount dan net_after_affiliate juga menggunakan subtotal
            // karena data diskon per item belum tersedia. Dapat dikembangkan sesuai kebutuhan.
            ContributorCommissionBase::NetAfterDiscount => (float) $item->subtotal_amount,
            ContributorCommissionBase::NetAfterAffiliate => (float) $item->subtotal_amount,
        };
    }
}
