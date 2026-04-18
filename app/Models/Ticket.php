<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // Báo cho Laravel biết tên bảng chính xác
    protected $table = 'support_tickets';

    // Báo cho Laravel biết khóa chính là ticket_id
    protected $primaryKey = 'ticket_id';

    protected $fillable = ['user_id', 'subject', 'message', 'status', 'admin_reply'];

    // Mối quan hệ: Một ticket thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
