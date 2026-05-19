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
use Illuminate\Support\Facades\Validator;

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
    // Xử lý lưu xe
    public function store(Request $request)
    {
        $uiLog = [
            'ts' => now()->toIso8601String(),
            'action' => 'admin.cars.store',
        ];

        $request->merge([
            'license_plate' => $request->filled('license_plate')
                ? trim((string) $request->license_plate)
                : null,
        ]);

        $currentYear = (int) date('Y');
        $maxVideoKb = 20480; // 20MB

        $rules = [
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin',
            'license_plate'  => 'nullable|string|max:20|unique:cars,license_plate',
            'price'          => 'required|numeric|min:0',
            'year'           => 'required|integer|min:1000|max:' . $currentYear,
            'mileage_km'     => 'required|integer|min:0',
            'owner_count'    => 'nullable|integer|min:0|max:10',
            'color'          => 'nullable|string|max:50',
            'interior_color' => 'nullable|string|max:50',
            'status'         => 'nullable|in:1,2,3',
            'is_featured'    => 'nullable|boolean',
            'image'          => 'required|file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'gallery'        => 'nullable|array|max:10',
            'gallery.*'      => 'file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'video_file'     => 'nullable|file|mimes:mp4,mov,ogg,qt,m4v,avi|max:' . $maxVideoKb,
            'video_url'      => 'nullable|url',
            'description'    => 'nullable|string|max:10000',
        ];

        $messages = $this->carFormValidationMessages($maxVideoKb);
        $attributes = $this->carFormValidationAttributes();

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        $validator->after(function ($validator) use ($request) {
            $description = $request->input('description');
            if ($description !== null && $description !== '' && mb_strlen(trim($description)) < 10) {
                $validator->errors()->add(
                    'description',
                    'Mô tả nên có ít nhất 10 ký tự hoặc để trống.'
                );
            }

            $mileage = $request->input('mileage_km');
            if ($mileage === null || $mileage === '') {
                return;
            }

            $mileage = (int) $mileage;
            $ownerCount = $request->input('owner_count');
            if ($ownerCount === null || $ownerCount === '') {
                $ownerCount = $mileage === 0 ? 0 : 1;
            }
            $ownerCount = (int) $ownerCount;

            if ($mileage === 0) {
                if ($ownerCount < 0 || $ownerCount > 10) {
                    $validator->errors()->add(
                        'owner_count',
                        'Xe mới (0 km): số đời chủ được phép từ 0 đến 10.'
                    );
                }
            } elseif ($ownerCount < 1 || $ownerCount > 10) {
                $validator->errors()->add(
                    'owner_count',
                    'Xe đã qua sử dụng: số đời chủ phải từ 1 đến 10.'
                );
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('admin.cars.create')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Không thể thêm xe. Vui lòng kiểm tra lại các trường được đánh dấu.');
        }

        $validated = $validator->validated();
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
                    'owner_count' => $validated['owner_count']
                        ?? ($validated['mileage_km'] == 0 ? 0 : 1),
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

            return redirect()
                ->route('admin.cars.create')
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

        $request->merge([
            'license_plate' => $request->filled('license_plate')
                ? trim((string) $request->license_plate)
                : null,
        ]);

        $currentYear = (int) date('Y');
        $maxVideoKb = 20480; // 20MB

        $rules = [
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin,' . $car->getAttribute('car_id') . ',car_id',
            'license_plate'  => 'nullable|string|max:20|unique:cars,license_plate,' . $car->getAttribute('car_id') . ',car_id',
            'price'          => 'required|numeric|min:0',
            'year'           => 'required|integer|min:1000|max:' . $currentYear,
            'mileage_km'     => 'required|integer|min:0',
            'owner_count'    => 'nullable|integer|min:0|max:10',
            'color'          => 'nullable|string|max:50',
            'interior_color' => 'nullable|string|max:50',
            'status'         => 'nullable|in:1,2,3',
            'is_featured'    => 'nullable|boolean',
            'image'          => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'gallery'        => 'nullable|array|max:10',
            'gallery.*'      => 'file|mimetypes:image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif|max:5120',
            'video_file'     => 'nullable|file|mimes:mp4,mov,ogg,qt,m4v,avi|max:' . $maxVideoKb,
            'video_url'      => 'nullable|url',
            'description'    => 'nullable|string|max:10000',
        ];

        $messages = $this->carFormValidationMessages($maxVideoKb);
        $attributes = $this->carFormValidationAttributes();

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        $validator->after(function ($validator) use ($request) {
            $description = $request->input('description');
            if ($description !== null && $description !== '' && mb_strlen(trim($description)) < 10) {
                $validator->errors()->add(
                    'description',
                    'Mô tả nên có ít nhất 10 ký tự hoặc để trống.'
                );
            }

            $mileage = $request->input('mileage_km');
            if ($mileage === null || $mileage === '') {
                return;
            }

            $mileage = (int) $mileage;
            $ownerCount = $request->input('owner_count');
            if ($ownerCount === null || $ownerCount === '') {
                $ownerCount = $mileage === 0 ? 0 : 1;
            }
            $ownerCount = (int) $ownerCount;

            if ($mileage === 0) {
                if ($ownerCount < 0 || $ownerCount > 10) {
                    $validator->errors()->add(
                        'owner_count',
                        'Xe mới (0 km): số đời chủ được phép từ 0 đến 10.'
                    );
                }
            } elseif ($ownerCount < 1 || $ownerCount > 10) {
                $validator->errors()->add(
                    'owner_count',
                    'Xe đã qua sử dụng: số đời chủ phải từ 1 đến 10.'
                );
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('admin.cars.edit', $car->getAttribute('car_id'))
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Không thể cập nhật xe. Vui lòng kiểm tra lại các trường được đánh dấu.');
        }

        $validated = $validator->validated();
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
                    'owner_count' => $validated['owner_count']
                        ?? ($validated['mileage_km'] == 0 ? 0 : 1),
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

    private function carFormValidationAttributes(): array
    {
        return [
            'car_model_id'   => 'dòng xe',
            'name'           => 'tên hiển thị',
            'vin'            => 'số khung (VIN)',
            'license_plate'  => 'biển số xe',
            'price'          => 'giá bán',
            'year'           => 'năm sản xuất',
            'mileage_km'     => 'số km đã đi',
            'owner_count'    => 'số đời chủ',
            'color'          => 'màu ngoại thất',
            'interior_color' => 'màu nội thất',
            'status'         => 'trạng thái bán hàng',
            'is_featured'    => 'độ nổi bật',
            'image'          => 'ảnh đại diện',
            'gallery'        => 'album ảnh',
            'gallery.*'      => 'ảnh trong album',
            'video_file'     => 'file video',
            'video_url'      => 'đường dẫn Youtube',
            'description'    => 'mô tả',
        ];
    }

    private function carFormValidationMessages(int $maxVideoKb): array
    {
        $maxVideoMb = (int) round($maxVideoKb / 1024);

        return [
            'required' => 'Vui lòng nhập :attribute.',
            'string'   => ':attribute phải là chuỗi ký tự.',
            'integer'  => ':attribute phải là số nguyên.',
            'numeric'  => ':attribute phải là số.',
            'max.string' => ':attribute không được vượt quá :max ký tự.',
            'max.array'  => ':attribute chỉ được tối đa :max mục.',
            'max.file'   => ':attribute không được vượt quá :max kilobyte.',
            'min.numeric' => ':attribute không được nhỏ hơn :min.',
            'min.integer' => ':attribute không được nhỏ hơn :min.',
            'max.numeric' => ':attribute không được lớn hơn :max.',
            'max.integer' => ':attribute không được lớn hơn :max.',
            'in'       => ':attribute không hợp lệ.',
            'url'      => ':attribute phải là đường dẫn hợp lệ.',
            'boolean'  => ':attribute không hợp lệ.',
            'exists'   => ':attribute đã chọn không tồn tại.',
            'unique'   => ':attribute đã tồn tại trên hệ thống, vui lòng kiểm tra lại.',
            'file'     => ':attribute phải là tệp tin hợp lệ.',
            'mimes'    => ':attribute phải có định dạng: :values.',
            'mimetypes' => ':attribute phải là file ảnh (jpg, png, webp, avif, heic, heif).',

            'car_model_id.required' => 'Vui lòng chọn dòng xe (Model).',
            'car_model_id.exists'   => 'Dòng xe đã chọn không tồn tại.',
            'name.required'         => 'Bạn chưa nhập tên hiển thị cho xe.',
            'name.max'              => 'Tên hiển thị không được vượt quá 255 ký tự.',
            'vin.required'          => 'Số khung (VIN) là bắt buộc.',
            'vin.max'               => 'Số khung (VIN) tối đa 17 ký tự.',
            'vin.unique'            => 'Số khung này đã được đăng ký cho xe khác, vui lòng kiểm tra lại.',
            'license_plate.unique'  => 'Biển số xe này đã được đăng ký cho xe khác, vui lòng kiểm tra lại.',
            'license_plate.max'     => 'Biển số xe không được vượt quá 20 ký tự.',
            'price.required'        => 'Vui lòng nhập giá bán.',
            'price.numeric'         => 'Giá bán phải là một con số.',
            'price.min'             => 'Giá xe không được là số âm.',
            'year.required'         => 'Vui lòng nhập năm sản xuất.',
            'year.integer'          => 'Năm sản xuất phải là số nguyên.',
            'year.min'              => 'Năm sản xuất phải từ năm 1000 trở lên.',
            'year.max'              => 'Năm sản xuất không được vượt quá năm hiện tại.',
            'mileage_km.required'   => 'Vui lòng nhập số km đã đi.',
            'mileage_km.integer'    => 'Số km phải là số nguyên.',
            'mileage_km.min'        => 'Số km không được âm.',
            'owner_count.integer'   => 'Số đời chủ phải là số nguyên.',
            'owner_count.min'       => 'Số đời chủ không hợp lệ.',
            'owner_count.max'       => 'Số đời chủ tối đa là 10.',
            'image.required'        => 'Xe phải có ít nhất một ảnh đại diện.',
            'image.mimetypes'       => 'Ảnh đại diện phải là file jpg, png, webp, avif, heic hoặc heif.',
            'image.max'             => 'Ảnh đại diện không được vượt quá 5MB.',
            'gallery.max'           => 'Album ảnh chỉ được tối đa 10 ảnh.',
            'gallery.*.mimetypes'   => 'Mỗi ảnh trong album phải là jpg, png, webp, avif, heic hoặc heif.',
            'gallery.*.max'         => 'Mỗi ảnh trong album không được vượt quá 5MB.',
            'video_file.mimes'      => 'Video phải có định dạng mp4, mov, m4v hoặc avi.',
            'video_file.max'        => "Video vượt quá dung lượng cho phép (tối đa {$maxVideoMb}MB).",
            'video_url.url'         => 'Đường dẫn Youtube không đúng định dạng.',
            'description.min'       => 'Mô tả nên có ít nhất 10 ký tự (hoặc để trống).',
        ];
    }
}
