<?php

namespace App\Actions\LegacyV1;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class NormalizeLegacyV1CommissionAction
{
    public function __construct(
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function execute(array $row): array
    {
        $legacyCommissionId = $this->nullableString($row['legacy_commission_id'] ?? null);
        $legacyUserEpicId = $this->normalizeEpicId($row['user_epic_id'] ?? null);
        $legacyUserEmail = $this->normalizeEmail($row['user_email'] ?? null);
        $legacyUserWhatsapp = $this->normalizeWhatsappNumber->execute($this->nullableString($row['user_whatsapp'] ?? null));
        $legacyProductCode = $this->normalizeKey($row['product_code'] ?? null);
        $legacyProductName = $this->nullableString($row['product_name'] ?? null);
        $commissionType = $this->normalizeKey($row['commission_type'] ?? null);
        $commissionAmount = round((float) str_replace([','], [''], (string) ($row['commission_amount'] ?? 0)), 2);
        $earnedAt = $this->parseDate($row['earned_at'] ?? null);
        $approvedAt = $this->parseDate($row['approved_at'] ?? null);
        $paidAt = $this->parseDate($row['paid_at'] ?? null);
        $commissionStatus = $this->mapStatus($row['commission_status'] ?? null);
        $migrationStatus = $commissionStatus === LegacyV1CommissionStatus::Unknown
            ? LegacyV1CommissionMigrationStatus::UnknownStatus
            : LegacyV1CommissionMigrationStatus::Pending;

        $importKey = $legacyCommissionId ?: sha1(implode('|', [
            $legacyUserEpicId ?? '',
            $legacyUserEmail ?? '',
            $legacyProductCode ?? Str::lower((string) $legacyProductName),
            number_format($commissionAmount, 2, '.', ''),
            $earnedAt?->toIso8601String() ?? '',
            $commissionType ?? '',
        ]));

        return [
            'import_key' => $importKey,
            'legacy_commission_id' => $legacyCommissionId,
            'legacy_user_epic_id' => $legacyUserEpicId,
            'legacy_user_name' => $this->nullableString($row['user_name'] ?? null),
            'legacy_user_email' => $legacyUserEmail,
            'legacy_user_whatsapp' => $legacyUserWhatsapp,
            'legacy_sponsor_epic_id' => $this->normalizeEpicId($row['sponsor_epic_id'] ?? null),
            'legacy_downline_epic_id' => $this->normalizeEpicId($row['downline_epic_id'] ?? null),
            'legacy_downline_name' => $this->nullableString($row['downline_name'] ?? null),
            'legacy_order_id' => $this->nullableString($row['legacy_order_id'] ?? null),
            'legacy_product_code' => $legacyProductCode,
            'legacy_product_name' => $legacyProductName,
            'commission_type' => $commissionType,
            'commission_level' => $this->nullableString($row['commission_level'] ?? null),
            'commission_amount' => $commissionAmount,
            'commission_status' => $commissionStatus,
            'earned_at' => $earnedAt,
            'approved_at' => $approvedAt,
            'paid_at' => $paidAt,
            'legacy_period_month' => $this->nullableInt($row['period_month'] ?? null),
            'legacy_period_year' => $this->nullableInt($row['period_year'] ?? null),
            'source_note' => $this->nullableString($row['note'] ?? null),
            'migration_status' => $migrationStatus,
            'raw_status' => $this->nullableString($row['commission_status'] ?? null),
        ];
    }

    protected function mapStatus(mixed $value): LegacyV1CommissionStatus
    {
        $normalized = Str::lower(trim((string) $value));

        return match ($normalized) {
            'pending', 'wait', 'waiting' => LegacyV1CommissionStatus::Pending,
            'approved', 'approve', 'valid' => LegacyV1CommissionStatus::Approved,
            'paid', 'settled', 'done' => LegacyV1CommissionStatus::Paid,
            'rejected', 'reject', 'failed' => LegacyV1CommissionStatus::Rejected,
            'cancelled', 'canceled', 'void' => LegacyV1CommissionStatus::Cancelled,
            default => LegacyV1CommissionStatus::Unknown,
        };
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected function normalizeEpicId(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        return $value !== null ? Str::upper($value) : null;
    }

    protected function normalizeEmail(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        return $value !== null ? Str::lower($value) : null;
    }

    protected function normalizeKey(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        return $value !== null ? Str::lower($value) : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function nullableInt(mixed $value): ?int
    {
        $string = trim((string) $value);

        if ($string === '' || ! is_numeric($string)) {
            return null;
        }

        return (int) $string;
    }
}
