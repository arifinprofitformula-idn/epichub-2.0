<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailketing_subscriber_logs', function (Blueprint $table) {
            $table->id();
            $table->string('list_id')->nullable()->index();
            $table->string('list_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('status', 20)->index();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->string('event_type', 100)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailketing_subscriber_logs');
    }
};
