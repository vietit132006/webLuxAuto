<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanViewStockHistory
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->canViewStockHistory()) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền xem lịch sử tồn kho.');
    }
}
