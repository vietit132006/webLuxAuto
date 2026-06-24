<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
    ];
    protected $primaryKey = 'user_id';
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

    public function canViewStockHistory(): bool
    {
        $role = Str::of((string) $this->role)
            ->ascii()
            ->lower()
            ->replace([' ', '-'], '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();

        return in_array($role, [
            'super_admin',
            'admin',
            'warehouse_manager',
            'inventory_manager',
            'quan_ly_kho',
            'accountant',
            'ke_toan',
        ], true);
    }
}
