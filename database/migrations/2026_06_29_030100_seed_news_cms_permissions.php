<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        'news.publish',
        'news_categories.view',
        'news_categories.create',
        'news_categories.edit',
        'news_categories.delete',
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
            ->whereIn('name', ['Super Admin', 'Admin', 'Marketing'])
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        foreach (['Super Admin', 'Admin', 'Marketing'] as $roleName) {
            $roleId = $roleIds[$roleName] ?? null;

            if (!$roleId) {
                continue;
            }

            $rows = collect($this->permissions)
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
        if (!Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
