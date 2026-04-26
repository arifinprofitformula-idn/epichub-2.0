<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_lesson_attachments', function (Blueprint $table) {
            $table->string('source_type', 30)->default('upload');
            $table->string('external_url')->nullable();
            $table->string('button_label')->nullable();
            $table->boolean('open_in_new_tab')->default(true);
        });

        Schema::table('course_lesson_attachments', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });

        DB::table('course_lesson_attachments')
            ->whereNull('source_type')
            ->update(['source_type' => 'upload']);
    }

    public function down(): void
    {
        Schema::table('course_lesson_attachments', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
            $table->dropColumn([
                'source_type',
                'external_url',
                'button_label',
                'open_in_new_tab',
            ]);
        });
    }
};
