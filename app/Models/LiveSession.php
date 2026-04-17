<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'is_active',
        'featured_car_ids',
        'title',
        'description',
    ];

    // Chuyển đổi cột JSON thành mảng Array (rất quan trọng để lấy mảng ID xe ra)
    protected $casts = [
        'featured_car_ids' => 'array',
        'is_active' => 'boolean',
    ];
}
