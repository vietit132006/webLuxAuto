<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Car::query()
            ->where('car_id', true)
            ->latest()
            ->take(6)
            ->get();

        return view('client.home', [
            'featuredCars' => $featured,
        ]);
    }
}
