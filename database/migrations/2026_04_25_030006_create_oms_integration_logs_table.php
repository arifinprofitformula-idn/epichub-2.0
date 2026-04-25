<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oms_integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 20);
            $table->string('action', 50);
            $table->string('request_id')->nullable()->unique();
            $table->string('epic_code')->nullable();
            $table->string('email')->nullable();
            $table->string('status', 20);
            $table->string('response_code', 10)->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['direction', 'action']);
            $table->index(['status']);
            $table->index(['epic_code']);
            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oms_integration_logs');
    }
};

