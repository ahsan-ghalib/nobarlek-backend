<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\DashboardPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';
        $permissionNames = DashboardPermissions::names();

        foreach ($permissionNames as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $adminRole = Role::findOrCreate(DashboardPermissions::adminRole(), $guard);
        $adminRole->syncPermissions($permissionNames);

        $admin = User::query()
            ->where('email', config('dashboard-permissions.admin_email', 'admin@upliga.com'))
            ->first();

        if ($admin) {
            $admin->syncRoles([$adminRole]);
        }
    }
}
