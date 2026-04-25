<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'admin@epichub.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ],
        );

        if (! $user->hasRole('super_admin')) {
            $user->syncRoles(['super_admin']);
        }
    }
}
