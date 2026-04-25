<?php

namespace App\Actions\Oms;

use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UpdatePasswordFromOmsAction
{
    public function execute(string $epicCode, string $email, string $plainPassword): User
    {
        $epicCode = trim($epicCode);
        $email = trim($email);

        $user = null;

        if ($epicCode !== '') {
            $user = EpiChannel::query()
                ->where('epic_code', $epicCode)
                ->with('user')
                ->first()
                ?->user;
        }

        if (! $user && $email !== '') {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            throw new RuntimeException('User tidak ditemukan.');
        }

        $user->update([
            'password' => Hash::make($plainPassword),
        ]);

        return $user->refresh();
    }
}

