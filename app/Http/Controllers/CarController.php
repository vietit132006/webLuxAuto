<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarController extends Controller
{
    // Hiển thị danh sách xe cho khách hàng (Trang chủ)
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q', ''));
        $query = Car::query()->orderByDesc('year')->orderByDesc('car_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"); // Đã sửa 'model' thành 'name' cho khớp DB của bạn
            });
        }

        $cars = $query->paginate(9)->withQueryString();

        return view('client.index', [
            'cars' => $cars,
            'search' => $search,
        ]);
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
