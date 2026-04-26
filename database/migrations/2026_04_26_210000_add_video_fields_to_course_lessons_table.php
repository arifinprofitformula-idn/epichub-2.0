<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->text('video_url')->nullable()->change();

            $table->string('video_provider', 50)->nullable()->after('video_url');
            $table->string('video_id')->nullable()->after('video_provider');
            $table->string('video_title')->nullable()->after('video_id');
            $table->text('video_description')->nullable()->after('video_title');
            $table->boolean('show_video')->default(false)->after('video_description');
        });
    }

    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table): void {
            $table->dropColumn(['video_provider', 'video_id', 'video_title', 'video_description', 'show_video']);
            $table->string('video_url', 255)->nullable()->change();
        });
    }
};
