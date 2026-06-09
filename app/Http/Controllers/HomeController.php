<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Car::query()
            ->with(['brand', 'carModel.brand'])
            ->where('is_featured', 1)
            ->latest()
            ->take(6)
            ->get();

        return view('client.home', [
            'featuredCars' => $featured,
        ]);
    }
}
