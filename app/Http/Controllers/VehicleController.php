<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q', ''));

        $query = Vehicle::query()->orderByDesc('year')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate(9)->withQueryString();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        Vehicle::create($request->all());
        return redirect()->route('vehicles.index');
    }

    public function show(Vehicle $vehicle): View
    {
        return view('vehicles.show', [
            'vehicle' => $vehicle,
        ]);
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update($request->all());

        return redirect()->route('vehicles.index');
    }
}
