<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    // 1. Trang Bảng điều khiển (Dashboard)
    public function dashboard()
    {
        $totalCars = Car::count();
        $totalBrands = Brand::count();
        $totalStock = Car::sum('stock');
        $recentCars = Car::with('brand')->orderBy('car_id', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('totalCars', 'totalBrands', 'totalStock', 'recentCars'));
    }

    // 2. Trang danh sách xe của Admin (Đổi tên từ adminIndex thành index cho chuẩn)
    public function index(Request $request)
    {
        $search = $request->input('q');
        $cars = Car::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->orderBy('car_id', 'desc')->paginate(15);

        return view('admin.list_of_cars', compact('cars', 'search'));
    }

    // 3. Trang form thêm xe
    public function create()
    {
        $brands = Brand::all();
        return view('admin.create', compact('brands'));
    }

    // 4. Xử lý lưu xe mới
    public function store(Request $request)
    {
        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        Car::create($data);

        return redirect()->route('admin.cars.index')->with('success', 'Thêm xe thành công!');
    }

    // 5. Trang xem chi tiết xe (Góc nhìn Admin)
    public function show(Car $car): View
    {
        return view('admin.show', [
            'car' => $car,
        ]);
    }

    // 6. Trang form sửa xe
    public function edit($id)
    {
        $car = Car::findOrFail($id);
        $brands = Brand::all();
        // Nếu file edit của bạn nằm trong thư mục admin, hãy sửa lại thành view('admin.edit', ...)
        return view('admin.edit', compact('car', 'brands'));
    }

    // 7. Xử lý cập nhật xe
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $car->update($data);

        return redirect()->route('admin.cars.index')->with('success', 'Cập nhật xe thành công!');
    }

    // 8. Xử lý xóa xe (Mới thêm)
    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();

        return redirect()->route('admin.cars.index')->with('success', 'Đã xóa xe!');
    }
}
