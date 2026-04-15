<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $primaryKey = 'news_id'; // Giả sử khóa chính của bạn là news_id

    protected $fillable = [
        'title',
        'slug',      // Đường dẫn không dấu (VD: xe-bmw-moi-ra-mat)
        'summary',   // Tóm tắt tin
        'content',   // Nội dung chi tiết
        'image',     // Ảnh bìa bài viết
        'status',    // 1: Hiện, 0: Ẩn
    ];
}
