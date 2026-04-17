<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index()
    {
        // 1. Lấy ra các chiếc xe đang được đánh dấu là "Nổi bật" (is_featured = 1)
        // để hiển thị ngay bên dưới luồng Livestream cho khách dễ mua.
        $featuredCars = Car::where('is_featured', 1)
            ->orderBy('created_at', 'desc')
            ->take(4) // Lấy 4 chiếc tiêu biểu
            ->get();

        // 2. ID Video YouTube hoặc Link Facebook Livestream hiện tại (Có thể lưu trong DB, ở đây tôi code cứng để demo)
        // Ví dụ ID YouTube: v=dQw4w9WgXcQ -> ID là dQw4w9WgXcQ
        $liveVideoId = "YOUR_YOUTUBE_LIVE_ID_HERE";

        return view('client.livestream', compact('featuredCars', 'liveVideoId'));
    }
}
