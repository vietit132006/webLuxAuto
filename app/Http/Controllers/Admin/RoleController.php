<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderByRaw("CASE WHEN name = 'Super Admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(12);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $role = new Role(['guard_name' => config('admin_permissions.guard', 'web')]);
        $permissionGroups = $this->permissionGroups();
        $selectedPermissions = collect();

        return view('admin.roles.form', compact('role', 'permissionGroups', 'selectedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => config('admin_permissions.guard', 'web'),
        ]);

        $role->syncPermissions($data['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Đã tạo vai trò mới.');
    }

    public function edit(Role $role): View
    {
        $permissionGroups = $this->permissionGroups();
        $selectedPermissions = $role->permissions->pluck('name');

        return view('admin.roles.form', compact('role', 'permissionGroups', 'selectedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validatedData($request, $role);

        if (!$this->isProtectedRole($role)) {
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
        } else {
            $role->syncPermissions(array_keys(config('admin_permissions.permissions', [])));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Đã cập nhật vai trò.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($this->isProtectedRole($role)) {
            return redirect()->route('admin.roles.index')->with('error', 'Không thể xóa Super Admin.');
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Đã xóa vai trò.');
    }

    private function validatedData(Request $request, ?Role $role = null): array
    {
        $isProtected = $role && $this->isProtectedRole($role);

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role?->id),
                Rule::notIn($isProtected ? [] : ['Customer', 'customer', 'Khách hàng', 'khách hàng']),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);
    }

    private function permissionGroups(): array
    {
        $permissionLabels = config('admin_permissions.permissions', []);
        $permissions = Permission::orderBy('name')->get()->keyBy('name');
        $used = collect();

        $groups = collect(config('admin_permissions.groups', []))
            ->map(function (array $group) use ($permissions, $permissionLabels, &$used) {
                $items = collect($group['permissions'])
                    ->filter(fn (string $name) => $permissions->has($name))
                    ->map(function (string $name) use ($permissions, $permissionLabels) {
                        return [
                            'name' => $name,
                            'label' => $permissionLabels[$name] ?? $name,
                        ];
                    })
                    ->values();

                $used = $used->merge($items->pluck('name'));

                return [
                    'label' => $group['label'],
                    'permissions' => $items,
                ];
            })
            ->filter(fn (array $group) => $group['permissions']->isNotEmpty())
            ->values()
            ->all();

        $other = $permissions
            ->keys()
            ->diff($used)
            ->map(fn (string $name) => [
                'name' => $name,
                'label' => $permissionLabels[$name] ?? $name,
            ])
            ->values();

        if ($other->isNotEmpty()) {
            $groups[] = [
                'label' => 'Khác',
                'permissions' => $other,
            ];
        }

        return $groups;
    }

    private function isProtectedRole(Role $role): bool
    {
        return $role->name === 'Super Admin';
    }
}
