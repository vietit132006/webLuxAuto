<?php

namespace App\Exports;

use App\Models\Car;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CarsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection(): Collection
    {
        return Car::query()
            ->with('carModel.brand')
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'model_id',
            'Tên xe',
            'Model',
            'VIN',
            'Biển số',
            'Giá niêm yết',
            'Giá khuyến mãi',
            'Giá bán thực tế',
            'Màu ngoại thất',
            'Màu nội thất',
            'Năm sản xuất',
            'Số km',
            'Tình trạng',
            'Trạng thái',
            'Vị trí',
            'Tồn kho',
        ];
    }

    public function map($car): array
    {
        $listPrice = $car->list_price ?? $car->price;
        $salePrice = $car->sale_price;

        return [
            $car->car_model_id,
            $car->name,
            $this->modelName($car),
            $car->vin,
            $car->license_plate,
            $listPrice,
            $salePrice,
            $salePrice ?? $listPrice,
            $car->color,
            $car->interior_color,
            $car->year,
            $car->mileage_km,
            $this->conditionText($car->vehicle_condition),
            $this->statusText($car->status),
            $car->current_location,
            $this->stockQuantity($car),
        ];
    }

    private function modelName(Car $car): string
    {
        $brandName = $car->carModel?->brand?->name;
        $modelName = $car->carModel?->name;

        return trim(($brandName ? "{$brandName} - " : '') . ($modelName ?? ''));
    }

    private function stockQuantity(Car $car): int
    {
        return (int) ($car->stock_quantity ?? $car->stock ?? 0);
    }

    private function conditionText(?string $condition): string
    {
        return match ($condition) {
            'used' => 'Cũ',
            'display' => 'Trưng bày',
            'test_drive' => 'Lái thử',
            default => 'Mới',
        };
    }

    private function statusText(mixed $status): string
    {
        return match ((int) $status) {
            2 => 'Đã cọc',
            3 => 'Đã bán',
            default => 'Sẵn sàng',
        };
    }
}
