<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class ResetPasswordController extends Controller
{
    // 1. Hiện form nhập Email
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // 2. Xử lý gửi link vào Email
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Chúng tôi đã gửi link đặt lại mật khẩu vào email của bạn!');
        }

        return back()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
    }

    // 3. Hiện form nhập Mật khẩu mới (Khi bấm vào link trong email)
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // 4. Xử lý đổi mật khẩu mới
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Mật khẩu của bạn đã được đặt lại thành công! Vui lòng đăng nhập.');
        }

        return back()->withErrors(['email' => 'Mã khôi phục không hợp lệ hoặc đã hết hạn.']);
    }
}
