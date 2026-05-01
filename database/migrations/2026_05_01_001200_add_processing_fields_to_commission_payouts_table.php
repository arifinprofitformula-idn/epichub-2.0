<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_payouts', function (Blueprint $table): void {
            if (! Schema::hasColumn('commission_payouts', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('commission_payouts', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }

            if (! Schema::hasColumn('commission_payouts', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('commission_payouts', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_payouts', function (Blueprint $table): void {
            if (Schema::hasColumn('commission_payouts', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('commission_payouts', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('commission_payouts', 'processed_by')) {
                $table->dropConstrainedForeignId('processed_by');
            }

            if (Schema::hasColumn('commission_payouts', 'processed_at')) {
                $table->dropColumn('processed_at');
            }
        });
    }
};
