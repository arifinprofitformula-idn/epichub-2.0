<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key');
            $table->string('event_label');
            $table->string('target_key');
            $table->string('target_label');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('whatsapp_enabled')->default(true);
            $table->text('email_subject')->nullable();
            $table->longText('email_body')->nullable();
            $table->longText('whatsapp_body')->nullable();
            $table->json('available_shortcodes')->nullable();
            $table->text('default_email_subject')->nullable();
            $table->longText('default_email_body')->nullable();
            $table->longText('default_whatsapp_body')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['event_key', 'target_key']);
            $table->index('event_key');
            $table->index('target_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
