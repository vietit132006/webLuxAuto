<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // 1. Dashboard
    public function dashboard()
    {
        $totalCars = Car::count();
        $totalBrands = Brand::count();
        $totalStock = Car::sum('stock') ?? 0;

        $recentCars = Car::with('brand')
            ->orderBy('car_id', 'desc')
            ->take(5)
            ->get();

        return view('admin.cars.dashboard', compact(
            'totalCars',
            'totalBrands',
            'totalStock',
            'recentCars'
        ));
    }

    // 2. Danh sách xe
    public function index(Request $request)
    {
        $search = $request->input('q');

        $cars = Car::with('brand') // ✅ FIX N+1
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('car_id', 'desc')
            ->paginate(15);

        return view('admin.cars.list_of_cars', compact('cars', 'search'));
    }

    // 3. Form thêm
    public function create()
    {
        $brands = Brand::all();
        return view('admin.cars.create', compact('brands'));
    }

    // 4. Lưu xe
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,brand_id',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'stock' => 'required|integer|min:0',

            'year' => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'mileage_km' => 'nullable|integer|min:0',

            'origin' => 'nullable|string|max:100',
            'body_type' => 'nullable|string|max:50',
            'engine' => 'nullable|string|max:100',
            'fuel' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'interior_color' => 'nullable|string|max:50',
            'seats' => 'nullable|integer|min:1|max:50',
            'drive_type' => 'nullable|string|max:50',

            'image' => 'required|image|max:2048',
            'gallery.*' => 'image|max:2048',

            'video_file' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'video_url' => 'nullable|url',
        ]);

        // ❗ FIX: Không cho cả 2 video
        if ($request->video_file && $request->video_url) {
            return back()->withErrors(['video' => 'Chỉ chọn 1: upload file hoặc link']);
        }

        $data = $request->except(['image', 'gallery']);

        // ✅ FORMAT PRICE
        $data['price'] = (int) $request->price;

        // IMAGE
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        // GALLERY
        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('images/gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        // VIDEO FILE
        if ($request->hasFile('video_file')) {
            $data['video_file'] = $request->file('video_file')->store('videos', 'public');
            $data['video_url'] = null;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

        Car::create($data);

        return redirect()->route('admin.cars.index')->with('success', 'Thêm xe thành công!');
    }

    // 5. Xem chi tiết
    public function show(Car $car): View
    {
        return view('admin.cars.show', compact('car'));
    }

    // 6. Form sửa
    public function edit($id)
    {
        $car = Car::findOrFail($id);
        $brands = Brand::all();

        return view('admin.cars.edit', compact('car', 'brands'));
    }

    // 7. Update
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $request->validate([
            'brand_id' => 'required|exists:brands,brand_id',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'stock' => 'required|integer|min:0',

            'image' => 'nullable|image|max:2048',
            'gallery.*' => 'image|max:2048',

            'video_file' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'video_url' => 'nullable|url',
        ]);

        if ($request->video_file && $request->video_url) {
            return back()->withErrors(['video' => 'Chỉ chọn 1: upload file hoặc link']);
        }

        $data = $request->except(['image', 'gallery']);
        $data['price'] = (int) $request->price;

        // IMAGE
        if ($request->hasFile('image')) {
            if ($car->image) {
                Storage::disk('public')->delete($car->image);
            }
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        // GALLERY
        if ($request->hasFile('gallery')) {

            // Xóa cũ
            if ($car->gallery) {
                foreach ($car->gallery as $img) {
                    Storage::disk('public')->delete($img);
                }
            }

            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('images/gallery', 'public');
            }

            $data['gallery'] = $gallery;
        }

        // VIDEO FILE
        if ($request->hasFile('video_file')) {
            if ($car->video_file) {
                Storage::disk('public')->delete($car->video_file);
            }

            $data['video_file'] = $request->file('video_file')->store('videos', 'public');
            $data['video_url'] = null;
        }

        // VIDEO URL
        if ($request->video_url) {
            if ($car->video_file) {
                Storage::disk('public')->delete($car->video_file);
            }
            $data['video_file'] = null;
        }

        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;

        $car->update($data);

        return redirect()->route('admin.cars.index')->with('success', 'Cập nhật xe thành công!');
    }

    // 8. Xóa xe
    public function destroy($id)
    {
        $car = Car::findOrFail($id);

        // ✅ FIX: XÓA FILE TRÁNH RÁC STORAGE
        if ($car->image) {
            Storage::disk('public')->delete($car->image);
        }

        if ($car->gallery) {
            foreach ($car->gallery as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        if ($car->video_file) {
            Storage::disk('public')->delete($car->video_file);
        }

        $car->delete();

        return redirect()->route('admin.cars.index')->with('success', 'Đã xóa xe!');
    }
}
