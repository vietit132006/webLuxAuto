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
            'car_id',
            'internal_code',
            'car_model_id',
            'brand_name',
            'car_model_name',
            'name',
            'vin',
            'license_plate',
            'year',
            'color',
            'interior_color',
            'vehicle_condition',
            'vehicle_condition_label',
            'status',
            'status_label',
            'current_location',
            'stock_quantity',
            'stock',
            'physical_stock',
            'reserved_quantity',
            'available_stock',
            'unit_inventory_price',
            'inventory_value',
            'price',
            'list_price',
            'sale_price',
            'estimated_rolling_price',
            'rolling_inventory_value',
            'registration_area',
            'stock_in_date',
            'stock_age_days',
            'on_road_date',
            'mileage_km',
            'owner_count',
            'updated_at',
        ];
    }

    public function map($car): array
    {
        $stockQuantity = $this->stockQuantity($car);
        $reservedQuantity = $car->reservedStock();
        $availableStock = $car->availableStock();
        $unitValue = (int) ($car->sale_price ?? $car->list_price ?? $car->price ?? 0);
        $rollingValue = (int) ($car->estimated_rolling_price ?? 0);

        return [
            $car->car_id,
            $car->internal_code,
            $car->car_model_id,
            $car->carModel?->brand?->name,
            $car->carModel?->name,
            $car->name,
            $car->vin,
            $car->license_plate,
            $car->year,
            $car->color,
            $car->interior_color,
            $car->vehicle_condition,
            $this->conditionLabel($car->vehicle_condition),
            $car->status,
            $this->statusLabel($car->status),
            $car->current_location,
            $stockQuantity,
            $car->stock,
            $stockQuantity,
            $reservedQuantity,
            $availableStock,
            $unitValue,
            $stockQuantity * $unitValue,
            $car->price,
            $car->list_price,
            $car->sale_price,
            $car->estimated_rolling_price,
            $stockQuantity * $rollingValue,
            $car->registration_area,
            $this->dateValue($car->stock_in_date),
            $this->stockAgeDays($car->stock_in_date),
            $this->dateValue($car->on_road_date),
            $car->mileage_km,
            $car->owner_count,
            $this->dateTimeValue($car->updated_at),
        ];
    }

    private function stockQuantity(Car $car): int
    {
        return $car->physicalStock();
    }

    private function conditionLabel(?string $condition): string
    {
        return match ($condition) {
            'used' => 'Used',
            'display' => 'Display',
            'test_drive' => 'Test drive',
            default => 'New',
        };
    }

    private function statusLabel(mixed $status): string
    {
        return match ((int) $status) {
            2 => 'Deposit',
            3 => 'Sold',
            default => 'Available',
        };
    }

    private function stockAgeDays(mixed $value): ?int
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }

        return (int) $value->diff(now()->startOfDay())->format('%r%a');
    }

    private function dateValue(mixed $value): ?string
    {
        return $this->formatDate($value, 'Y-m-d');
    }

    private function dateTimeValue(mixed $value): ?string
    {
        return $this->formatDate($value, 'Y-m-d H:i:s');
    }

    private function formatDate(mixed $value, string $format): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format($format);
        }

        return (string) $value;
    }
}
