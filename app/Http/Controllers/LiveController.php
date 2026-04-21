<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Support\Facades\DB; // Thêm dòng này nếu dùng DB query
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index()
    {
        // 1. Lấy cấu hình Livestream từ Database (Bảng live_sessions)
        // Lưu ý: Đổi tên Model \App\Models\LiveSession nếu project của bạn đặt tên khác
        $live = \App\Models\LiveSession::first();

        // (Tuỳ chọn) Nếu Admin đã TẮT luồng phát sóng, đá người dùng về trang chủ
        if ($live && $live->is_active == 0) {
            return redirect()->route('home')->with('error', 'Chưa có chương trình phát sóng trực tiếp nào đang diễn ra.');
        }

        // 2. Lấy ID Video YouTube chính xác từ Database
        $liveVideoId = $live ? $live->video_id : '';

        // 3. Lấy ra các chiếc xe đang được đánh dấu
        // Nếu trong admin bạn đang ghim xe dựa vào mảng $live->featured_car_ids, bạn có thể dùng đoạn dưới:
        /*
        $carIds = $live->featured_car_ids ?? [];
        $featuredCars = Car::whereIn('car_id', $carIds)->get();
        */

        // Hoặc tạm thời giữ nguyên logic cũ của bạn:
        $featuredCars = Car::where('is_featured', 1)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('client.livestream', compact('featuredCars', 'liveVideoId'));
    }
}
