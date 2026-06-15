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
            'model_id',
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
    }

    public function array(): array
    {
        return [];
    }
}
