<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Brand;
use App\Models\CarImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $model = CarModel::with('brand')->find($id);
        return response()->json($model);
    }
    // Xử lý lưu xe cũ
    public function store(Request $request)
    {
        $uiLog = [
            'ts' => now()->toIso8601String(),
            'action' => 'admin.cars.store',
        ];

        // 1. Định nghĩa các quy tắc Validate
        $rules = [
            // Nhóm thông tin cơ bản
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin', // VIN thường tối đa 17 ký tự
            'license_plate'  => 'nullable|string|max:20',
            'price'          => 'required|numeric|min:0',
            'year'           => 'required|integer|min:1900|max:' . (date('Y') + 1),

            // Nhóm tình trạng
            'mileage_km'     => 'required|numeric|min:0',
            'owner_count'    => 'nullable|integer|min:1',
            'color'          => 'nullable|string|max:50',
            'interior_color' => 'nullable|string|max:50',
            'status'         => 'nullable|in:1,2,3',
            'is_featured'    => 'nullable|boolean',

            // Nhóm hình ảnh & Video
            // Lưu ý: rule `image` thường không hỗ trợ tốt HEIC/HEIF trên nhiều server,
            // nên dùng `file|mimetypes` để chấp nhận thêm các định dạng ảnh phổ biến.
            'image'          => 'required|file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'gallery'        => 'nullable|array|max:10', // Giới hạn tối đa 10 ảnh gallery để tránh quá tải
            'gallery.*'      => 'file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'video_file'     => 'nullable|file|mimes:mp4,mov,ogg,qt|max:20480', // Max 20MB cho video
            'video_url'      => 'nullable|url',

            // Mô tả
            'description'    => 'nullable|string|min:10',
        ];

        // 2. Định nghĩa tin nhắn báo lỗi tiếng Việt (Chuẩn UX)
        $messages = [
            'car_model_id.required' => 'Vui lòng chọn dòng xe (Model).',
            'car_model_id.exists'   => 'Dòng xe đã chọn không tồn tại.',
            'name.required'         => 'Bạn chưa nhập tên hiển thị cho xe.',
            'vin.required'          => 'Số khung (VIN) là bắt buộc.',
            'vin.unique'            => 'Số khung này đã tồn tại trên hệ thống.',
            'price.required'        => 'Vui lòng nhập giá bán.',
            'price.numeric'         => 'Giá bán phải là một con số.',
            'year.required'         => 'Vui lòng nhập năm sản xuất.',
            'year.max'              => 'Năm sản xuất không hợp lệ.',
            'mileage_km.required'   => 'Vui lòng nhập số km đã đi.',
            'image.required'        => 'Xe phải có ít nhất một ảnh đại diện.',
            'image.mimetypes'       => 'File tải lên phải là hình ảnh (jpg, jpeg, png, webp, avif, heic, heif).',
            'image.max'             => 'Ảnh đại diện không được vượt quá 5MB.',
            'gallery.*.mimetypes'   => 'Các file trong album phải là hình ảnh (jpg, jpeg, png, webp, avif, heic, heif).',
            'gallery.max'           => 'Bạn chỉ được tải lên tối đa 10 ảnh trong album.',
            'video_file.mimes'      => 'Video phải có định dạng mp4, mov hoặc ogg.',
            'video_url.url'         => 'Đường dẫn Youtube không đúng định dạng.',
            'description.min'       => 'Mô tả nên chi tiết một chút (ít nhất 10 ký tự).',
        ];

        // Thực hiện Validate
        $validated = $request->validate($rules, $messages);
        $uiLog['validated'] = true;
        $uiLog['input'] = collect($validated)->except(['description'])->toArray();
        $uiLog['files'] = [
            'image' => $request->hasFile('image') ? $request->file('image')->getClientOriginalName() : null,
            'gallery_count' => $request->hasFile('gallery') ? count($request->file('gallery')) : 0,
            'video_file' => $request->hasFile('video_file') ? $request->file('video_file')->getClientOriginalName() : null,
        ];

        try {
            $result = DB::transaction(function () use ($request, $validated, &$uiLog) {
                // Upload ảnh đại diện
                $mainImagePath = null;
                if ($request->hasFile('image')) {
                    $mainImagePath = $request->file('image')->store('cars/main', 'public');
                }
                $uiLog['stored_main_image'] = $mainImagePath;

                // Upload video file (nếu có)
                $videoFilePath = null;
                if ($request->hasFile('video_file')) {
                    $videoFilePath = $request->file('video_file')->store('cars/videos', 'public');
                }
                $uiLog['stored_video_file'] = $videoFilePath;

                // Tạo xe
                $car = new Car();
                $car->fill([
                    'car_model_id' => $validated['car_model_id'],
                    'name' => $validated['name'],
                    'vin' => $validated['vin'],
                    'license_plate' => $validated['license_plate'] ?? null,
                    'price' => $validated['price'],
                    'year' => $validated['year'],
                    'mileage_km' => $validated['mileage_km'],
                    'owner_count' => $validated['owner_count'] ?? null,
                    'color' => $validated['color'] ?? null,
                    'interior_color' => $validated['interior_color'] ?? null,
                    'status' => $validated['status'] ?? 1,
                    'is_featured' => (bool)($validated['is_featured'] ?? false),
                    'description' => $validated['description'] ?? null,
                    'image' => $mainImagePath,
                    'video_url' => $validated['video_url'] ?? null,
                    'video_file' => $videoFilePath,
                ]);

                $car->save();
                $uiLog['car_saved'] = true;
                $uiLog['car_id'] = $car->getAttribute('car_id');

                // Upload gallery (nếu có)
                $galleryPaths = [];
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $file) {
                        $path = $file->store('cars/gallery', 'public');
                        $galleryPaths[] = $path;

                        $img = new CarImage();
                        $img->car_id = $car->getAttribute('car_id');
                        $img->image_path = $path;
                        $img->save();
                    }
                }
                $uiLog['stored_gallery'] = $galleryPaths;

                return $car;
            });

            Log::info('admin.cars.store success', [
                'car_id' => $result->getAttribute('car_id'),
                'vin' => $result->getAttribute('vin'),
            ]);

            return redirect()
                ->route('admin.cars.show', $result->getAttribute('car_id'))
                ->with('success', 'Đã lưu xe thành công.')
                ->with('ui_log', $uiLog);
        } catch (\Exception $e) {
            $uiLog['error'] = true;
            $uiLog['message'] = $e->getMessage();

            Log::error('admin.cars.store failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Cleanup file đã upload (nếu lỗi sau khi store)
            foreach (['stored_main_image', 'stored_video_file'] as $k) {
                if (!empty($uiLog[$k])) {
                    Storage::disk('public')->delete($uiLog[$k]);
                }
            }
            if (!empty($uiLog['stored_gallery']) && is_array($uiLog['stored_gallery'])) {
                foreach ($uiLog['stored_gallery'] as $p) {
                    Storage::disk('public')->delete($p);
                }
            }

            return back()
                ->withInput()
                ->with('error', 'Lỗi hệ thống: ' . $e->getMessage())
                ->with('ui_log', $uiLog);
        }
    }
    // Hiển thị chi tiết xe trong trang quản trị
    public function show($id)
    {
        $car = Car::with(['brand', 'carModel', 'images'])->findOrFail($id);

        return view('admin.cars.show', compact('car'));
    }

    public function edit($id)
    {
        $car = Car::with(['images', 'carModel.brand'])->findOrFail($id);
        $brands = Brand::all();
        $carModels = CarModel::with('brand')->get();

        return view('admin.cars.edit', compact('car', 'brands', 'carModels'));
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $uiLog = [
            'ts' => now()->toIso8601String(),
            'action' => 'admin.cars.update',
            'car_id' => $car->getAttribute('car_id'),
        ];

        $rules = [
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin,' . $car->getAttribute('car_id') . ',car_id',
            'license_plate'  => 'nullable|string|max:20',
            'price'          => 'required|numeric|min:0',
            'year'           => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage_km'     => 'required|numeric|min:0',
            'owner_count'    => 'nullable|integer|min:1',
            'color'          => 'nullable|string|max:50',
            'interior_color' => 'nullable|string|max:50',
            'status'         => 'nullable|in:1,2,3',
            'is_featured'    => 'nullable|boolean',
            'image'          => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'gallery'        => 'nullable|array|max:10',
            'gallery.*'      => 'file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'video_file'     => 'nullable|file|mimes:mp4,mov,ogg,qt|max:20480',
            'video_url'      => 'nullable|url',
            'description'    => 'nullable|string|min:10',
        ];

        $messages = [
            'car_model_id.required' => 'Vui lòng chọn dòng xe (Model).',
            'car_model_id.exists'   => 'Dòng xe đã chọn không tồn tại.',
            'name.required'         => 'Bạn chưa nhập tên hiển thị cho xe.',
            'vin.required'          => 'Số khung (VIN) là bắt buộc.',
            'vin.unique'            => 'Số khung này đã tồn tại trên hệ thống.',
            'price.required'        => 'Vui lòng nhập giá bán.',
            'price.numeric'         => 'Giá bán phải là một con số.',
            'year.required'         => 'Vui lòng nhập năm sản xuất.',
            'year.max'              => 'Năm sản xuất không hợp lệ.',
            'mileage_km.required'   => 'Vui lòng nhập số km đã đi.',
            'image.mimetypes'       => 'File tải lên phải là hình ảnh (jpg, jpeg, png, webp, avif, heic, heif).',
            'image.max'             => 'Ảnh đại diện không được vượt quá 5MB.',
            'gallery.*.mimetypes'   => 'Các file trong album phải là hình ảnh (jpg, jpeg, png, webp, avif, heic, heif).',
            'gallery.max'           => 'Bạn chỉ được tải lên tối đa 10 ảnh trong album.',
            'video_file.mimes'      => 'Video phải có định dạng mp4, mov hoặc ogg.',
            'video_url.url'         => 'Đường dẫn Youtube không đúng định dạng.',
            'description.min'       => 'Mô tả nên chi tiết một chút (ít nhất 10 ký tự).',
        ];

        $validated = $request->validate($rules, $messages);
        $uiLog['validated'] = true;

        try {
            $result = DB::transaction(function () use ($request, $validated, $car, &$uiLog) {
                $mainImagePath = $car->image;
                if ($request->hasFile('image')) {
                    if ($mainImagePath) {
                        Storage::disk('public')->delete($mainImagePath);
                    }
                    $mainImagePath = $request->file('image')->store('cars/main', 'public');
                }
                $uiLog['stored_main_image'] = $mainImagePath;

                $videoFilePath = $car->video_file;
                if ($request->hasFile('video_file')) {
                    if ($videoFilePath) {
                        Storage::disk('public')->delete($videoFilePath);
                    }
                    $videoFilePath = $request->file('video_file')->store('cars/videos', 'public');
                }
                $uiLog['stored_video_file'] = $videoFilePath;

                $car->fill([
                    'car_model_id' => $validated['car_model_id'],
                    'name' => $validated['name'],
                    'vin' => $validated['vin'],
                    'license_plate' => $validated['license_plate'] ?? null,
                    'price' => $validated['price'],
                    'year' => $validated['year'],
                    'mileage_km' => $validated['mileage_km'],
                    'owner_count' => $validated['owner_count'] ?? null,
                    'color' => $validated['color'] ?? null,
                    'interior_color' => $validated['interior_color'] ?? null,
                    'status' => $validated['status'] ?? $car->status,
                    'is_featured' => (bool)($validated['is_featured'] ?? false),
                    'description' => $validated['description'] ?? null,
                    'image' => $mainImagePath,
                    'video_url' => $validated['video_url'] ?? $car->video_url,
                    'video_file' => $videoFilePath,
                ]);

                $car->save();
                $uiLog['car_saved'] = true;

                $galleryPaths = [];
                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $file) {
                        $path = $file->store('cars/gallery', 'public');
                        $galleryPaths[] = $path;

                        $img = new CarImage();
                        $img->car_id = $car->getAttribute('car_id');
                        $img->image_path = $path;
                        $img->save();
                    }
                }
                $uiLog['stored_gallery'] = $galleryPaths;

                return $car->fresh(['images', 'carModel.brand']);
            });

            Log::info('admin.cars.update success', [
                'car_id' => $result->getAttribute('car_id'),
                'vin' => $result->getAttribute('vin'),
            ]);

            return redirect()
                ->route('admin.cars.show', $result->getAttribute('car_id'))
                ->with('success', 'Đã cập nhật xe thành công.')
                ->with('ui_log', $uiLog);
        } catch (\Exception $e) {
            $uiLog['error'] = true;
            $uiLog['message'] = $e->getMessage();

            Log::error('admin.cars.update failed', [
                'car_id' => $car->getAttribute('car_id'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            foreach (['stored_main_image', 'stored_video_file'] as $k) {
                if (!empty($uiLog[$k]) && $uiLog[$k] !== $car->image && $uiLog[$k] !== $car->video_file) {
                    Storage::disk('public')->delete($uiLog[$k]);
                }
            }
            if (!empty($uiLog['stored_gallery']) && is_array($uiLog['stored_gallery'])) {
                foreach ($uiLog['stored_gallery'] as $p) {
                    Storage::disk('public')->delete($p);
                }
            }

            return back()
                ->withInput()
                ->with('error', 'Lỗi hệ thống: ' . $e->getMessage())
                ->with('ui_log', $uiLog);
        }
    }

    public function destroy($id)
    {
        $car = Car::with('images')->findOrFail($id);

        try {
            DB::transaction(function () use ($car) {
                if ($car->image) {
                    Storage::disk('public')->delete($car->image);
                }

                if ($car->video_file) {
                    Storage::disk('public')->delete($car->video_file);
                }

                foreach ($car->images as $img) {
                    Storage::disk('public')->delete($img->image_path);
                }

                $car->delete();
            });

            return redirect()
                ->route('admin.cars.index')
                ->with('success', 'Đã xóa xe thành công.');
        } catch (\Exception $e) {
            Log::error('admin.cars.destroy failed', [
                'car_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Không thể xóa xe: ' . $e->getMessage());
        }
    }
}
