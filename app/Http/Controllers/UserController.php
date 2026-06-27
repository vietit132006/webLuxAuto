<?php

namespace App\Http\Controllers;

use App\Models\RoleAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('q');

        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('user_id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $adminRoles = $this->adminRoles();

        return view('admin.users.form', compact('adminRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $roleName = $data['admin_role'] ?? null;

        DB::transaction(function () use ($data, $roleName) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => User::legacyRoleForAdminRole($roleName),
                'status' => (bool) $data['status'],
            ]);

            $user->syncRoles($roleName ? [$roleName] : []);

            if ($roleName) {
                $this->writeRoleAudit($user, null, $roleName);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Đã thêm người dùng mới.');
    }

    public function edit($id): View
    {
        $user = User::with('roles')->findOrFail($id);
        $adminRoles = $this->adminRoles();

        return view('admin.users.form', compact('user', 'adminRoles'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::with('roles')->findOrFail($id);
        $data = $this->validatedData($request, $user);
        $roleName = $data['admin_role'] ?? null;
        $newStatus = (bool) $data['status'];

        if ($this->wouldRemoveLastSuperAdmin($user, $roleName)) {
            return back()->withInput()->with('error', 'Không thể đổi vai trò của Super Admin cuối cùng.');
        }

        if ($this->wouldLockLastActiveSuperAdmin($user, $newStatus)) {
            return back()->withInput()->with('error', 'Không thể khóa Super Admin đang hoạt động cuối cùng.');
        }

        if (Auth::id() === $user->user_id && !$newStatus) {
            return back()->withInput()->with('error', 'Không thể tự khóa tài khoản đang đăng nhập.');
        }

        DB::transaction(function () use ($user, $data, $roleName, $newStatus) {
            $oldRole = $user->adminRoleName();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => User::legacyRoleForAdminRole($roleName),
                'status' => $newStatus,
            ]);

            if (!empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            $user->syncRoles($roleName ? [$roleName] : []);

            if (($oldRole ?? '') !== ($roleName ?? '')) {
                $this->writeRoleAudit($user, $oldRole, $roleName);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    public function toggleStatus($id): RedirectResponse
    {
        $user = User::with('roles')->findOrFail($id);

        if (Auth::id() === $user->user_id) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể tự khóa tài khoản đang đăng nhập.');
        }

        $newStatus = !$user->status;

        if ($this->wouldLockLastActiveSuperAdmin($user, $newStatus)) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể khóa Super Admin đang hoạt động cuối cùng.');
        }

        $user->update(['status' => $newStatus]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', $newStatus ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
    }

    public function destroy($id): RedirectResponse
    {
        if (Auth::id() == $id) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể tự xóa tài khoản đang đăng nhập.');
        }

        $user = User::with('roles')->findOrFail($id);

        if ($this->isLastSuperAdmin($user)) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa Super Admin cuối cùng.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng.');
    }

    private function validatedData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->user_id, 'user_id'),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'admin_role' => ['nullable', 'string', Rule::in($this->adminRoleNames())],
            'status' => ['required', 'boolean'],
        ]);
    }

    private function adminRoles()
    {
        $configured = array_keys(config('admin_permissions.roles', []));

        return Role::query()
            ->where('guard_name', config('admin_permissions.guard', 'web'))
            ->whereNotIn('name', ['Customer', 'customer', 'Khách hàng', 'khách hàng'])
            ->get()
            ->sortBy(fn (Role $role) => ($position = array_search($role->name, $configured, true)) === false ? 999 : $position)
            ->values();
    }

    private function adminRoleNames(): array
    {
        return $this->adminRoles()->pluck('name')->all();
    }

    private function writeRoleAudit(User $targetUser, ?string $oldRole, ?string $newRole): void
    {
        RoleAuditLog::create([
            'performed_by_user_id' => Auth::id(),
            'target_user_id' => $targetUser->user_id,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);
    }

    private function wouldRemoveLastSuperAdmin(User $user, ?string $newRole): bool
    {
        return $this->isLastSuperAdmin($user) && $newRole !== 'Super Admin';
    }

    private function wouldLockLastActiveSuperAdmin(User $user, bool $newStatus): bool
    {
        if ($newStatus || !$user->status || !$user->hasRole('Super Admin')) {
            return false;
        }

        return User::role('Super Admin')
            ->where('status', true)
            ->where('user_id', '!=', $user->user_id)
            ->count() === 0;
    }

    private function isLastSuperAdmin(User $user): bool
    {
        if (!$user->hasRole('Super Admin')) {
            return false;
        }

        return User::role('Super Admin')
            ->where('user_id', '!=', $user->user_id)
            ->count() === 0;
    }
}
