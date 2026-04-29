<?php

namespace App\Console\Commands;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1CommissionImportBatch;
use App\Models\User;
use Illuminate\Console\Command;
use RuntimeException;

abstract class LegacyV1Command extends Command
{
    protected function resolveOptionalBatch(?string $batchInput): ?LegacyV1ImportBatch
    {
        $batchInput = trim((string) $batchInput);

        if ($batchInput === '') {
            return null;
        }

        return $this->resolveBatch($batchInput);
    }

    protected function resolveBatch(string $batchInput): LegacyV1ImportBatch
    {
        $batch = LegacyV1ImportBatch::query()
            ->when(
                ctype_digit($batchInput),
                fn ($query) => $query->whereKey((int) $batchInput),
                fn ($query) => $query->where('uuid', $batchInput),
            )
            ->first();

        if (! $batch) {
            throw new RuntimeException('Batch legacy tidak ditemukan.');
        }

        return $batch;
    }

    protected function latestBatch(?string $sourceType = null): ?LegacyV1ImportBatch
    {
        return LegacyV1ImportBatch::query()
            ->when($sourceType !== null, fn ($query) => $query->where('source_type', $sourceType))
            ->latest('id')
            ->first();
    }

    protected function resolveCommissionBatch(string $batchInput): LegacyV1CommissionImportBatch
    {
        $batch = LegacyV1CommissionImportBatch::query()
            ->when(
                ctype_digit($batchInput),
                fn ($query) => $query->whereKey((int) $batchInput),
                fn ($query) => $query->where('uuid', $batchInput),
            )
            ->first();

        if (! $batch) {
            throw new RuntimeException('Batch legacy commission tidak ditemukan.');
        }

        return $batch;
    }

    protected function resolveActor(?string $email): ?User
    {
        $email = trim((string) $email);

        if ($email === '') {
            return null;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        if (! $user) {
            throw new RuntimeException('Actor email tidak ditemukan.');
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function renderSummary(array $summary): void
    {
        $rows = [];

        foreach ($summary as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $rows[] = [$key, (string) $value];
            }
        }

        $this->table(['Metric', 'Value'], $rows);
    }
}
