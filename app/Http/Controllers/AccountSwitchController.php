<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSwitchController extends Controller
{
    public const SESSION_SWITCHER_ID = 'account_switcher_user_id';
    public const SESSION_SWITCHER_NAME = 'account_switcher_user_name';
    public const SESSION_STARTED_AT = 'account_switcher_started_at';

    public function switchTo(Request $request)
    {
        $currentUser = $request->user();

        abort_unless($currentUser && in_array($currentUser->role, ['admin', 'staff'], true), 403);

        $request->validate([
            'user_id' => ['required', 'integer'],
        ]);

        $targetUser = User::whereKey((int) $request->input('user_id'))->firstOrFail();

        if ((int) $targetUser->getKey() === (int) $currentUser->getKey()) {
            return back()->with('error', 'Bạn đang ở đúng tài khoản này rồi.');
        }

        if (!$targetUser->status) {
            return back()->with('error', 'Không thể chuyển sang tài khoản đang bị khóa.');
        }

        if ($currentUser->role !== 'admin' && $targetUser->role !== 'customer') {
            return back()->with('error', 'Nhân viên chỉ được chuyển nhanh sang tài khoản khách hàng.');
        }

        $request->session()->put(
            self::SESSION_SWITCHER_ID,
            $request->session()->get(self::SESSION_SWITCHER_ID, $currentUser->getKey())
        );
        $request->session()->put(
            self::SESSION_SWITCHER_NAME,
            $request->session()->get(self::SESSION_SWITCHER_NAME, $currentUser->name)
        );
        $request->session()->put(self::SESSION_STARTED_AT, now()->toDateTimeString());

        Auth::login($targetUser);
        $request->session()->regenerate();

        $route = in_array($targetUser->role, ['admin', 'staff'], true)
            ? 'admin.dashboard'
            : 'home';

        return redirect()->route($route)->with('success', 'Đã chuyển sang tài khoản ' . $targetUser->name . '.');
    }

    public function restore(Request $request)
    {
        $switcherId = $request->session()->get(self::SESSION_SWITCHER_ID);

        if (!$switcherId) {
            return back()->with('error', 'Không có phiên chuyển tài khoản nào để quay lại.');
        }

        $switcher = User::whereKey((int) $switcherId)->first();

        if (!$switcher || !$switcher->status || !in_array($switcher->role, ['admin', 'staff'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Tài khoản gốc không còn hợp lệ. Vui lòng đăng nhập lại.');
        }

        Auth::login($switcher);
        $request->session()->forget([
            self::SESSION_SWITCHER_ID,
            self::SESSION_SWITCHER_NAME,
            self::SESSION_STARTED_AT,
        ]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Đã quay lại tài khoản ' . $switcher->name . '.');
    }
}
