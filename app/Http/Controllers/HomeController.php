<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Car::query()
            ->where('is_featured', 1)
            ->latest()
            ->take(6)
            ->get();
        // dd($featured->toArray());
        return view('client.home', [
            'featuredCars' => $featured,
        ]);
    }
}
