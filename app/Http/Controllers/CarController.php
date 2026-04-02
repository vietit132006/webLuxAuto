<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request): View
    {
        // dd($request->all());
        $search = trim((string) $request->get('q', ''));

        $query = Car::query()->orderByDesc('year')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand_id', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $cars = $query->paginate(9)->withQueryString();

        return view('cars.index', [
            'cars' => $cars,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('cars.create');
    }

    public function store(Request $request)
    {
        Car::create($request->all());
        return redirect()->route('cars.index');
    }

    public function show(Car $car): View
    {
        return view('cars.show', [
            'car' => $car,
        ]);
    }

    public function edit($id)
    {
        $car = Car::findOrFail($id);
        return view('cars.edit', compact('car'));
    }

    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);
        $car->update($request->all());

        return redirect()->route('cars.index');
    }
}
