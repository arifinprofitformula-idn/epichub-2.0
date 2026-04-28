<?php

namespace App\Actions\LegacyV1;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResolveLegacyV1UserMatchAction
{
    /**
     * @return array{
     *     user: ?User,
     *     matched_by: ?string,
     *     conflict: ?string,
     *     matches: array<string, Collection<int, User>>
     * }
     */
    public function execute(?string $epicId = null, ?string $email = null, ?string $whatsapp = null): array
    {
        $matches = [
            'epic_id' => new Collection(),
            'email' => new Collection(),
            'whatsapp' => new Collection(),
        ];

        if ($epicId !== null) {
            $matches['epic_id'] = User::query()
                ->where('legacy_epic_id', $epicId)
                ->orWhereHas('epiChannel', fn ($query) => $query->where('epic_code', $epicId))
                ->get()
                ->unique('id')
                ->values();

            if ($matches['epic_id']->count() > 1) {
                return [
                    'user' => null,
                    'matched_by' => null,
                    'conflict' => 'Satu ID EPIC mengarah ke lebih dari satu user EPIC HUB 2.0.',
                    'matches' => $matches,
                ];
            }
        }

        if ($email !== null) {
            $matches['email'] = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->get();
        }

        if ($whatsapp !== null) {
            $matches['whatsapp'] = User::query()
                ->where('whatsapp_number', $whatsapp)
                ->get();
        }

        $allMatches = collect($matches)
            ->flatten(1)
            ->unique('id')
            ->values();

        if ($allMatches->count() > 1) {
            return [
                'user' => null,
                'matched_by' => null,
                'conflict' => 'Identifier legacy mengarah ke user EPIC HUB 2.0 yang berbeda.',
                'matches' => $matches,
            ];
        }

        $user = $allMatches->first();

        if (! $user instanceof User) {
            return [
                'user' => null,
                'matched_by' => null,
                'conflict' => null,
                'matches' => $matches,
            ];
        }

        $matchedBy = null;

        foreach (['epic_id', 'email', 'whatsapp'] as $key) {
            if ($matches[$key]->contains(fn (User $matched): bool => $matched->is($user))) {
                $matchedBy = $key;
                break;
            }
        }

        return [
            'user' => $user,
            'matched_by' => $matchedBy,
            'conflict' => null,
            'matches' => $matches,
        ];
    }
}
