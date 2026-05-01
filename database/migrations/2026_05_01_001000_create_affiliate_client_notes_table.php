<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_client_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('epi_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('follow_up_status', 30)->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['epi_channel_id', 'client_user_id'], 'affiliate_client_notes_channel_client_idx');
            $table->index(['epi_channel_id', 'follow_up_status'], 'affiliate_client_notes_channel_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_client_notes');
    }
};
