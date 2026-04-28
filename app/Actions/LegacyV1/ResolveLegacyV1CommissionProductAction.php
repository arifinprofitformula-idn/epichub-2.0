<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1Commission;
use App\Models\LegacyV1ProductMapping;
use App\Models\Product;

class ResolveLegacyV1CommissionProductAction
{
    public function execute(LegacyV1Commission $commission): ?Product
    {
        if ($commission->legacy_product_code) {
            $mapping = LegacyV1ProductMapping::query()
                ->where('legacy_product_key', $commission->legacy_product_code)
                ->where('is_active', true)
                ->first();

            if ($mapping?->product) {
                return $mapping->product;
            }

            $product = Product::query()->where('slug', $commission->legacy_product_code)->first();

            if ($product) {
                return $product;
            }
        }

        if ($commission->legacy_product_name) {
            return Product::query()
                ->whereRaw('LOWER(title) = ?', [strtolower($commission->legacy_product_name)])
                ->first();
        }

        return null;
    }
}
