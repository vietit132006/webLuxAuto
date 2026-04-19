<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    // Báo cho Laravel biết tên bảng chính xác
    protected $table = 'support_tickets';

    // Báo cho Laravel biết khóa chính là ticket_id
    protected $primaryKey = 'ticket_id';

    // Bảng chỉ có created_at (không có updated_at)
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected $fillable = [
        'user_id',
        'ticket_type',
        'car_id',
        'subject',
        'message',
        'status',
        'admin_reply',
    ];

    // Mối quan hệ: Một ticket thuộc về một user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
