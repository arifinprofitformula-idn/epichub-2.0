<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1CommissionImportBatch;

class GenerateLegacyV1CommissionReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1CommissionImportBatch $batch, bool $persist = false): array
    {
        $batch->loadMissing(['commissions', 'errors']);
        $commissions = $batch->commissions;

        $summary = [
            'batch_id' => $batch->id,
            'batch_uuid' => $batch->uuid,
            'status' => $batch->status,
            'total_rows' => $commissions->count(),
            'imported_count' => $commissions->count(),
            'success_count' => $commissions->reject(fn ($commission) => $commission->migration_status?->value === 'error')->count(),
            'failed_count' => $commissions->filter(fn ($commission) => $commission->migration_status?->value === 'error')->count(),
            'resolved_count' => $commissions->filter(fn ($commission) => $commission->migration_status?->value === 'resolved')->count(),
            'unresolved_count' => $commissions->filter(fn ($commission) => in_array($commission->migration_status?->value, ['unresolved_user', 'unresolved_product'], true))->count(),
            'unresolved_user_count' => $commissions->filter(fn ($commission) => $commission->migration_status?->value === 'unresolved_user')->count(),
            'unresolved_product_count' => $commissions->filter(fn ($commission) => $commission->migration_status?->value === 'unresolved_product')->count(),
            'unknown_status_count' => $commissions->filter(fn ($commission) => $commission->commission_status?->value === 'unknown')->count(),
            'payable_count' => $commissions->where('is_payable', true)->count(),
            'paid_count' => $commissions->filter(fn ($commission) => $commission->commission_status?->value === 'paid')->count(),
            'approved_count' => $commissions->filter(fn ($commission) => $commission->commission_status?->value === 'approved')->count(),
            'pending_count' => $commissions->filter(fn ($commission) => $commission->commission_status?->value === 'pending')->count(),
            'total_amount' => (float) $commissions->sum('commission_amount'),
            'paid_amount' => (float) $commissions->filter(fn ($commission) => $commission->commission_status?->value === 'paid')->sum('commission_amount'),
            'unpaid_amount' => (float) $commissions->reject(fn ($commission) => in_array($commission->commission_status?->value, ['paid', 'rejected', 'cancelled'], true))->sum('commission_amount'),
            'error_count' => $batch->errors->where('severity', 'error')->count(),
            'warning_count' => $batch->errors->where('severity', 'warning')->count(),
            'conflict_count' => $batch->errors->where('severity', 'conflict')->count(),
            'generated_at' => now()->toIso8601String(),
        ];

        if ($persist) {
            $batch->forceFill(['summary' => $summary])->save();
        }

        return $summary;
    }
}
