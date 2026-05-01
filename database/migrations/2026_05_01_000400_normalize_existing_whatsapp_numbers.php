<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normalize all whatsapp_number values to the 62... format and clear any
     * cross-format duplicates that slipped through before unique validation was enforced.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('whatsapp_number')
            ->orderBy('id')
            ->each(function (object $row): void {
                $normalized = $this->normalize($row->whatsapp_number);

                if ($normalized === null) {
                    DB::table('users')->where('id', $row->id)->update(['whatsapp_number' => null]);

                    return;
                }

                if ($normalized === $row->whatsapp_number) {
                    return; // Already in correct format.
                }

                // If another row already owns this normalized number, clear the duplicate
                // (older row wins — lower id was registered first).
                $alreadyOwned = DB::table('users')
                    ->where('whatsapp_number', $normalized)
                    ->where('id', '!=', $row->id)
                    ->exists();

                DB::table('users')
                    ->where('id', $row->id)
                    ->update(['whatsapp_number' => $alreadyOwned ? null : $normalized]);
            });
    }

    public function down(): void
    {
        // Normalization is a one-way data fix; cannot safely reverse.
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
