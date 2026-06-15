<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\CarModel;
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

    public function collection(Collection $rows): void
    {
        $this->errors = [];
        $this->importedCount = 0;
        $validRows = [];
        $seenVins = [];
        $seenLicensePlates = [];
        $currentYear = (int) date('Y');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalizeRow($row instanceof Collection ? $row->toArray() : (array) $row);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $data['vehicle_condition'] = $this->normalizeCondition($data['condition'] ?? null);
            $data['status_value'] = $this->normalizeStatus($data['status'] ?? null);

            $validator = Validator::make($data, [
                'model_id' => ['required', 'integer', Rule::exists('car_models', 'id')],
                'name' => ['required', 'string', 'max:255'],
                'vin' => ['required', 'string', 'max:17', Rule::unique('cars', 'vin')],
                'license_plate' => ['nullable', 'string', 'max:20', Rule::unique('cars', 'license_plate')],
                'list_price' => ['required', 'numeric', 'min:0'],
                'sale_price' => ['nullable', 'numeric', 'min:0'],
                'exterior_color' => ['nullable', 'string', 'max:50'],
                'interior_color' => ['nullable', 'string', 'max:50'],
                'manufacture_year' => ['required', 'integer', 'min:1000', 'max:' . $currentYear],
                'mileage' => ['nullable', 'integer', 'min:0'],
                'vehicle_condition' => ['nullable', Rule::in(self::CONDITION_VALUES)],
                'stock_quantity' => ['nullable', 'integer', 'min:0'],
                'status_value' => ['nullable', Rule::in(self::STATUS_VALUES)],
                'location' => ['nullable', 'string', 'max:255'],
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
                'model_id' => 'model_id',
                'name' => 'name',
                'vin' => 'vin',
                'license_plate' => 'license_plate',
                'list_price' => 'list_price',
                'sale_price' => 'sale_price',
                'exterior_color' => 'exterior_color',
                'interior_color' => 'interior_color',
                'manufacture_year' => 'manufacture_year',
                'mileage' => 'mileage',
                'vehicle_condition' => 'condition',
                'stock_quantity' => 'stock_quantity',
                'status_value' => 'status',
                'location' => 'location',
                'description' => 'description',
            ]);

            $validator->after(function ($validator) use (&$seenVins, &$seenLicensePlates, $data, $rowNumber) {
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
                Car::create($payload);
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
            'model_id',
            'model',
            'name',
            'vin',
            'license_plate',
            'list_price',
            'sale_price',
            'exterior_color',
            'interior_color',
            'manufacture_year',
            'mileage',
            'condition',
            'stock_quantity',
            'status',
            'location',
            'description',
        ];

        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $this->blankToNull($this->valueFromRow($row, $field));
        }

        if ($data['model_id'] === null && $data['model'] !== null) {
            $data['model_id'] = $this->resolveModelId((string) $data['model']);
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
            'model_id' => ['model_id', 'car_model_id', 'id_model', 'ma_model', 'id_dong_xe', 'ma_dong_xe'],
            'model' => ['model', 'dong_xe', 'mau_xe'],
            'name' => ['name', 'ten_xe', 'ten', 'phien_ban'],
            'vin' => ['vin', 'so_vin', 'so_khung'],
            'license_plate' => ['license_plate', 'bien_so', 'bien_so_xe'],
            'list_price' => ['list_price', 'gia_niem_yet'],
            'sale_price' => ['sale_price', 'gia_khuyen_mai'],
            'exterior_color' => ['exterior_color', 'mau_ngoai_that', 'color'],
            'interior_color' => ['interior_color', 'mau_noi_that'],
            'manufacture_year' => ['manufacture_year', 'nam_san_xuat', 'year'],
            'mileage' => ['mileage', 'so_km', 'mileage_km'],
            'condition' => ['condition', 'tinh_trang', 'vehicle_condition'],
            'stock_quantity' => ['stock_quantity', 'ton_kho', 'so_luong_ton', 'stock'],
            'status' => ['status', 'trang_thai'],
            'location' => ['location', 'vi_tri', 'current_location'],
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
        $actualPrice = $salePrice ?? $listPrice;
        $stockQuantity = (int) round((float) ($data['stock_quantity'] ?? 1));

        return [
            'car_model_id' => (int) $data['model_id'],
            'name' => (string) $data['name'],
            'vin' => (string) $data['vin'],
            'license_plate' => $data['license_plate'],
            'price' => $actualPrice,
            'list_price' => $listPrice,
            'sale_price' => $salePrice,
            'registration_fee' => 0,
            'license_plate_fee' => 0,
            'inspection_fee' => 0,
            'insurance_fee' => 0,
            'other_fees' => 0,
            'estimated_rolling_price' => $actualPrice,
            'year' => (int) $data['manufacture_year'],
            'mileage_km' => (int) round((float) ($data['mileage'] ?? 0)),
            'vehicle_condition' => $data['vehicle_condition'] ?? 'new',
            'stock_quantity' => $stockQuantity,
            'stock' => $stockQuantity,
            'status' => $data['status_value'] ?? 1,
            'current_location' => $data['location'],
            'color' => $data['exterior_color'],
            'interior_color' => $data['interior_color'],
            'description' => $data['description'],
            'is_featured' => false,
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
