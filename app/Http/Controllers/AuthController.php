<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // LOGIN
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Thử đăng nhập
        if (Auth::attempt($request->only('email', 'password'))) {

            $request->session()->regenerate();

            // Lấy thông tin user vừa đăng nhập thành công
            $user = Auth::user();

            // Kiểm tra: Nếu là admin hoặc staff thì cho vào Dashboard
            if (in_array($user->role, ['admin', 'staff'])) {
                return redirect()->route('admin.dashboard');
            }

            // Nếu là khách hàng bình thường (customer), cho ra trang chủ / trang danh sách xe
            return redirect()->route('home');
        }

        // Trả về lỗi nếu sai email hoặc mật khẩu
        return back()->with('error', 'Sai tài khoản hoặc mật khẩu');
    }
    // LOGOUT
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    // REGISTER
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'required',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'customer',
            'status' => 1
        ]);

        return redirect('/login')->with('success', 'Đăng ký thành công!');
    }
}
