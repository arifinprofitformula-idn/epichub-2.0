<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'legacy_epic_id')) {
                $table->string('legacy_epic_id', 50)->nullable()->after('email');
                $table->unique('legacy_epic_id');
            }

            if (! Schema::hasColumn('users', 'must_reset_password')) {
                $table->boolean('must_reset_password')->default(false)->after('password');
                $table->index('must_reset_password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_reset_password')) {
                $table->dropIndex(['must_reset_password']);
                $table->dropColumn('must_reset_password');
            }

            if (Schema::hasColumn('users', 'legacy_epic_id')) {
                $table->dropUnique(['legacy_epic_id']);
                $table->dropColumn('legacy_epic_id');
            }
        });
    }
};
