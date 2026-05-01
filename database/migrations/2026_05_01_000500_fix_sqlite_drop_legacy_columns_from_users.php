<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration 000200 skipped SQLite, leaving legacy_import_batch_id (FK to
     * the already-dropped legacy_v1_import_batches) on the users table. Every
     * INSERT into users then fails with "no such table: legacy_v1_import_batches".
     *
     * SQLite's DROP COLUMN validates FK integrity even with foreign_keys = OFF,
     * so we must recreate the table via the classic temp-table rename trick.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $legacyColumns = [
            'legacy_source',
            'legacy_user_id',
            'legacy_import_batch_id',
            'legacy_imported_at',
            'legacy_epic_id',
            'must_reset_password',
        ];

        $existing = array_filter($legacyColumns, fn (string $col) => Schema::hasColumn('users', $col));

        if (empty($existing)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            // Non-SQLite was already handled by migration 000200.
            return;
        }

        // Columns to keep (all current columns minus the legacy ones).
        $keep = array_diff(
            array_column(DB::select('PRAGMA table_info(users)'), 'name'),
            array_values($existing),
        );
        $keepList = implode(', ', array_map(fn ($c) => '"'.$c.'"', $keep));

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('PRAGMA legacy_alter_table = ON');

        DB::transaction(function () use ($keepList): void {
            // New clean schema — identical to the users table but without legacy columns and FKs.
            DB::statement('CREATE TABLE "users_new" (
                "id" integer primary key autoincrement not null,
                "name" varchar not null,
                "email" varchar not null,
                "email_verified_at" datetime,
                "password" varchar not null,
                "remember_token" varchar,
                "created_at" datetime,
                "updated_at" datetime,
                "two_factor_secret" text,
                "two_factor_recovery_codes" text,
                "two_factor_confirmed_at" datetime,
                "whatsapp_number" varchar,
                "referrer_epi_channel_id" integer,
                "referral_locked_at" datetime,
                "referral_source" varchar,
                "profile_photo_path" varchar,
                foreign key("referrer_epi_channel_id") references "epi_channels"("id") on delete set null on update no action
            )');

            DB::statement("INSERT INTO \"users_new\" ({$keepList}) SELECT {$keepList} FROM \"users\"");

            DB::statement('DROP TABLE "users"');
            DB::statement('ALTER TABLE "users_new" RENAME TO "users"');

            // Restore any indexes that existed on the original table.
            DB::statement('CREATE UNIQUE INDEX "users_email_unique" ON "users" ("email")');

            $hasWaIndex = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='users_whatsapp_number_unique'");
            if (empty($hasWaIndex)) {
                DB::statement('CREATE UNIQUE INDEX "users_whatsapp_number_unique" ON "users" ("whatsapp_number")');
            }
        });

        DB::statement('PRAGMA legacy_alter_table = OFF');
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        //
    }
};
