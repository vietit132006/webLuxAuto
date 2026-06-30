<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
    ];
    protected $primaryKey = 'user_id';
    protected $guard_name = 'web';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function savedLoginAccounts()
    {
        return $this->hasMany(SavedLoginAccount::class, 'user_id', 'user_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'user_id', 'user_id');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'user_id', 'user_id');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class, 'user_id', 'user_id');
    }

    public function serviceAppointments()
    {
        return $this->hasMany(ServiceAppointment::class, 'user_id', 'user_id');
    }

    public function handledServiceRecords()
    {
        return $this->hasMany(ServiceRecord::class, 'handled_by', 'user_id');
    }

    public function canViewStockHistory(): bool
    {
        try {
            return $this->can('inventory.history');
        } catch (Throwable) {
            return $this->role === 'admin';
        }
    }

    public function canAccessAdmin(): bool
    {
        if (!$this->status) {
            return false;
        }

        try {
            return $this->can('dashboard.view');
        } catch (Throwable) {
            return in_array($this->role, ['admin', 'staff'], true);
        }
    }

    public function adminRoleName(): ?string
    {
        try {
            return $this->getRoleNames()->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function adminRoleLabel(): string
    {
        return $this->adminRoleName() ?? match ($this->role) {
            'admin' => 'Admin',
            'staff' => 'Nhân viên',
            default => 'Khách hàng',
        };
    }

    public static function legacyRoleForAdminRole(?string $roleName): string
    {
        if (!$roleName) {
            return 'customer';
        }

        $roles = config('admin_permissions.roles', []);

        return $roles[$roleName]['legacy_role'] ?? 'staff';
    }
}
