<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dripsender_lists', function (Blueprint $table) {
            $table->id();
            $table->string('list_id')->unique();
            $table->string('list_name')->nullable();
            $table->integer('contact_count')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dripsender_lists');
    }
};
