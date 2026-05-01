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
        // Normalize all existing whatsapp numbers to 62... format before adding unique index.
        // This prevents migration failure from differently-formatted duplicates and ensures
        // the PHP validation closure (which queries normalized values) matches DB values.
        DB::table('users')
            ->whereNotNull('whatsapp_number')
            ->orderBy('id')
            ->each(function (object $row): void {
                $normalized = $this->normalize($row->whatsapp_number);

                if ($normalized === null || $normalized === $row->whatsapp_number) {
                    return;
                }

                // If another record already has this normalized number, clear the duplicate.
                $conflict = DB::table('users')
                    ->where('whatsapp_number', $normalized)
                    ->where('id', '!=', $row->id)
                    ->exists();

                if ($conflict) {
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
        } elseif (str_starts_with($normalized, '0')) {
            $normalized = '62'.substr($normalized, 1);
        }

        $normalized = preg_replace('/\D/', '', $normalized) ?? '';

        return $normalized !== '' ? $normalized : null;
    }
};
