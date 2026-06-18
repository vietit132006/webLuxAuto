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
            'car_id',
            'car_model_id',
            'brand_name',
            'car_model_name',
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
            'year',
            'mileage_km',
            'owner_count',
            'stock_in_date',
            'on_road_date',
            'vehicle_condition',
            'vehicle_condition_label',
            'current_location',
            'color',
            'interior_color',
            'stock_quantity',
            'stock',
            'status',
            'status_label',
            'is_featured',
            'image',
            'video_url',
            'video_file',
            'description',
            'created_at',
            'updated_at',
        ];
    }

    public function map($car): array
    {
        return [
            $car->car_id,
            $car->car_model_id,
            $car->carModel?->brand?->name,
            $car->carModel?->name,
            $car->name,
            $car->vin,
            $car->license_plate,
            $car->internal_code,
            $car->price,
            $car->list_price,
            $car->sale_price,
            $car->registration_fee,
            $car->license_plate_fee,
            $car->inspection_fee,
            $car->insurance_fee,
            $car->other_fees,
            $car->estimated_rolling_price,
            $car->registration_area,
            $car->year,
            $car->mileage_km,
            $car->owner_count,
            $this->dateValue($car->stock_in_date),
            $this->dateValue($car->on_road_date),
            $car->vehicle_condition,
            $this->conditionLabel($car->vehicle_condition),
            $car->current_location,
            $car->color,
            $car->interior_color,
            $this->stockQuantity($car),
            $car->stock,
            $car->status,
            $this->statusLabel($car->status),
            (int) (bool) $car->is_featured,
            $car->image,
            $car->video_url,
            $car->video_file,
            $car->description,
            $this->dateTimeValue($car->created_at),
            $this->dateTimeValue($car->updated_at),
        ];
    }

    private function stockQuantity(Car $car): int
    {
        return (int) ($car->stock_quantity ?? $car->stock ?? 0);
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
