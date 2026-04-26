<?php

namespace Database\Seeders;

use App\Actions\Affiliates\EnsureDefaultEpiChannelAction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SuperAdminSeeder::class);

        app(EnsureDefaultEpiChannelAction::class)->execute();
    }
}
