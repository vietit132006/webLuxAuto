<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestDriveActivityLog extends Model
{
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_APPOINTMENT_UPDATED = 'appointment_updated';
    public const ACTION_NOTE_ADDED = 'note_added';
    public const ACTION_FILE_UPLOADED = 'file_uploaded';
    public const ACTION_FILE_DELETED = 'file_deleted';

    public const ACTION_LABELS = [
        self::ACTION_STATUS_CHANGED => 'Đổi trạng thái',
        self::ACTION_APPOINTMENT_UPDATED => 'Cập nhật lịch hẹn',
        self::ACTION_NOTE_ADDED => 'Thêm ghi chú',
        self::ACTION_FILE_UPLOADED => 'Upload tài liệu',
        self::ACTION_FILE_DELETED => 'Xóa tài liệu',
    ];

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', (string) $this->action));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
