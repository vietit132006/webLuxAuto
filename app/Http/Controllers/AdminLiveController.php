<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class AdminLiveController extends Controller
{
    // 1. Hiển thị trang Quản lý Livestream
    public function index()
    {
        // Lấy phiên Live đầu tiên (nếu chưa có thì tạo một phiên nháp tạm thời)
        $live = LiveSession::firstOrCreate(
            ['id' => 1],
            [
                'video_id' => '',
                'is_active' => 0,
                'featured_car_ids' => []
            ]
        );

        // Lấy toàn bộ danh sách xe để Admin chọn
        $cars = Car::orderBy('car_id', 'desc')->get();

        return view('admin.live.index', compact('live', 'cars'));
    }

    // 2. Xử lý lưu thiết lập Livestream
    public function update(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'video_id' => 'nullable|string|max:255',
            'car_ids' => 'nullable|array', // Mảng các ID xe được tick chọn
            'car_ids.*' => 'integer|exists:cars,car_id'
        ]);

        // Tìm phiên Live đang có
        $live = LiveSession::find(1);

        if (!$live) {
            return back()->with('error', 'Không tìm thấy cấu hình Live!');
        }

        // Cập nhật thông tin
        $live->video_id = $request->video_id;
        $live->is_active = $request->has('is_active') ? 1 : 0; // Nếu checkbox được tick thì bằng 1

        // Lưu mảng ID xe (nếu admin không chọn xe nào, gán mảng rỗng)
        $live->featured_car_ids = $request->car_ids ?? [];

        $live->save();

        return back()->with('success', 'Đã cập nhật cấu hình Livestream thành công!');
    }
}
