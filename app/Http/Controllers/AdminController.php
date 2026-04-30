<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // 1. Trang Bảng điều khiển (Dashboard)
    public function dashboard()
    {
        $totalCars = Car::count();
        $totalBrands = Brand::count();
        $totalStock = Car::sum('stock');
        $recentCars = Car::with('brand')->orderBy('car_id', 'desc')->take(5)->get();

        return view('admin.cars.dashboard', compact('totalCars', 'totalBrands', 'totalStock', 'recentCars'));
    }

    // 2. Trang danh sách xe của Admin
    public function index(Request $request)
    {
        $search = $request->input('q');
        $cars = Car::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->orderBy('car_id', 'desc')->paginate(15);

        return view('admin.cars.list_of_cars', compact('cars', 'search'));
    }

    // 3. Trang form thêm xe
    public function create()
    {
        $brands = Brand::all();
        return view('admin.cars.create', compact('brands'));
    }

    // 4. Xử lý lưu xe mới
    public function store(Request $request)
    {
        // Loại bỏ cả 'image' và 'gallery' ra khỏi data mặc định để xử lý riêng
        $data = $request->except(['image', 'gallery']);

        // Xử lý ảnh đại diện (Thumbnail)
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        // Xử lý Upload nhiều ảnh (Gallery)
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                // Lưu từng ảnh vào thư mục images/gallery
                $path = $file->store('images/gallery', 'public');
                $galleryPaths[] = $path;
            }
            $data['gallery'] = $galleryPaths; // Lưu mảng đường dẫn vào DB
        }

        // Xử lý Upload video
        if ($request->hasFile('video_file')) {
            // Lưu video vào thư mục storage/app/public/videos
            $videoPath = $request->file('video_file')->store('videos', 'public');
            $data['video_file'] = $videoPath;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        Car::create($data);

        return redirect()->route('admin.cars.index')->with('success', 'Thêm xe thành công!');
    }

    // 5. Trang xem chi tiết xe (Góc nhìn Admin)
    public function show(Car $car): View
    {
        return view('admin.cars.show', [
            'car' => $car,
        ]);
    }

    // 6. Trang form sửa xe
    public function edit($id)
    {
        $car = Car::findOrFail($id);
        $brands = Brand::all();
        return view('admin.cars.edit', compact('car', 'brands'));
    }

    // 7. Xử lý cập nhật xe
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $data = $request->except(['image', 'gallery']);

        // Xử lý ảnh đại diện
        if ($request->hasFile('image')) {
            // (Tùy chọn) Có thể thêm logic xóa ảnh cũ ở đây bằng Storage::disk('public')->delete($car->image)
            $imagePath = $request->file('image')->store('images', 'public');
            $data['image'] = $imagePath;
        }

        // Xử lý cập nhật Album ảnh (Gallery)
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $path = $file->store('images/gallery', 'public');
                $galleryPaths[] = $path;
            }

            // Ở đây tôi đang để chế độ GHI ĐÈ: Nếu upload ảnh mới, album cũ sẽ bị thay thế.
            // Nếu bạn muốn giữ ảnh cũ và NỐI THÊM ảnh mới, hãy dùng hàm sau:
            // $data['gallery'] = array_merge($car->gallery ?? [], $galleryPaths);
            $data['gallery'] = $galleryPaths;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $car->update($data);

        return redirect()->route('admin.cars.index')->with('success', 'Cập nhật xe thành công!');
    }

    // 8. Xử lý xóa xe
    public function destroy($id)
    {
        $car = Car::findOrFail($id);

        // Bạn có thể thêm logic xóa file ảnh vật lý (image và gallery) trong folder storage trước khi xóa record ở đây nếu muốn dọn rác

        $car->delete();

        return redirect()->route('admin.cars.index')->with('success', 'Đã xóa xe!');
    }
}
