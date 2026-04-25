<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $accessAdminPanel = Permission::findOrCreate('access_admin_panel');
        $manageUsers = Permission::findOrCreate('manage_users');
        $manageRoles = Permission::findOrCreate('manage_roles');
        $viewDashboard = Permission::findOrCreate('view_dashboard');

        $superAdminRole = Role::findOrCreate('super_admin');
        $adminRole = Role::findOrCreate('admin');
        $customerRole = Role::findOrCreate('customer');
        $affiliateRole = Role::findOrCreate('affiliate');

        $superAdminRole->syncPermissions([
            $accessAdminPanel,
            $manageUsers,
            $manageRoles,
            $viewDashboard,
        ]);

        $adminRole->syncPermissions([
            $accessAdminPanel,
            $manageUsers,
            $manageRoles,
        ]);

        $customerRole->syncPermissions([$viewDashboard]);
        $affiliateRole->syncPermissions([$viewDashboard]);

        //
    }
}
