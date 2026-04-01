<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
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

    public function show(Vehicle $vehicle): View
    {
        return view('vehicles.show', [
            'vehicle' => $vehicle,
        ]);
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'min:1990', 'max:'.((int) date('Y') + 1)],
            'price' => ['required', 'integer', 'min:0'],
            'mileage_km' => ['nullable', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:80'],
            'transmission' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');

        $vehicle->update($data);

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Đã cập nhật thông tin xe.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Đã xóa xe khỏi danh sách.');
    }
}
