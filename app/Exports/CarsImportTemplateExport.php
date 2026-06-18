<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CarsImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'car_model_id',
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
            'current_location',
            'color',
            'interior_color',
            'stock_quantity',
            'stock',
            'status',
            'is_featured',
            'video_url',
            'description',
        ];
    }

    public function array(): array
    {
        return [];
    }
}
