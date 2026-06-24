<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CarsImport implements ToCollection, WithHeadingRow
{
    private const CONDITION_VALUES = ['new', 'used', 'display', 'test_drive'];
    private const STATUS_VALUES = [1, 2, 3];

    private int $importedCount = 0;
    private array $errors = [];

    public function __construct(private readonly ?StockMovementService $stockMovementService = null)
    {
    }

    public function collection(Collection $rows): void
    {
        $this->errors = [];
        $this->importedCount = 0;
        $validRows = [];
        $seenVins = [];
        $seenLicensePlates = [];
        $seenInternalCodes = [];
        $currentYear = (int) date('Y');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalizeRow($row instanceof Collection ? $row->toArray() : (array) $row);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $data['vehicle_condition'] = $this->normalizeCondition($data['vehicle_condition'] ?? null);
            $data['status_value'] = $this->normalizeStatus($data['status'] ?? null);
            $data['is_featured_value'] = $this->normalizeBoolean($data['is_featured'] ?? null);

            $validator = Validator::make($data, [
                'car_model_id' => ['required', 'integer', Rule::exists('car_models', 'id')],
                'name' => ['required', 'string', 'max:255'],
                'vin' => ['required', 'string', 'max:17', Rule::unique('cars', 'vin')],
                'license_plate' => ['nullable', 'string', 'max:20', Rule::unique('cars', 'license_plate')],
                'internal_code' => ['nullable', 'string', 'max:50', Rule::unique('cars', 'internal_code')],
                'price' => ['nullable', 'numeric', 'min:0'],
                'list_price' => ['required', 'numeric', 'min:0'],
                'sale_price' => ['nullable', 'numeric', 'min:0'],
                'registration_fee' => ['nullable', 'numeric', 'min:0'],
                'license_plate_fee' => ['nullable', 'numeric', 'min:0'],
                'inspection_fee' => ['nullable', 'numeric', 'min:0'],
                'insurance_fee' => ['nullable', 'numeric', 'min:0'],
                'other_fees' => ['nullable', 'numeric', 'min:0'],
                'estimated_rolling_price' => ['nullable', 'numeric', 'min:0'],
                'registration_area' => ['nullable', 'string', 'max:100'],
                'color' => ['nullable', 'string', 'max:50'],
                'interior_color' => ['nullable', 'string', 'max:50'],
                'year' => ['required', 'integer', 'min:1000', 'max:' . $currentYear],
                'mileage_km' => ['nullable', 'integer', 'min:0'],
                'owner_count' => ['nullable', 'integer', 'min:0', 'max:10'],
                'stock_in_date' => ['nullable', 'date'],
                'on_road_date' => ['nullable', 'date'],
                'vehicle_condition' => ['nullable', Rule::in(self::CONDITION_VALUES)],
                'current_location' => ['nullable', 'string', 'max:255'],
                'stock_quantity' => ['nullable', 'integer', 'min:0'],
                'stock' => ['nullable', 'integer', 'min:0'],
                'status_value' => ['nullable', Rule::in(self::STATUS_VALUES)],
                'is_featured_value' => ['nullable', 'boolean'],
                'image' => ['nullable', 'string', 'max:255'],
                'video_url' => ['nullable', 'url', 'max:255'],
                'video_file' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:10000'],
            ], [
                'required' => ':attribute là bắt buộc.',
                'integer' => ':attribute phải là số nguyên.',
                'numeric' => ':attribute phải là số.',
                'min' => ':attribute không hợp lệ.',
                'max' => ':attribute vượt quá giới hạn cho phép.',
                'exists' => ':attribute không tồn tại trong bảng car_models.',
                'unique' => ':attribute đã tồn tại trên hệ thống.',
                'in' => ':attribute không hợp lệ.',
            ], [
                'car_model_id' => 'car_model_id',
                'name' => 'name',
                'vin' => 'vin',
                'license_plate' => 'license_plate',
                'internal_code' => 'internal_code',
                'price' => 'price',
                'list_price' => 'list_price',
                'sale_price' => 'sale_price',
                'registration_fee' => 'registration_fee',
                'license_plate_fee' => 'license_plate_fee',
                'inspection_fee' => 'inspection_fee',
                'insurance_fee' => 'insurance_fee',
                'other_fees' => 'other_fees',
                'estimated_rolling_price' => 'estimated_rolling_price',
                'registration_area' => 'registration_area',
                'color' => 'color',
                'interior_color' => 'interior_color',
                'year' => 'year',
                'mileage_km' => 'mileage_km',
                'owner_count' => 'owner_count',
                'stock_in_date' => 'stock_in_date',
                'on_road_date' => 'on_road_date',
                'vehicle_condition' => 'vehicle_condition',
                'current_location' => 'current_location',
                'stock_quantity' => 'stock_quantity',
                'stock' => 'stock',
                'status_value' => 'status',
                'is_featured_value' => 'is_featured',
                'image' => 'image',
                'video_url' => 'video_url',
                'video_file' => 'video_file',
                'description' => 'description',
            ]);

            $validator->after(function ($validator) use (&$seenVins, &$seenLicensePlates, &$seenInternalCodes, $data, $rowNumber) {
                if (!empty($data['vin'])) {
                    $vin = Str::upper((string) $data['vin']);
                    if (isset($seenVins[$vin])) {
                        $validator->errors()->add('vin', "vin bị trùng với dòng {$seenVins[$vin]} trong file.");
                    }
                    $seenVins[$vin] = $rowNumber;
                }

                if (!empty($data['license_plate'])) {
                    $plate = Str::upper((string) $data['license_plate']);
                    if (isset($seenLicensePlates[$plate])) {
                        $validator->errors()->add('license_plate', "license_plate bị trùng với dòng {$seenLicensePlates[$plate]} trong file.");
                    }
                    $seenLicensePlates[$plate] = $rowNumber;
                }

                if (!empty($data['internal_code'])) {
                    $internalCode = Str::upper((string) $data['internal_code']);
                    if (isset($seenInternalCodes[$internalCode])) {
                        $validator->errors()->add('internal_code', "internal_code duplicated with row {$seenInternalCodes[$internalCode]} in file.");
                    }
                    $seenInternalCodes[$internalCode] = $rowNumber;
                }

                if (
                    $data['sale_price'] !== null
                    && $data['list_price'] !== null
                    && is_numeric($data['sale_price'])
                    && is_numeric($data['list_price'])
                    && (float) $data['sale_price'] > (float) $data['list_price']
                ) {
                    $validator->errors()->add('sale_price', 'sale_price không được lớn hơn list_price.');
                }
            });

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $this->errors[] = "Dòng {$rowNumber}: {$message}";
                }

                continue;
            }

            $validRows[] = $this->toCarPayload($data);
        }

        if ($this->errors !== []) {
            throw ValidationException::withMessages([
                'file' => $this->errors,
            ]);
        }

        if ($validRows === []) {
            throw ValidationException::withMessages([
                'file' => ['File import không có dòng dữ liệu hợp lệ.'],
            ]);
        }

        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $payload) {
                $car = Car::create($payload);
                $stockQuantity = (int) ($car->stock_quantity ?? $car->stock ?? 0);

                $this->stockMovementService?->recordMovement(
                    $car,
                    0,
                    $stockQuantity,
                    $stockQuantity,
                    StockMovement::ACTION_IMPORT,
                    'Import Excel xe vào kho.'
                );

                $this->importedCount++;
            }
        });
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    private function normalizeRow(array $row): array
    {
        $fields = [
            'car_model_id',
            'model',
            'name',
            'vin',
            'license_plate',
            'internal_code',
            'price',
            'list_price',
            'sale_price',
            'registration_fee',
            'license_plate_fee',
            'inspection_fee',
            'insurance_fee',
            'other_fees',
            'estimated_rolling_price',
            'registration_area',
            'color',
            'interior_color',
            'year',
            'mileage_km',
            'owner_count',
            'stock_in_date',
            'on_road_date',
            'vehicle_condition',
            'current_location',
            'stock_quantity',
            'stock',
            'status',
            'is_featured',
            'image',
            'video_url',
            'video_file',
            'description',
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->blankToNull($this->valueFromRow($row, $field));
        }

        if ($data['car_model_id'] === null && $data['model'] !== null) {
            $data['car_model_id'] = $this->resolveModelId((string) $data['model']);
        }

        if ($data['vin'] !== null) {
            $data['vin'] = Str::upper((string) $data['vin']);
        }

        if ($data['license_plate'] !== null) {
            $data['license_plate'] = Str::upper((string) $data['license_plate']);
        }

        return $data;
    }

    private function valueFromRow(array $row, string $field): mixed
    {
        foreach ($this->aliasesFor($field) as $alias) {
            if (array_key_exists($alias, $row)) {
                return $row[$alias];
            }
        }

        return null;
    }

    private function aliasesFor(string $field): array
    {
        return match ($field) {
            'car_model_id' => ['car_model_id', 'model_id', 'id_model', 'ma_model', 'id_dong_xe', 'ma_dong_xe'],
            'model' => ['model', 'dong_xe', 'mau_xe'],
            'name' => ['name', 'ten_xe', 'ten', 'phien_ban'],
            'vin' => ['vin', 'so_vin', 'so_khung'],
            'license_plate' => ['license_plate', 'bien_so', 'bien_so_xe'],
            'internal_code' => ['internal_code', 'ma_noi_bo', 'ma_xe_noi_bo'],
            'price' => ['price', 'actual_price', 'gia_ban_thuc_te', 'gia_ban'],
            'list_price' => ['list_price', 'gia_niem_yet'],
            'sale_price' => ['sale_price', 'gia_khuyen_mai'],
            'registration_fee' => ['registration_fee', 'phi_truoc_ba'],
            'license_plate_fee' => ['license_plate_fee', 'phi_bien_so'],
            'inspection_fee' => ['inspection_fee', 'phi_dang_kiem'],
            'insurance_fee' => ['insurance_fee', 'phi_bao_hiem'],
            'other_fees' => ['other_fees', 'phi_khac', 'phi_dich_vu_khac'],
            'estimated_rolling_price' => ['estimated_rolling_price', 'gia_lan_banh_du_kien'],
            'registration_area' => ['registration_area', 'khu_vuc_dang_ky'],
            'color' => ['color', 'exterior_color', 'mau_ngoai_that'],
            'interior_color' => ['interior_color', 'mau_noi_that'],
            'year' => ['year', 'manufacture_year', 'nam_san_xuat'],
            'mileage_km' => ['mileage_km', 'mileage', 'so_km'],
            'owner_count' => ['owner_count', 'so_doi_chu'],
            'stock_in_date' => ['stock_in_date', 'ngay_nhap_kho'],
            'on_road_date' => ['on_road_date', 'ngay_lan_banh'],
            'vehicle_condition' => ['vehicle_condition', 'condition', 'tinh_trang'],
            'current_location' => ['current_location', 'location', 'vi_tri'],
            'stock_quantity' => ['stock_quantity', 'ton_kho', 'so_luong_ton'],
            'stock' => ['stock'],
            'status' => ['status', 'trang_thai'],
            'is_featured' => ['is_featured', 'noi_bat'],
            'image' => ['image', 'anh_dai_dien'],
            'video_url' => ['video_url', 'youtube_url', 'duong_dan_video'],
            'video_file' => ['video_file', 'file_video'],
            'description' => ['description', 'mo_ta'],
            default => [$field],
        };
    }

    private function resolveModelId(string $modelText): ?int
    {
        $modelText = trim($modelText);
        if ($modelText === '') {
            return null;
        }

        if (is_numeric($modelText)) {
            return (int) $modelText;
        }

        $candidates = [$modelText];
        if (str_contains($modelText, ' - ')) {
            $parts = array_map('trim', explode(' - ', $modelText));
            $lastPart = end($parts);
            if ($lastPart !== false && $lastPart !== '') {
                $candidates[] = $lastPart;
            }
        }

        foreach (array_unique($candidates) as $candidate) {
            $modelId = CarModel::query()
                ->whereRaw('LOWER(name) = ?', [Str::lower($candidate)])
                ->value('id');

            if ($modelId !== null) {
                return (int) $modelId;
            }
        }

        return null;
    }

    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function blankToNull(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }

    private function normalizeCondition(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return 'new';
        }

        $key = $this->key((string) $value);

        return [
            'new' => 'new',
            'moi' => 'new',
            'used' => 'used',
            'cu' => 'used',
            'display' => 'display',
            'trung_bay' => 'display',
            'test_drive' => 'test_drive',
            'lai_thu' => 'test_drive',
        ][$key] ?? $key;
    }

    private function normalizeStatus(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return 1;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $key = $this->key((string) $value);

        return [
            'available' => 1,
            'san_sang' => 1,
            'dang_ban' => 1,
            'con_hang' => 1,
            'deposit' => 2,
            'da_coc' => 2,
            'coc' => 2,
            'sold' => 3,
            'da_ban' => 3,
            'ban' => 3,
        ][$key] ?? $key;
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return match ((int) $value) {
                0 => false,
                1 => true,
                default => $value,
            };
        }

        $key = $this->key((string) $value);

        return [
            'true' => true,
            'yes' => true,
            'co' => true,
            'featured' => true,
            'noi_bat' => true,
            'false' => false,
            'no' => false,
            'khong' => false,
            'normal' => false,
            'binh_thuong' => false,
        ][$key] ?? $value;
    }

    private function key(string $value): string
    {
        return preg_replace(
            '/_+/',
            '_',
            Str::of($value)->ascii()->lower()->replace([' ', '-'], '_')->toString()
        );
    }

    private function toCarPayload(array $data): array
    {
        $listPrice = $this->moneyValue($data['list_price']);
        $salePrice = $this->nullableMoneyValue($data['sale_price']);
        $actualPrice = $this->nullableMoneyValue($data['price']) ?? $salePrice ?? $listPrice;
        $registrationFee = $this->moneyValue($data['registration_fee']);
        $licensePlateFee = $this->moneyValue($data['license_plate_fee']);
        $inspectionFee = $this->moneyValue($data['inspection_fee']);
        $insuranceFee = $this->moneyValue($data['insurance_fee']);
        $otherFees = $this->moneyValue($data['other_fees']);
        $feeTotal = $registrationFee + $licensePlateFee + $inspectionFee + $insuranceFee + $otherFees;
        $estimatedRollingPrice = $this->nullableMoneyValue($data['estimated_rolling_price']) ?? ($actualPrice + $feeTotal);
        $mileageKm = (int) round((float) ($data['mileage_km'] ?? 0));
        $stockQuantity = (int) round((float) ($data['stock_quantity'] ?? $data['stock'] ?? 1));
        $stock = $stockQuantity;

        return [
            'car_model_id' => (int) $data['car_model_id'],
            'name' => (string) $data['name'],
            'vin' => (string) $data['vin'],
            'license_plate' => $data['license_plate'],
            'internal_code' => $data['internal_code'],
            'price' => $actualPrice,
            'list_price' => $listPrice,
            'sale_price' => $salePrice,
            'registration_fee' => $registrationFee,
            'license_plate_fee' => $licensePlateFee,
            'inspection_fee' => $inspectionFee,
            'insurance_fee' => $insuranceFee,
            'other_fees' => $otherFees,
            'estimated_rolling_price' => $estimatedRollingPrice,
            'registration_area' => $data['registration_area'],
            'year' => (int) $data['year'],
            'mileage_km' => $mileageKm,
            'owner_count' => $data['owner_count'] !== null
                ? (int) $data['owner_count']
                : ($mileageKm === 0 ? 0 : 1),
            'stock_in_date' => $data['stock_in_date'],
            'on_road_date' => $data['on_road_date'],
            'vehicle_condition' => $data['vehicle_condition'] ?? 'new',
            'stock_quantity' => $stockQuantity,
            'stock' => $stock,
            'status' => $data['status_value'] ?? 1,
            'current_location' => $data['current_location'],
            'color' => $data['color'],
            'interior_color' => $data['interior_color'],
            'description' => $data['description'],
            'image' => $data['image'],
            'video_url' => $data['video_url'],
            'video_file' => $data['video_file'],
            'is_featured' => (bool) ($data['is_featured_value'] ?? false),
        ];
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
}
