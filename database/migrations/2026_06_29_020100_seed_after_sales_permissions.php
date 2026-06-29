<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'warranties.view',
        'warranties.create',
        'warranties.edit',
        'warranties.delete',
        'services.view',
        'services.create',
        'services.edit',
        'services.delete',
        'service_records.view',
        'service_records.create',
        'service_records.edit',
        'service_records.delete',
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

        DB::table('roles')->insertOrIgnore([
            'name' => 'Nhân viên dịch vụ / CSKH',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Super Admin', 'Admin', 'Nhân viên bán hàng', 'Nhân viên dịch vụ / CSKH', 'Kế toán'])
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $rolePermissionNames = [
            'Super Admin' => $this->permissions,
            'Admin' => $this->permissions,
            'Nhân viên bán hàng' => ['warranties.view'],
            'Nhân viên dịch vụ / CSKH' => [
                'warranties.view',
                'services.view',
                'services.create',
                'services.edit',
                'service_records.view',
                'service_records.create',
            ],
            'Kế toán' => [
                'warranties.view',
                'services.view',
                'service_records.view',
            ],
        ];

        foreach ($rolePermissionNames as $roleName => $permissionNames) {
            $roleId = $roleIds[$roleName] ?? null;

            if (!$roleId) {
                continue;
            }

            $rows = collect($permissionNames)
                ->map(fn (string $permission): ?array => isset($permissionIds[$permission]) ? [
                    'role_id' => $roleId,
                    'permission_id' => $permissionIds[$permission],
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
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
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

        DB::table('roles')
            ->where('name', 'Nhân viên dịch vụ / CSKH')
            ->where('guard_name', 'web')
            ->delete();
    }
};
