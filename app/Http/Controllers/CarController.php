<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarController extends Controller
{
    // Hiển thị danh sách xe cho khách hàng (Trang chủ)
    public function index(\Illuminate\Http\Request $request)
    {
        // 1. Lấy danh sách các Hãng xe để đổ ra Sidebar lọc
        $brands = \App\Models\Brand::all();

        // 2. Bắt đầu câu truy vấn (Query Builder)
        $query = \App\Models\Car::query();

        // Lọc theo Tên xe (Từ khóa)
        $query->when($request->keyword, function ($q, $keyword) {
            return $q->where('name', 'like', '%' . $keyword . '%');
        });

        // Lọc theo Hãng xe
        $query->when($request->brand_id, function ($q, $brand_id) {
            return $q->where('brand_id', $brand_id);
        });

        // Lọc theo Trạng thái (Ví dụ: 1 = Mới 100%, 0 = Xe lướt)
        $query->when($request->has('status') && $request->status != '', function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        // Lọc theo Khoảng giá (Từ Min đến Max)
        $query->when($request->min_price, function ($q, $min_price) {
            return $q->where('price', '>=', $min_price);
        });
        $query->when($request->max_price, function ($q, $max_price) {
            return $q->where('price', '<=', $max_price);
        });

        // 3. Thực thi truy vấn, sắp xếp xe mới nhất lên đầu và phân trang
        // LƯU Ý: Phải có withQueryString() để khi khách bấm sang Trang 2, bộ lọc không bị mất!
        $cars = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('client.index', compact('cars', 'brands'));
    }

    // Hiển thị trang chi tiết xe dành cho Khách hàng
    public function show(Car $car): View
    {
        // Nhớ tạo view 'cars.detail' bằng đoạn code giao diện Khách hàng tôi đã gửi trước đó nhé
        return view('client.show', [
            'car' => $car,
        ]);
    }
}
