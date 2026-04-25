<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epi_channel_id')->constrained('epi_channels')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('referral_code');
            $table->text('landing_url')->nullable();
            $table->text('source_url')->nullable();
            $table->string('visitor_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['epi_channel_id']);
            $table->index(['referral_code']);
            $table->index(['clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_visits');
    }
};

