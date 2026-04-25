<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete()->unique();
            $table->foreignId('epi_channel_id')->constrained('epi_channels')->cascadeOnDelete();
            $table->foreignId('referral_visit_id')->nullable()->constrained('referral_visits')->nullOnDelete();
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 20)->default('pending');
            $table->timestamp('attributed_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['epi_channel_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_orders');
    }
};

