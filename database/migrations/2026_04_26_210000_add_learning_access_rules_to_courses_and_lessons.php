<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('courses', 'lesson_access_mode')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->string('lesson_access_mode', 20)->default('free');
            });
        }

        if (! Schema::hasColumn('courses', 'show_locked_lessons')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->boolean('show_locked_lessons')->default(true);
            });
        }

        if (! Schema::hasColumn('course_lessons', 'status')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->string('status', 20)->default('published');
            });
        }

        if (! Schema::hasColumn('course_lessons', 'is_required')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->boolean('is_required')->default(true);
            });
        }

        if (! Schema::hasColumn('course_lessons', 'available_from')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->timestamp('available_from')->nullable();
            });
        }

        if (! Schema::hasColumn('lesson_progress', 'completed_at') && ! Schema::hasColumn('lesson_progress', 'is_completed')) {
            Schema::table('lesson_progress', function (Blueprint $table): void {
                $table->timestamp('completed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('courses', 'lesson_access_mode')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->dropColumn('lesson_access_mode');
            });
        }

        if (Schema::hasColumn('courses', 'show_locked_lessons')) {
            Schema::table('courses', function (Blueprint $table): void {
                $table->dropColumn('show_locked_lessons');
            });
        }

        if (Schema::hasColumn('course_lessons', 'status')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('course_lessons', 'is_required')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->dropColumn('is_required');
            });
        }

        if (Schema::hasColumn('course_lessons', 'available_from')) {
            Schema::table('course_lessons', function (Blueprint $table): void {
                $table->dropColumn('available_from');
            });
        }
    }
};
