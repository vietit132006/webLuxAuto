<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Brand;
use App\Models\CarImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    // Hiển thị danh sách xe cũ trong kho
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = Car::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // THAY ĐỔI TẠI ĐÂY: Dùng paginate thay vì get
        $cars = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.cars.index', compact('cars', 'search'));
    }

    // Trang tạo mới xe
    public function create()
    {
        $brands = Brand::all();
        $carModels = CarModel::with('brand')->get(); // Lấy danh sách mẫu xe có sẵn
        // dd($carModels);
        return view('admin.cars.create', compact('brands', 'carModels'));
    }
    public function getModelSpecs($id)
    {
        $model = CarModel::find($id);
        return response()->json($model);
    }
    // Xử lý lưu xe cũ
    public function store(Request $request)
    {
        // dd($request->all(), $request->file('gallery'));
        // 1. Validate dữ liệu
        // 1. Validate dữ liệu
        $request->validate([
            'car_model_id' => 'required|exists:car_models,id',
            'vin'          => 'required|unique:cars,vin',
            'price'        => 'required|numeric',
            'mileage_km'   => 'required|numeric',
            'image'        => 'required|image|mimes:jpeg,png,jpg|max:5120',

            // Thay đổi ở đây:
            'gallery'      => 'nullable|array',
            'gallery.*'    => 'nullable|file|max:10240', // Chỉ cần kiểm tra là file, tăng giới hạn lên 10MB
        ]);


        try {
            return DB::transaction(function () use ($request) {

                // 2. Xử lý upload ảnh đại diện
                $thumbnailPath = $request->file('image')->store('cars/thumbnails', 'public');

                // 3. Lưu thông tin xe
                $car = Car::create([
                    'car_model_id'   => $request->car_model_id,
                    'vin'            => $request->vin,
                    'name'           => $request->name,
                    'license_plate'  => $request->license_plate,
                    'price'          => $request->price,
                    'year'           => $request->year ?? date('Y'), // Mặc định năm hiện tại
                    'mileage_km'     => $request->mileage_km,
                    'owner_count'    => $request->owner_count ?? 1,
                    'color'          => $request->color,
                    'interior_color' => $request->interior_color,
                    'description'    => $request->description,
                    'image'          => $thumbnailPath,
                    'status'         => 1,
                ]);

                // 4. Xử lý lưu Album ảnh
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $index => $file) {
                        $path = $file->store('cars/galleries', 'public');
                        CarImage::create([
                            'car_id'     => $car->car_id, // Sử dụng car_id từ model vừa tạo
                            'image_path' => $path,
                            'sort_order' => $index
                        ]);
                    }
                }

                return redirect()->route('admin.cars.index')->with('success', 'Đã thêm xe vào kho thành công!');
            });
        } catch (\Exception $e) {
            // Nếu lỗi, bạn có thể xóa ảnh đã upload lên storage tại đây để tránh rác (tùy chọn)
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
