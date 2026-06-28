<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Brand;
use App\Models\CarImage;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    private const VEHICLE_CONDITIONS = ['new', 'used', 'display', 'test_drive'];
    private const FEE_FIELDS = [
        'registration_fee',
        'license_plate_fee',
        'inspection_fee',
        'insurance_fee',
        'other_fees',
    ];

    public function __construct(private readonly StockMovementService $stockMovementService)
    {
    }

    // Hiển thị danh sách xe cũ trong kho
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $query = Car::with('carModel.brand');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('vin', 'LIKE', "%{$search}%")
                    ->orWhere('license_plate', 'LIKE', "%{$search}%")
                    ->orWhere('internal_code', 'LIKE', "%{$search}%")
                    ->orWhere('current_location', 'LIKE', "%{$search}%");
            });
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
            'internal_code' => $request->filled('internal_code')
                ? trim((string) $request->internal_code)
                : null,
            'current_location' => $request->filled('current_location')
                ? trim((string) $request->current_location)
                : null,
            'registration_area' => $request->filled('registration_area')
                ? trim((string) $request->registration_area)
                : null,
        ]);

        $currentYear = (int) date('Y');
        $maxVideoKb = 20480; // 20MB

        $rules = [
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin',
            'license_plate'  => 'nullable|string|max:20|unique:cars,license_plate',
            'internal_code'  => 'nullable|string|max:50|unique:cars,internal_code',
            'price'          => 'nullable|numeric|min:0',
            'list_price'     => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
            'license_plate_fee' => 'nullable|numeric|min:0',
            'inspection_fee' => 'nullable|numeric|min:0',
            'insurance_fee'  => 'nullable|numeric|min:0',
            'other_fees'     => 'nullable|numeric|min:0',
            'estimated_rolling_price' => 'nullable|numeric|min:0',
            'registration_area' => 'nullable|string|max:100',
            'year'           => 'required|integer|min:1000|max:' . $currentYear,
            'mileage_km'     => 'required|integer|min:0',
            'owner_count'    => 'nullable|integer|min:0|max:10',
            'stock_in_date'  => 'nullable|date',
            'on_road_date'   => 'nullable|date',
            'vehicle_condition' => 'required|in:' . implode(',', self::VEHICLE_CONDITIONS),
            'current_location' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
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

            $listPrice = $request->input('list_price');
            $salePrice = $request->input('sale_price');
            if ($salePrice !== null && $salePrice !== '' && is_numeric($salePrice) && is_numeric($listPrice)
                && (float) $salePrice > (float) $listPrice) {
                $validator->errors()->add(
                    'sale_price',
                    'Giá khuyến mãi không được lớn hơn giá niêm yết.'
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
        $pricing = $this->calculateCarPricing($validated);
        $initialStock = (int) ($validated['stock_quantity'] ?? 1);

        try {
            $result = DB::transaction(function () use ($request, $validated, $pricing, $initialStock, &$uiLog) {
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
                    'internal_code' => $validated['internal_code'] ?? null,
                    'price' => $pricing['actual_price'],
                    'list_price' => $pricing['list_price'],
                    'sale_price' => $pricing['sale_price'],
                    'registration_fee' => $pricing['registration_fee'],
                    'license_plate_fee' => $pricing['license_plate_fee'],
                    'inspection_fee' => $pricing['inspection_fee'],
                    'insurance_fee' => $pricing['insurance_fee'],
                    'other_fees' => $pricing['other_fees'],
                    'estimated_rolling_price' => $pricing['estimated_rolling_price'],
                    'registration_area' => $validated['registration_area'] ?? null,
                    'year' => $validated['year'],
                    'mileage_km' => $validated['mileage_km'],
                    'owner_count' => $validated['owner_count']
                        ?? ($validated['mileage_km'] == 0 ? 0 : 1),
                    'stock_in_date' => $validated['stock_in_date'] ?? null,
                    'on_road_date' => $validated['on_road_date'] ?? null,
                    'vehicle_condition' => $validated['vehicle_condition'] ?? 'new',
                    'current_location' => $validated['current_location'] ?? null,
                    'stock_quantity' => $initialStock,
                    'reserved_quantity' => 0,
                    'stock' => $initialStock,
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

                $this->stockMovementService->recordMovement(
                    $car,
                    0,
                    $initialStock,
                    $initialStock,
                    StockMovement::ACTION_IMPORT,
                    'Tạo xe mới và nhập tồn kho ban đầu.',
                    null,
                    null,
                    $request
                );

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
        $car = Car::with([
            'brand',
            'carModel',
            'images',
            'activeStockReservations.order.user',
            'activeStockReservations.user',
            'activeStockReservations.reservedBy',
        ])->findOrFail($id);

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
            'internal_code' => $request->filled('internal_code')
                ? trim((string) $request->internal_code)
                : null,
            'current_location' => $request->filled('current_location')
                ? trim((string) $request->current_location)
                : null,
            'registration_area' => $request->filled('registration_area')
                ? trim((string) $request->registration_area)
                : null,
        ]);

        $currentYear = (int) date('Y');
        $maxVideoKb = 20480; // 20MB

        $rules = [
            'car_model_id'   => 'required|exists:car_models,id',
            'name'           => 'required|string|max:255',
            'vin'            => 'required|string|max:17|unique:cars,vin,' . $car->getAttribute('car_id') . ',car_id',
            'license_plate'  => 'nullable|string|max:20|unique:cars,license_plate,' . $car->getAttribute('car_id') . ',car_id',
            'internal_code'  => 'nullable|string|max:50|unique:cars,internal_code,' . $car->getAttribute('car_id') . ',car_id',
            'price'          => 'nullable|numeric|min:0',
            'list_price'     => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
            'license_plate_fee' => 'nullable|numeric|min:0',
            'inspection_fee' => 'nullable|numeric|min:0',
            'insurance_fee'  => 'nullable|numeric|min:0',
            'other_fees'     => 'nullable|numeric|min:0',
            'estimated_rolling_price' => 'nullable|numeric|min:0',
            'registration_area' => 'nullable|string|max:100',
            'year'           => 'required|integer|min:1000|max:' . $currentYear,
            'mileage_km'     => 'required|integer|min:0',
            'owner_count'    => 'nullable|integer|min:0|max:10',
            'stock_in_date'  => 'nullable|date',
            'on_road_date'   => 'nullable|date',
            'vehicle_condition' => 'required|in:' . implode(',', self::VEHICLE_CONDITIONS),
            'current_location' => 'nullable|string|max:255',
            'stock_quantity' => 'nullable|integer|min:0',
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

            $listPrice = $request->input('list_price');
            $salePrice = $request->input('sale_price');
            if ($salePrice !== null && $salePrice !== '' && is_numeric($salePrice) && is_numeric($listPrice)
                && (float) $salePrice > (float) $listPrice) {
                $validator->errors()->add(
                    'sale_price',
                    'Giá khuyến mãi không được lớn hơn giá niêm yết.'
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
        $pricing = $this->calculateCarPricing($validated);

        try {
            $result = DB::transaction(function () use ($request, $validated, $pricing, $car, &$uiLog) {
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

                $oldStock = (int) ($car->stock_quantity ?? $car->stock ?? 0);
                $newStock = (int) ($validated['stock_quantity'] ?? $oldStock);

                if ($newStock < $car->reservedStock()) {
                    throw new \InvalidArgumentException('Không thể điều chỉnh tồn vật lý thấp hơn số lượng xe đang được giữ.');
                }

                $car->fill([
                    'car_model_id' => $validated['car_model_id'],
                    'name' => $validated['name'],
                    'vin' => $validated['vin'],
                    'license_plate' => $validated['license_plate'] ?? null,
                    'internal_code' => $validated['internal_code'] ?? null,
                    'price' => $pricing['actual_price'],
                    'list_price' => $pricing['list_price'],
                    'sale_price' => $pricing['sale_price'],
                    'registration_fee' => $pricing['registration_fee'],
                    'license_plate_fee' => $pricing['license_plate_fee'],
                    'inspection_fee' => $pricing['inspection_fee'],
                    'insurance_fee' => $pricing['insurance_fee'],
                    'other_fees' => $pricing['other_fees'],
                    'estimated_rolling_price' => $pricing['estimated_rolling_price'],
                    'registration_area' => $validated['registration_area'] ?? null,
                    'year' => $validated['year'],
                    'mileage_km' => $validated['mileage_km'],
                    'owner_count' => $validated['owner_count']
                        ?? ($validated['mileage_km'] == 0 ? 0 : 1),
                    'stock_in_date' => $validated['stock_in_date'] ?? null,
                    'on_road_date' => $validated['on_road_date'] ?? null,
                    'vehicle_condition' => $validated['vehicle_condition'] ?? ($car->vehicle_condition ?? 'new'),
                    'current_location' => $validated['current_location'] ?? null,
                    'stock_quantity' => $newStock,
                    'stock' => $newStock,
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

                $this->stockMovementService->recordMovement(
                    $car,
                    $oldStock,
                    $newStock - $oldStock,
                    $newStock,
                    StockMovement::ACTION_ADJUSTMENT,
                    'Cập nhật số lượng tồn trong hồ sơ xe.',
                    null,
                    null,
                    $request
                );

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

    private function calculateCarPricing(array $validated): array
    {
        $listPrice = $this->moneyValue($validated['list_price'] ?? 0);
        $salePrice = $this->nullableMoneyValue($validated['sale_price'] ?? null);
        $actualPrice = $salePrice ?? $listPrice;

        $pricing = [
            'list_price' => $listPrice,
            'sale_price' => $salePrice,
            'actual_price' => $actualPrice,
        ];

        $feeTotal = 0;
        foreach (self::FEE_FIELDS as $field) {
            $pricing[$field] = $this->moneyValue($validated[$field] ?? 0);
            $feeTotal += $pricing[$field];
        }

        $pricing['estimated_rolling_price'] = $actualPrice + $feeTotal;

        return $pricing;
    }

    private function moneyValue(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round((float) $value);
    }

    private function nullableMoneyValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->moneyValue($value);
    }

    private function carFormValidationAttributes(): array
    {
        return [
            'car_model_id'   => 'dòng xe',
            'name'           => 'tên hiển thị',
            'vin'            => 'số khung (VIN)',
            'license_plate'  => 'biển số xe',
            'internal_code'  => 'mã xe nội bộ',
            'price'          => 'giá bán thực tế',
            'list_price'     => 'giá niêm yết',
            'sale_price'     => 'giá khuyến mãi',
            'registration_fee' => 'phí trước bạ',
            'license_plate_fee' => 'phí biển số',
            'inspection_fee' => 'phí đăng kiểm',
            'insurance_fee'  => 'phí bảo hiểm',
            'other_fees'     => 'phí dịch vụ khác',
            'estimated_rolling_price' => 'giá lăn bánh dự kiến',
            'registration_area' => 'khu vực đăng ký',
            'year'           => 'năm sản xuất',
            'mileage_km'     => 'số km đã đi',
            'owner_count'    => 'số đời chủ',
            'stock_in_date'  => 'ngày nhập kho',
            'on_road_date'   => 'ngày lăn bánh',
            'vehicle_condition' => 'tình trạng xe',
            'current_location' => 'vị trí xe',
            'stock_quantity' => 'số lượng tồn kho',
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
            'date'     => ':attribute phải là ngày hợp lệ.',
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
            'internal_code.unique'   => 'Mã xe nội bộ này đã tồn tại, vui lòng dùng mã khác.',
            'internal_code.max'      => 'Mã xe nội bộ không được vượt quá 50 ký tự.',
            'price.required'        => 'Vui lòng nhập giá bán.',
            'price.numeric'         => 'Giá bán phải là một con số.',
            'price.min'             => 'Giá xe không được là số âm.',
            'list_price.required'   => 'Vui lòng nhập giá niêm yết.',
            'list_price.numeric'    => 'Giá niêm yết phải là một con số.',
            'list_price.min'        => 'Giá niêm yết không được là số âm.',
            'sale_price.numeric'    => 'Giá khuyến mãi phải là một con số.',
            'sale_price.min'        => 'Giá khuyến mãi không được là số âm.',
            'registration_fee.numeric' => 'Phí trước bạ phải là một con số.',
            'license_plate_fee.numeric' => 'Phí biển số phải là một con số.',
            'inspection_fee.numeric' => 'Phí đăng kiểm phải là một con số.',
            'insurance_fee.numeric' => 'Phí bảo hiểm phải là một con số.',
            'other_fees.numeric'    => 'Phí dịch vụ khác phải là một con số.',
            'registration_fee.min'  => 'Phí trước bạ không được là số âm.',
            'license_plate_fee.min' => 'Phí biển số không được là số âm.',
            'inspection_fee.min'    => 'Phí đăng kiểm không được là số âm.',
            'insurance_fee.min'     => 'Phí bảo hiểm không được là số âm.',
            'other_fees.min'        => 'Phí dịch vụ khác không được là số âm.',
            'estimated_rolling_price.numeric' => 'Giá lăn bánh dự kiến phải là một con số.',
            'estimated_rolling_price.min'     => 'Giá lăn bánh dự kiến không được là số âm.',
            'registration_area.max' => 'Khu vực đăng ký không được vượt quá 100 ký tự.',
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
            'stock_in_date.date'    => 'Ngày nhập kho không hợp lệ.',
            'on_road_date.date'     => 'Ngày lăn bánh không hợp lệ.',
            'vehicle_condition.required' => 'Vui lòng chọn tình trạng xe.',
            'vehicle_condition.in'  => 'Tình trạng xe không hợp lệ.',
            'current_location.max'  => 'Vị trí xe không được vượt quá 255 ký tự.',
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
