<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epi_channel_id')->constrained('epi_channels')->cascadeOnDelete();
            $table->foreignId('referral_order_id')->nullable()->constrained('referral_orders')->nullOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('commission_type', 20);
            $table->decimal('commission_value', 15, 2)->default(0);
            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);

            $table->string('status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->foreignId('commission_payout_id')->nullable()->constrained('commission_payouts')->nullOnDelete();

            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['order_item_id', 'epi_channel_id']);
            $table->index(['epi_channel_id', 'status']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};

