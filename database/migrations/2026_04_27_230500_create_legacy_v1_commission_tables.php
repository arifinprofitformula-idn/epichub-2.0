<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('legacy_v1_commission_import_batches', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name')->nullable();
                $table->string('status', 30)->default('pending');
                $table->string('file_name')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_hash', 64)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['status']);
        });

        $this->createTableIfMissing('legacy_v1_commissions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('import_batch_id')->constrained('legacy_v1_commission_import_batches', indexName: 'lv1_comm_import_batch_fk')->cascadeOnDelete();
                $table->string('import_key')->unique();
                $table->unsignedInteger('row_number')->nullable();
                $table->string('legacy_commission_id')->nullable();
                $table->string('legacy_user_epic_id', 50)->nullable();
                $table->string('legacy_user_name')->nullable();
                $table->string('legacy_user_email')->nullable();
                $table->string('legacy_user_whatsapp', 30)->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users', indexName: 'lv1_comm_user_fk')->nullOnDelete();
                $table->foreignId('epi_channel_id')->nullable()->constrained('epi_channels', indexName: 'lv1_comm_channel_fk')->nullOnDelete();
                $table->string('legacy_sponsor_epic_id', 50)->nullable();
                $table->string('legacy_downline_epic_id', 50)->nullable();
                $table->string('legacy_downline_name')->nullable();
                $table->string('legacy_order_id')->nullable();
                $table->string('legacy_product_code')->nullable();
                $table->string('legacy_product_name')->nullable();
                $table->foreignId('product_id')->nullable()->constrained('products', indexName: 'lv1_comm_product_fk')->nullOnDelete();
                $table->string('commission_type')->nullable();
                $table->string('commission_level', 30)->nullable();
                $table->decimal('commission_amount', 15, 2);
                $table->string('commission_status', 20)->default('unknown');
                $table->timestamp('earned_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedTinyInteger('legacy_period_month')->nullable();
                $table->unsignedSmallInteger('legacy_period_year')->nullable();
                $table->boolean('is_payable')->default(false);
                $table->foreignId('payout_id')->nullable()->constrained('commission_payouts', indexName: 'lv1_comm_payout_fk')->nullOnDelete();
                $table->text('source_note')->nullable();
                $table->json('raw_payload')->nullable();
                $table->string('migration_status', 30)->default('pending');
                $table->timestamps();

                $table->index(['legacy_commission_id']);
                $table->index(['legacy_user_epic_id']);
                $table->index(['legacy_user_email']);
                $table->index(['commission_status']);
                $table->index(['migration_status']);
                $table->index(['legacy_period_year', 'legacy_period_month']);
        });

        $this->createTableIfMissing('legacy_v1_commission_import_errors', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('import_batch_id')->constrained('legacy_v1_commission_import_batches', indexName: 'lv1_comm_err_batch_fk')->cascadeOnDelete();
                $table->foreignId('legacy_v1_commission_id')->nullable()->constrained('legacy_v1_commissions', indexName: 'lv1_comm_err_comm_fk')->nullOnDelete();
                $table->string('scope', 30);
                $table->string('severity', 20)->default('error');
                $table->string('code', 50);
                $table->text('message');
                $table->json('context')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users', indexName: 'lv1_comm_err_resolved_fk')->nullOnDelete();
                $table->timestamps();

                $table->index(['import_batch_id', 'scope', 'severity']);
                $table->index(['import_batch_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_v1_commission_import_errors');
        Schema::dropIfExists('legacy_v1_commissions');
        Schema::dropIfExists('legacy_v1_commission_import_batches');
    }

    protected function createTableIfMissing(string $table, \Closure $callback): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::create($table, $callback);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateTableError($exception)) {
                throw $exception;
            }
        }
    }

    protected function isDuplicateTableError(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        return $sqlState === '42S01'
            || $driverCode === 1050
            || str_contains($message, 'already exists');
    }
};
