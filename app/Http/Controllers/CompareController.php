<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function index(Request $request): View
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(4)
            ->values()
            ->all();

        $cars = collect();
        if ($ids !== []) {
            $cars = Car::with('brand')
                ->whereIn('car_id', $ids)
                ->get()
                ->sortBy(fn ($c) => array_search($c->car_id, $ids, true));
        }

        return view('client.compare', [
            'cars' => $cars,
            'ids' => $ids,
        ]);
    }
}
