<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CarModelController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $query = CarModel::query()
            ->with('brand')
            ->withCount('cars');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('engine', 'LIKE', "%{$search}%")
                    ->orWhere('fuel_type', 'LIKE', "%{$search}%")
                    ->orWhere('transmission', 'LIKE', "%{$search}%")
                    ->orWhere('body_type', 'LIKE', "%{$search}%")
                    ->orWhere('drive_type', 'LIKE', "%{$search}%")
                    ->orWhere('origin', 'LIKE', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $carModels = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.car_models.index', compact('carModels', 'search'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name')->get();

        return view('admin.car_models.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateModel($request);

        try {
            CarModel::create($validated);

            return redirect()
                ->route('admin.car-models.index')
                ->with('success', 'Đã thêm model xe thành công.');
        } catch (\Exception $e) {
            Log::error('admin.car-models.store failed', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Không thể thêm model xe: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $carModel = CarModel::with('brand')
            ->withCount('cars')
            ->findOrFail($id);

        $cars = $carModel->cars()
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        return view('admin.car_models.show', compact('carModel', 'cars'));
    }

    public function edit($id)
    {
        $carModel = CarModel::withCount('cars')->findOrFail($id);
        $brands = Brand::orderBy('name')->get();

        return view('admin.car_models.edit', compact('carModel', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $carModel = CarModel::findOrFail($id);
        $validated = $this->validateModel($request, $carModel->id);

        try {
            $carModel->update($validated);

            return redirect()
                ->route('admin.car-models.index')
                ->with('success', 'Đã cập nhật model xe thành công.');
        } catch (\Exception $e) {
            Log::error('admin.car-models.update failed', [
                'model_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật model xe: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $carModel = CarModel::withCount('cars')->findOrFail($id);

        // Nếu model đã có xe sử dụng thì không cho xóa
        if ($carModel->cars_count > 0) {
            return back()->with(
                'error',
                'Không thể xóa dòng xe "' . $carModel->name . '" vì đang có ' . $carModel->cars_count . ' xe sử dụng.'
            );
        }

        try {
            DB::transaction(function () use ($carModel) {
                $carModel->delete();
            });

            return redirect()
                ->route('admin.car-models.index')
                ->with('success', 'Đã xóa dòng xe thành công.');
        } catch (\Exception $e) {
            Log::error('admin.car-models.destroy failed', [
                'model_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Không thể xóa dòng xe: ' . $e->getMessage());
        }
    }

    private function validateModel(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'engine' => $request->filled('engine') ? trim((string) $request->input('engine')) : null,
            'fuel_type' => $request->filled('fuel_type') ? trim((string) $request->input('fuel_type')) : null,
            'transmission' => $request->filled('transmission') ? trim((string) $request->input('transmission')) : null,
            'body_type' => $request->filled('body_type') ? trim((string) $request->input('body_type')) : null,
            'drive_type' => $request->filled('drive_type') ? trim((string) $request->input('drive_type')) : null,
            'origin' => $request->filled('origin') ? trim((string) $request->input('origin')) : null,
        ]);

        return $request->validate(
            [
                'brand_id' => ['required', 'exists:brands,brand_id'],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('car_models', 'name')
                        ->where(fn($query) => $query->where('brand_id', $request->brand_id))
                        ->ignore($ignoreId),
                ],
                'engine' => ['nullable', 'string', 'max:255'],
                'fuel_type' => ['nullable', 'string', 'max:100'],
                'transmission' => ['nullable', 'string', 'max:100'],
                'body_type' => ['nullable', 'string', 'max:100'],
                'drive_type' => ['nullable', 'string', 'max:100'],
                'seats' => ['nullable', 'integer', 'min:1', 'max:100'],
                'doors' => ['nullable', 'integer', 'min:1', 'max:20'],
                'origin' => ['nullable', 'string', 'max:100'],
            ],
            [
                'required' => 'Vui lòng nhập :attribute.',
                'exists' => ':attribute đã chọn không tồn tại.',
                'unique' => ':attribute này đã tồn tại trong hãng xe đã chọn.',
                'string' => ':attribute phải là chuỗi ký tự.',
                'integer' => ':attribute phải là số nguyên.',
                'min' => ':attribute không được nhỏ hơn :min.',
                'max.string' => ':attribute không được vượt quá :max ký tự.',
                'max.integer' => ':attribute không được lớn hơn :max.',
            ],
            [
                'brand_id' => 'hãng xe',
                'name' => 'tên model',
                'engine' => 'động cơ',
                'fuel_type' => 'nhiên liệu',
                'transmission' => 'hộp số',
                'body_type' => 'kiểu dáng',
                'drive_type' => 'dẫn động',
                'seats' => 'số chỗ',
                'doors' => 'số cửa',
                'origin' => 'xuất xứ',
            ]
        );
    }
}
