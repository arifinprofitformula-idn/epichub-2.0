<?php

namespace App\Actions\LegacyV1;

use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Models\LegacyV1Commission;

class MarkLegacyV1CommissionAsPayableAction
{
    public function execute(LegacyV1Commission $commission, bool $isPayable = true): LegacyV1Commission
    {
        $migrationStatus = $isPayable
            ? LegacyV1CommissionMigrationStatus::Payable
            : $this->resolveBaseMigrationStatus($commission);

        $commission->forceFill([
            'is_payable' => $isPayable,
            'migration_status' => $migrationStatus,
        ])->save();

        return $commission->fresh();
    }

    protected function resolveBaseMigrationStatus(LegacyV1Commission $commission): LegacyV1CommissionMigrationStatus
    {
        if ($commission->user_id === null) {
            return LegacyV1CommissionMigrationStatus::UnresolvedUser;
        }

        if ($commission->commission_status?->value === 'unknown') {
            return LegacyV1CommissionMigrationStatus::UnknownStatus;
        }

        if ($commission->product_id === null && ($commission->legacy_product_code !== null || $commission->legacy_product_name !== null)) {
            return LegacyV1CommissionMigrationStatus::UnresolvedProduct;
        }

        return LegacyV1CommissionMigrationStatus::Resolved;
    }
}
