<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleAuditLog extends Model
{
    protected $fillable = [
        'performed_by_user_id',
        'target_user_id',
        'old_role',
        'new_role',
    ];

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id', 'user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id', 'user_id');
    }
}
