<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'referrer_epi_channel_id')) {
                    $table->foreignId('referrer_epi_channel_id')
                        ->nullable()
                        ->after('whatsapp_number')
                        ->constrained('epi_channels')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('users', 'referral_locked_at')) {
                    $table->timestamp('referral_locked_at')
                        ->nullable()
                        ->after('referrer_epi_channel_id');
                }

                if (! Schema::hasColumn('users', 'referral_source')) {
                    $table->string('referral_source')
                        ->nullable()
                        ->after('referral_locked_at');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'referrer_epi_channel_id')) {
                    $table->foreignId('referrer_epi_channel_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('epi_channels')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('orders', 'referral_source')) {
                    $table->string('referral_source')
                        ->nullable()
                        ->after('referrer_epi_channel_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'referral_source')) {
                    $table->dropColumn('referral_source');
                }

                if (Schema::hasColumn('orders', 'referrer_epi_channel_id')) {
                    $table->dropConstrainedForeignId('referrer_epi_channel_id');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'referral_source')) {
                    $table->dropColumn('referral_source');
                }

                if (Schema::hasColumn('users', 'referral_locked_at')) {
                    $table->dropColumn('referral_locked_at');
                }

                if (Schema::hasColumn('users', 'referrer_epi_channel_id')) {
                    $table->dropConstrainedForeignId('referrer_epi_channel_id');
                }
            });
        }
    }
};
