<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'legacy_source')) {
                $table->string('legacy_source', 50)->nullable()->after('legacy_epic_id');
            }

            if (! Schema::hasColumn('users', 'legacy_user_id')) {
                $table->string('legacy_user_id', 64)->nullable()->after('legacy_source');
            }

            if (! Schema::hasColumn('users', 'legacy_import_batch_id')) {
                $table->foreignId('legacy_import_batch_id')
                    ->nullable()
                    ->after('legacy_user_id')
                    ->constrained('legacy_v1_import_batches', indexName: 'users_legacy_batch_fk')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'legacy_imported_at')) {
                $table->timestamp('legacy_imported_at')->nullable()->after('legacy_import_batch_id');
            }
        });

        Schema::table('legacy_v1_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('legacy_v1_users', 'legacy_user_id')) {
                $table->string('legacy_user_id', 64)->nullable()->after('row_number');
            }

            if (! Schema::hasColumn('legacy_v1_users', 'source_type')) {
                $table->string('source_type', 30)->nullable()->after('legacy_user_id');
            }

            if (! Schema::hasColumn('legacy_v1_users', 'import_key')) {
                $table->string('import_key', 80)->nullable()->after('source_type');
                $table->unique('import_key', 'lv1_users_import_key_unique');
            }
        });

        Schema::table('legacy_v1_product_accesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('legacy_v1_product_accesses', 'legacy_access_id')) {
                $table->string('legacy_access_id', 64)->nullable()->after('legacy_v1_user_id');
            }

            if (! Schema::hasColumn('legacy_v1_product_accesses', 'source_type')) {
                $table->string('source_type', 30)->nullable()->after('legacy_access_id');
            }

            if (! Schema::hasColumn('legacy_v1_product_accesses', 'import_key')) {
                $table->string('import_key', 80)->nullable()->after('source_type');
                $table->unique('import_key', 'lv1_access_import_key_unique');
            }
        });

        $this->createTableIfMissing('legacy_v1_user_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('legacy_v1_import_batches', indexName: 'lv1_um_batch_fk')->cascadeOnDelete();
            $table->foreignId('legacy_v1_user_id')->nullable()->constrained('legacy_v1_users', indexName: 'lv1_um_legacy_user_fk')->nullOnDelete();
            $table->string('legacy_user_id', 64)->nullable();
            $table->string('legacy_epic_id', 50)->nullable();
            $table->string('legacy_email')->nullable();
            $table->string('legacy_whatsapp', 30)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', indexName: 'lv1_um_user_fk')->nullOnDelete();
            $table->string('match_method', 30)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['legacy_user_id']);
            $table->index(['legacy_epic_id']);
        });

        $this->createTableIfMissing('legacy_v1_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('legacy_v1_import_batches', indexName: 'lv1_orders_batch_fk')->cascadeOnDelete();
            $table->string('import_key', 80)->unique();
            $table->string('legacy_order_id', 64)->nullable();
            $table->string('legacy_order_number')->nullable();
            $table->string('legacy_user_id', 64)->nullable();
            $table->string('legacy_user_epic_id', 50)->nullable();
            $table->string('legacy_customer_name')->nullable();
            $table->string('legacy_customer_email')->nullable();
            $table->string('legacy_customer_whatsapp', 30)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', indexName: 'lv1_orders_user_fk')->nullOnDelete();
            $table->string('legacy_status', 50)->nullable();
            $table->string('normalized_status', 30)->default('unknown');
            $table->string('currency', 10)->nullable();
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('migration_status', 30)->default('pending');
            $table->text('source_note')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'normalized_status']);
            $table->index(['legacy_order_id']);
            $table->index(['legacy_user_epic_id']);
        });

        $this->createTableIfMissing('legacy_v1_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('legacy_v1_import_batches', indexName: 'lv1_pay_batch_fk')->cascadeOnDelete();
            $table->string('import_key', 80)->unique();
            $table->string('legacy_payment_id', 64)->nullable();
            $table->string('legacy_payment_number')->nullable();
            $table->string('legacy_order_id', 64)->nullable();
            $table->foreignId('legacy_v1_order_id')->nullable()->constrained('legacy_v1_orders', indexName: 'lv1_pay_order_fk')->nullOnDelete();
            $table->string('legacy_user_id', 64)->nullable();
            $table->string('legacy_user_epic_id', 50)->nullable();
            $table->string('legacy_user_email')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', indexName: 'lv1_pay_user_fk')->nullOnDelete();
            $table->string('legacy_status', 50)->nullable();
            $table->string('normalized_status', 30)->default('unknown');
            $table->string('payment_method', 50)->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('provider_reference')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 10)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('migration_status', 30)->default('pending');
            $table->text('source_note')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'normalized_status']);
            $table->index(['legacy_payment_id']);
            $table->index(['legacy_order_id']);
        });

        $this->createTableIfMissing('legacy_v1_payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('legacy_v1_import_batches', indexName: 'lv1_payout_batch_fk')->cascadeOnDelete();
            $table->string('import_key', 80)->unique();
            $table->string('legacy_payout_id', 64)->nullable();
            $table->string('legacy_user_id', 64)->nullable();
            $table->string('legacy_user_epic_id', 50)->nullable();
            $table->string('legacy_user_email')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users', indexName: 'lv1_payout_user_fk')->nullOnDelete();
            $table->foreignId('epi_channel_id')->nullable()->constrained('epi_channels', indexName: 'lv1_payout_channel_fk')->nullOnDelete();
            $table->string('legacy_status', 50)->nullable();
            $table->string('normalized_status', 30)->default('unknown');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('migration_status', 30)->default('pending');
            $table->text('source_note')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'normalized_status']);
            $table->index(['legacy_payout_id']);
            $table->index(['legacy_user_epic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_v1_payouts');
        Schema::dropIfExists('legacy_v1_payments');
        Schema::dropIfExists('legacy_v1_orders');
        Schema::dropIfExists('legacy_v1_user_mappings');

        Schema::table('legacy_v1_product_accesses', function (Blueprint $table): void {
            if (Schema::hasColumn('legacy_v1_product_accesses', 'import_key')) {
                $table->dropUnique('lv1_access_import_key_unique');
                $table->dropColumn('import_key');
            }

            if (Schema::hasColumn('legacy_v1_product_accesses', 'source_type')) {
                $table->dropColumn('source_type');
            }

            if (Schema::hasColumn('legacy_v1_product_accesses', 'legacy_access_id')) {
                $table->dropColumn('legacy_access_id');
            }
        });

        Schema::table('legacy_v1_users', function (Blueprint $table): void {
            if (Schema::hasColumn('legacy_v1_users', 'import_key')) {
                $table->dropUnique('lv1_users_import_key_unique');
                $table->dropColumn('import_key');
            }

            if (Schema::hasColumn('legacy_v1_users', 'source_type')) {
                $table->dropColumn('source_type');
            }

            if (Schema::hasColumn('legacy_v1_users', 'legacy_user_id')) {
                $table->dropColumn('legacy_user_id');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'legacy_imported_at')) {
                $table->dropColumn('legacy_imported_at');
            }

            if (Schema::hasColumn('users', 'legacy_import_batch_id')) {
                $table->dropForeign('users_legacy_batch_fk');
                $table->dropColumn('legacy_import_batch_id');
            }

            if (Schema::hasColumn('users', 'legacy_user_id')) {
                $table->dropColumn('legacy_user_id');
            }

            if (Schema::hasColumn('users', 'legacy_source')) {
                $table->dropColumn('legacy_source');
            }
        });
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
