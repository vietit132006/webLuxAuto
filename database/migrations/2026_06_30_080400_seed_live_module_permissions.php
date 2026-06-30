<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'live.view',
        'live.create',
        'live.edit',
        'live.delete',
        'live.manage',
        'live.leads.view',
        'live.leads.edit',
        'live.reports.view',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $now = now();

        foreach ($this->permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Super Admin', 'Admin', 'Marketing', 'Nhân viên bán hàng'])
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $rolePermissionNames = [
            'Super Admin' => $this->permissions,
            'Admin' => $this->permissions,
            'Marketing' => [
                'live.view',
                'live.create',
                'live.edit',
                'live.manage',
                'live.leads.view',
            ],
            'Nhân viên bán hàng' => [
                'live.leads.view',
                'live.leads.edit',
            ],
        ];

        foreach ($rolePermissionNames as $roleName => $permissions) {
            $roleId = $roleIds[$roleName] ?? null;

            if (!$roleId) {
                continue;
            }

            $rows = collect($permissions)
                ->map(fn (string $permission): ?array => isset($permissionIds[$permission]) ? [
                    'permission_id' => $permissionIds[$permission],
                    'role_id' => $roleId,
                ] : null)
                ->filter()
                ->values()
                ->all();

            if ($rows !== []) {
                DB::table('role_has_permissions')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->delete();

            DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->delete();
        }
    }
};
