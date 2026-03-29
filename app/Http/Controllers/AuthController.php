<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $data=$request->only('email','password');
        if (Auth::attempt($data)) {
            return redirect()->route('home');
        }

        return back()->with('error', 'Sai tài khoản hoặc mật khẩu');
    }

    // Đăng xuất
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
