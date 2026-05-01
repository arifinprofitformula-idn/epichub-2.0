<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasColumn('users', 'legacy_import_batch_id') && in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_legacy_batch_fk');
            } catch (Throwable) {
                //
            }
        }

        if (Schema::hasColumn('users', 'legacy_epic_id') && in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_legacy_epic_id_unique');
            } catch (Throwable) {
                //
            }
        }

        if (Schema::hasColumn('users', 'must_reset_password') && in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_must_reset_password_index');
            } catch (Throwable) {
                //
            }
        }

        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'legacy_source',
                'legacy_user_id',
                'legacy_import_batch_id',
                'legacy_imported_at',
                'legacy_epic_id',
                'must_reset_password',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        //
    }
};
