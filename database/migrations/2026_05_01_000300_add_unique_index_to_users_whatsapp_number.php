<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize and deduplicate all existing whatsapp numbers before adding
        // the unique index. This must also handle rows that are already stored in
        // normalized form, otherwise MySQL will still fail on duplicate values.
        DB::table('users')
            ->whereNotNull('whatsapp_number')
            ->orderBy('id')
            ->each(function (object $row): void {
                $normalized = $this->normalize($row->whatsapp_number);

                if ($normalized === null) {
                    DB::table('users')
                        ->where('id', $row->id)
                        ->update(['whatsapp_number' => null]);

                    return;
                }

                // Older row wins. If another lower-id record already owns the same
                // normalized number, clear this duplicate even when its value was
                // already normalized before the migration ran.
                $ownerId = DB::table('users')
                    ->where('whatsapp_number', $normalized)
                    ->where('id', '<', $row->id)
                    ->orderBy('id')
                    ->value('id');

                if ($ownerId !== null) {
                    DB::table('users')
                        ->where('id', $row->id)
                        ->update(['whatsapp_number' => null]);
                } else {
                    DB::table('users')
                        ->where('id', $row->id)
                        ->update(['whatsapp_number' => $normalized]);
                }
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('whatsapp_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_whatsapp_number_unique');
        });
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9+]/', '', trim($value)) ?? '';

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '+62')) {
            $normalized = '62'.substr($normalized, 3);
        } elseif (str_starts_with($normalized, '62')) {
            // Already has Indonesia country code.
        } elseif (str_starts_with($normalized, '0')) {
            $normalized = '62'.substr($normalized, 1);
        } else {
            // Allow inputs that were saved without leading 0 or +62.
            $normalized = '62'.$normalized;
        }

        $normalized = preg_replace('/\D/', '', $normalized) ?? '';

        return $normalized !== '' ? $normalized : null;
    }
};
