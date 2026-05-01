<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('epi_channels', function (Blueprint $table): void {
            if (! Schema::hasColumn('epi_channels', 'payout_bank_name')) {
                $table->string('payout_bank_name')->nullable()->after('sponsor_name');
            }

            if (! Schema::hasColumn('epi_channels', 'payout_bank_account_number')) {
                $table->string('payout_bank_account_number', 50)->nullable()->after('payout_bank_name');
            }

            if (! Schema::hasColumn('epi_channels', 'payout_bank_account_holder_name')) {
                $table->string('payout_bank_account_holder_name')->nullable()->after('payout_bank_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('epi_channels', function (Blueprint $table): void {
            $columns = [
                'payout_bank_name',
                'payout_bank_account_number',
                'payout_bank_account_holder_name',
            ];

            $existingColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('epi_channels', $column),
            ));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
