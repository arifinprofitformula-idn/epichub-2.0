<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('legacy_v1_import_batches', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name')->nullable();
                $table->string('source_type', 30);
                $table->string('status', 30)->default('pending');
                $table->string('file_name')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_hash', 64)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('rolled_back_at')->nullable();
                $table->json('summary')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['source_type', 'status']);
        });

        $this->createTableIfMissing('legacy_v1_product_mappings', function (Blueprint $table): void {
                $table->id();
                $table->string('legacy_product_key')->unique();
                $table->string('legacy_product_name')->nullable();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->foreignId('mapped_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('mapped_at')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['is_active']);
        });

        $this->createTableIfMissing('legacy_v1_users', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('batch_id')->constrained('legacy_v1_import_batches')->cascadeOnDelete();
                $table->unsignedInteger('row_number');
                $table->string('status', 30)->default('pending');
                $table->string('match_status', 30)->default('pending');
                $table->string('sponsor_status', 30)->default('pending');

                $table->string('raw_name')->nullable();
                $table->string('raw_epic_id', 50)->nullable();
                $table->string('raw_email')->nullable();
                $table->string('raw_whatsapp', 50)->nullable();
                $table->string('raw_sponsor_epic_id', 50)->nullable();
                $table->string('raw_city')->nullable();

                $table->string('normalized_name')->nullable();
                $table->string('normalized_epic_id', 50)->nullable();
                $table->string('normalized_email')->nullable();
                $table->string('normalized_whatsapp', 30)->nullable();
                $table->string('normalized_sponsor_epic_id', 50)->nullable();
                $table->string('normalized_city')->nullable();

                $table->foreignId('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('matched_by', 30)->nullable();
                $table->foreignId('imported_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('epi_channel_id')->nullable()->constrained('epi_channels')->nullOnDelete();
                $table->timestamp('imported_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'row_number']);
                $table->index(['normalized_epic_id']);
                $table->index(['normalized_email']);
                $table->index(['normalized_whatsapp']);
                $table->index(['status', 'match_status', 'sponsor_status']);
        });

        $this->createTableIfMissing('legacy_v1_product_accesses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('batch_id')->constrained('legacy_v1_import_batches')->cascadeOnDelete();
                $table->foreignId('legacy_v1_user_id')->nullable()->constrained('legacy_v1_users')->nullOnDelete();
                $table->unsignedInteger('row_number');
                $table->string('status', 30)->default('pending');

                $table->string('raw_identifier_type', 30)->nullable();
                $table->string('raw_identifier_value')->nullable();
                $table->string('raw_legacy_product_key')->nullable();
                $table->string('raw_legacy_product_name')->nullable();
                $table->string('raw_granted_at')->nullable();

                $table->string('normalized_email')->nullable();
                $table->string('normalized_epic_id', 50)->nullable();
                $table->string('normalized_whatsapp', 30)->nullable();
                $table->string('normalized_legacy_product_key')->nullable();

                $table->foreignId('matched_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('matched_by', 30)->nullable();
                $table->foreignId('product_mapping_id')->nullable()->constrained('legacy_v1_product_mappings')->nullOnDelete();
                $table->foreignId('mapped_product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignId('granted_user_product_id')->nullable()->constrained('user_products')->nullOnDelete();
                $table->timestamp('granted_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'row_number']);
                $table->index(['normalized_epic_id']);
                $table->index(['normalized_email']);
                $table->index(['normalized_legacy_product_key']);
                $table->index(['status']);
        });

        $this->createTableIfMissing('legacy_v1_sponsor_links', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('batch_id')->constrained('legacy_v1_import_batches')->cascadeOnDelete();
                $table->foreignId('legacy_v1_user_id')->constrained('legacy_v1_users')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('sponsor_legacy_epic_id', 50)->nullable();
                $table->foreignId('previous_referrer_epi_channel_id')->nullable()->constrained('epi_channels')->nullOnDelete();
                $table->foreignId('resolved_sponsor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('resolved_referrer_epi_channel_id')->nullable()->constrained('epi_channels')->nullOnDelete();
                $table->string('resolution_status', 30)->default('pending');
                $table->boolean('forced')->default(false);
                $table->text('resolution_reason')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['legacy_v1_user_id']);
                $table->index(['batch_id', 'resolution_status']);
        });

        $this->createTableIfMissing('legacy_v1_import_errors', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('batch_id')->constrained('legacy_v1_import_batches')->cascadeOnDelete();
                $table->foreignId('legacy_v1_user_id')->nullable()->constrained('legacy_v1_users')->nullOnDelete();
                $table->foreignId('legacy_v1_product_access_id')->nullable()->constrained('legacy_v1_product_accesses')->nullOnDelete();
                $table->string('scope', 30);
                $table->string('severity', 20)->default('error');
                $table->string('code', 50);
                $table->text('message');
                $table->json('context')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['batch_id', 'scope', 'severity']);
                $table->index(['batch_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_v1_import_errors');
        Schema::dropIfExists('legacy_v1_sponsor_links');
        Schema::dropIfExists('legacy_v1_product_accesses');
        Schema::dropIfExists('legacy_v1_users');
        Schema::dropIfExists('legacy_v1_product_mappings');
        Schema::dropIfExists('legacy_v1_import_batches');
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
