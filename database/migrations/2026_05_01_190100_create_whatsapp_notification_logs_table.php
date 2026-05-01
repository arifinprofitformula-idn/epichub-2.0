<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('dripsender');
            $table->string('event_type')->nullable()->index();
            $table->nullableMorphs('notifiable');
            $table->string('recipient_phone', 30)->index();
            $table->string('recipient_name')->nullable();
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->string('group_id')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};
