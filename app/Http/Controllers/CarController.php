<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request): View
    {
        // dd($request->all());
        $search = trim((string) $request->get('q', ''));

        $query = Car::query()->orderByDesc('year')->orderByDesc('car_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand_id', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $cars = $query->paginate(9)->withQueryString();

        return view('cars.index', [
            'cars' => $cars,
            'search' => $search,
        ]);
    }
    public function adminIndex(Request $request)
    {
        $search = $request->input('q');

        // Lấy danh sách xe, sắp xếp xe mới thêm lên đầu
        $cars = Car::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->orderBy('car_id', 'desc')->paginate(15);

        // Trả về đúng file view trong thư mục admin của bạn
        return view('admin.list_of_cars', compact('cars', 'search'));
    }
    // Nhớ kiểm tra xem đã import Model Brand ở trên đầu file chưa nhé
    // use App\Models\Brand;

    public function create()
    {
        // Lấy toàn bộ danh sách hãng xe từ database
        $brands = Brand::all();

        // Truyền biến $brands sang view
        return view('admin.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        // Nếu checkbox 'is_featured' không được tích, request sẽ không có trường này.
        // Ta gán mặc định là 0 để tránh lỗi.
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

        Car::create($data);

        return redirect()->route('cars.index');
    }

    public function show(Car $car): View
    {
        return view('cars.show', [
            'car' => $car,
        ]);
    }

    public function edit($id)
    {
        $car = Car::findOrFail($id);
        $brands = Brand::all();
        return view('cars.edit', compact('car', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $car->update($request->all());

        return redirect()->route('cars.index');
    }
}
