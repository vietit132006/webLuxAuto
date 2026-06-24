<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('admin_permissions.guard', 'web');
        $permissions = config('admin_permissions.permissions', []);

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        foreach (config('admin_permissions.roles', []) as $roleName => $definition) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);

            $rolePermissions = $definition['permissions'] === '*'
                ? array_keys($permissions)
                : $definition['permissions'];

            $role->syncPermissions($rolePermissions);
        }

        $this->syncLegacyAdminUsers();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function syncLegacyAdminUsers(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        User::where('role', 'admin')
            ->whereDoesntHave('roles')
            ->get()
            ->each(fn (User $user) => $user->syncRoles(['Admin']));

        User::where('role', 'staff')
            ->whereDoesntHave('roles')
            ->get()
            ->each(fn (User $user) => $user->syncRoles(['Nhân viên bán hàng']));
    }
}
