<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Tham số $roles sẽ nhận vào danh sách các quyền được phép truy cập
     * Ví dụ: 'admin', 'staff'
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Chưa đăng nhập thì đuổi ra trang login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Lấy chức vụ của người dùng hiện tại
        $userRole = Auth::user()->role;

        // 3. Nếu chức vụ của họ nằm trong danh sách được phép ($roles) thì cho qua
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 4. Nếu không có quyền, báo lỗi 403 (Cấm truy cập)
        abort(403, 'Bạn không có quyền truy cập vào chức năng này!');
    }
}
