<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // 1. Hiển thị trang hồ sơ
    public function index()
    {
        $user = Auth::user(); // Lấy thông tin người dùng đang đăng nhập
        return view('profile.index', compact('user'));
    }

    // 2. Xử lý cập nhật thông tin
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            // Nếu có nhập mật khẩu mới, thì mật khẩu hiện tại là bắt buộc
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed', // confirmed yêu cầu trường new_password_confirmation
        ]);

        // Cập nhật thông tin cơ bản
        $user->name = $request->name;
        $user->phone = $request->phone;

        // Xử lý đổi mật khẩu (Nếu người dùng có nhập mật khẩu mới)
        if ($request->filled('new_password')) {
            // Kiểm tra mật khẩu cũ xem có đúng không
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác!']);
            }
            // Đổi thành mật khẩu mới
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Đã cập nhật hồ sơ thành công!');
    }
}
