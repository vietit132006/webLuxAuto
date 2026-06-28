<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'country' => trim((string) $request->input('country', '')),
            'status' => (string) $request->input('status', ''),
        ];

        if (!in_array($filters['status'], ['', 'active', 'inactive'], true)) {
            $filters['status'] = '';
        }

        $query = Brand::query()
            ->withCount([
                'carModels',
                'cars',
                'cars as available_cars_count' => function (Builder $query): void {
                    $query->availableForSale();
                },
            ]);

        if ($filters['q'] !== '') {
            $query->where('name', 'like', '%' . $filters['q'] . '%');
        }

        if ($filters['country'] !== '') {
            $query->where('country', $filters['country']);
        }

        if ($filters['status'] === 'active') {
            $query->where('is_active', true);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('is_active', false);
        }

        $brands = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $countries = Brand::query()
            ->whereNotNull('country')
            ->where('country', '<>', '')
            ->select('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('admin.brands.index', compact('brands', 'countries', 'filters'));
    }

    public function create()
    {
        return view('admin.brands.form');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $storedLogo = null;

        $croppedLogo = $this->storeCroppedLogo($data['logo_cropped_data'] ?? null);
        unset($data['logo_cropped_data']);

        if ($croppedLogo) {
            $storedLogo = $croppedLogo;
            $data['logo'] = $storedLogo;
        } elseif ($request->hasFile('logo')) {
            $storedLogo = $request->file('logo')->store('brands/logos', 'public');
            $data['logo'] = $storedLogo;
        }

        try {
            Brand::create($data);
        } catch (Throwable $e) {
            if ($storedLogo) {
                Storage::disk('public')->delete($storedLogo);
            }

            return back()
                ->withInput()
                ->with('error', 'Không thể lưu hãng xe: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Đã thêm hãng xe thành công.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $data = $this->validatedData($request, $brand);
        $oldLogo = null;
        $storedLogo = null;

        $croppedLogo = $this->storeCroppedLogo($data['logo_cropped_data'] ?? null);
        unset($data['logo_cropped_data']);

        if ($croppedLogo) {
            $oldLogo = $brand->logo;
            $storedLogo = $croppedLogo;
            $data['logo'] = $storedLogo;
        } elseif ($request->hasFile('logo')) {
            $oldLogo = $brand->logo;
            $storedLogo = $request->file('logo')->store('brands/logos', 'public');
            $data['logo'] = $storedLogo;
        }

        try {
            $brand->update($data);
        } catch (Throwable $e) {
            if ($storedLogo) {
                Storage::disk('public')->delete($storedLogo);
            }

            return back()
                ->withInput()
                ->with('error', 'Không thể cập nhật hãng xe: ' . $e->getMessage());
        }

        if ($oldLogo && $oldLogo !== $storedLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Đã cập nhật hãng xe thành công.');
    }

    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->forceFill(['is_active' => ! $brand->is_active])->save();

        return back()->with(
            'success',
            $brand->is_active
                ? 'Đã bật hiển thị hãng xe.'
                : 'Đã tạm ẩn hãng xe khỏi frontend.'
        );
    }

    public function destroy($id)
    {
        $brand = Brand::query()
            ->withCount(['carModels', 'cars'])
            ->findOrFail($id);

        if ($brand->car_models_count > 0 || $brand->cars_count > 0) {
            return back()->with(
                'error',
                'Không thể xóa hãng vì đang có model hoặc xe thuộc hãng này. Bạn có thể tạm ẩn hãng.'
            );
        }

        $logo = $brand->logo;
        $brand->delete();

        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Đã xóa hãng xe thành công.');
    }

    private function validatedData(Request $request, ?Brand $brand = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'country' => $this->nullableTrim($request->input('country')),
            'description' => $this->nullableTrim($request->input('description')),
            'seo_title' => $this->nullableTrim($request->input('seo_title')),
            'seo_description' => $this->nullableTrim($request->input('seo_description')),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        $rawSlug = trim((string) $request->input('slug', ''));
        $slugWasProvided = $rawSlug !== '';

        if ($slugWasProvided) {
            $slug = Str::slug($rawSlug);
        } elseif ($brand?->slug) {
            $slug = $brand->slug;
        } else {
            $slug = $this->uniqueSlug($request->input('name'), $brand?->brand_id);
        }

        $request->merge(['slug' => $slugWasProvided ? ($slug ?: $rawSlug) : ($slug ?: null)]);

        $brandId = $brand?->brand_id;

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'country' => ['nullable', 'string', 'max:100'],
                'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
                'logo_cropped_data' => ['nullable', 'string', 'max:3500000'],
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    Rule::unique('brands', 'slug')->ignore($brandId, 'brand_id'),
                ],
                'description' => ['nullable', 'string', 'max:5000'],
                'is_active' => ['required', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:-999999', 'max:999999'],
                'seo_title' => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string', 'max:500'],
            ],
            [
                'required' => 'Vui lòng nhập :attribute.',
                'string' => ':attribute phải là chuỗi ký tự.',
                'integer' => ':attribute phải là số nguyên.',
                'boolean' => ':attribute không hợp lệ.',
                'unique' => ':attribute đã tồn tại, vui lòng dùng giá trị khác.',
                'regex' => ':attribute chỉ được gồm chữ thường, số và dấu gạch ngang.',
                'file' => ':attribute phải là tệp hợp lệ.',
                'mimes' => ':attribute phải có định dạng jpg, jpeg, png, webp hoặc svg.',
                'max.file' => ':attribute không được vượt quá 2MB.',
                'logo_cropped_data.max' => 'Dữ liệu logo sau khi cắt quá lớn, vui lòng chọn ảnh nhỏ hơn.',
                'max.string' => ':attribute không được vượt quá :max ký tự.',
                'min' => ':attribute không được nhỏ hơn :min.',
                'max' => ':attribute không được lớn hơn :max.',
            ],
            [
                'name' => 'tên hãng',
                'country' => 'quốc gia',
                'logo' => 'logo hãng',
                'logo_cropped_data' => 'logo đã cắt',
                'slug' => 'slug',
                'description' => 'mô tả',
                'is_active' => 'trạng thái hiển thị',
                'sort_order' => 'thứ tự hiển thị',
                'seo_title' => 'SEO title',
                'seo_description' => 'SEO description',
            ]
        );
    }

    private function storeCroppedLogo(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $value, $matches)) {
            throw ValidationException::withMessages([
                'logo' => 'Dữ liệu logo sau khi cắt không hợp lệ.',
            ]);
        }

        $base64 = substr($value, strpos($value, ',') + 1);
        $binary = base64_decode($base64, true);

        if ($binary === false || strlen($binary) > 3 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'logo' => 'Dữ liệu logo sau khi cắt quá lớn hoặc không hợp lệ.',
            ]);
        }

        $path = 'brands/logos/' . (string) Str::uuid() . '.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'brand';
        $slug = $base;
        $suffix = 2;

        while (
            Brand::query()
                ->where('slug', $slug)
                ->when($ignoreId, function (Builder $query) use ($ignoreId): void {
                    $query->where('brand_id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
