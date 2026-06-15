<?php

namespace App\Exports;

use App\Models\Car;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection(): Collection
    {
        return Car::query()
            ->with('carModel.brand')
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tên xe',
            'Model',
            'VIN',
            'Biển số',
            'Số lượng tồn',
            'Trạng thái',
            'Vị trí',
            'Giá trị tồn kho ước tính',
            'Ngày cập nhật',
        ];
    }

    public function map($car): array
    {
        $stockQuantity = (int) ($car->stock_quantity ?? $car->stock ?? 0);
        $unitValue = (int) ($car->sale_price ?? $car->list_price ?? $car->price ?? 0);

        return [
            $car->name,
            $this->modelName($car),
            $car->vin,
            $car->license_plate,
            $stockQuantity,
            $this->statusText($car->status),
            $car->current_location,
            $stockQuantity * $unitValue,
            $car->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private function modelName(Car $car): string
    {
        $brandName = $car->carModel?->brand?->name;
        $modelName = $car->carModel?->name;

        return trim(($brandName ? "{$brandName} - " : '') . ($modelName ?? ''));
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
