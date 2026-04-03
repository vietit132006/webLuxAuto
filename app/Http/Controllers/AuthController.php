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
        return view('login');
    }

    public function login(Request $request)
    {
        // Thử đăng nhập
        if (Auth::attempt($request->only('email', 'password'))) {

            // DÙNG LỆNH NÀY ĐỂ BẮT BỆNH:
            // dd('ĐĂNG NHẬP THÀNH CÔNG!', 'ID của User là:', Auth::user()->id);

            $request->session()->regenerate();
            return redirect()->route('cars.index');
        }

        // NẾU SAI TÀI KHOẢN/MẬT KHẨU SẼ IN RA DÒNG NÀY:
        // dd('ĐĂNG NHẬP THẤT BẠI! Lỗi do sai Email hoặc Mật khẩu.');

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
