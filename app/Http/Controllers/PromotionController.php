<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $setting = Setting::where('key', 'promotions_content')->first();
        $content = $setting?->value ?? '';

        return view('client.promotions', compact('content'));
    }
}
