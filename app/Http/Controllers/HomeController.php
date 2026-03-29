<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Vehicle::query()
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        return view('home', [
            'featuredVehicles' => $featured,
        ]);
    }
    //Thêm sửa 
    
}