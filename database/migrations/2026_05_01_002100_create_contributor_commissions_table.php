<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributor_commissions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('contributor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('commission_type');
            $table->decimal('commission_value', 15, 2);
            $table->decimal('base_amount', 15, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->string('commission_base')->default('gross');

            $table->string('status')->default('approved');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('contributor_payout_id')->nullable()->index();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Satu komisi per order_item per kontributor — mencegah duplikasi
            $table->unique(['order_item_id', 'contributor_user_id'], 'uq_contributor_commission_item_user');

            $table->index(['contributor_user_id', 'status'], 'idx_contributor_user_status');
            $table->index(['order_id'], 'idx_contributor_order');
            $table->index(['status', 'created_at'], 'idx_contributor_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributor_commissions');
    }
};
