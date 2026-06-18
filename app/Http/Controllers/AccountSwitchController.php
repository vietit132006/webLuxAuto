<?php

namespace App\Http\Controllers;

use App\Models\SavedLoginAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountSwitchController extends Controller
{
    public const SESSION_SWITCHER_ID = 'account_switcher_user_id';
    public const SESSION_SWITCHER_NAME = 'account_switcher_user_name';
    public const SESSION_STARTED_AT = 'account_switcher_started_at';

    private const SAVED_LOGIN_TOKEN_DAYS = 30;

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

        return redirect()
            ->route($this->homeRouteFor($targetUser))
            ->with('success', 'Đã chuyển sang tài khoản ' . $targetUser->name . '.');
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

            return redirect()
                ->route('login')
                ->with('error', 'Tài khoản gốc không còn hợp lệ. Vui lòng đăng nhập lại.');
        }

        Auth::login($switcher);
        $request->session()->forget([
            self::SESSION_SWITCHER_ID,
            self::SESSION_SWITCHER_NAME,
            self::SESSION_STARTED_AT,
        ]);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Đã quay lại tài khoản ' . $switcher->name . '.');
    }

    public function storeSavedAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => ['required', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $token = Str::random(64);

        $account = SavedLoginAccount::updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'device_id' => $data['device_id'],
            ],
            [
                'device_name' => $data['device_name'] ?? $request->userAgent(),
                'token_hash' => $this->hashSavedLoginToken($token),
                'expires_at' => now()->addDays(self::SAVED_LOGIN_TOKEN_DAYS),
                'last_used_at' => now(),
            ]
        );

        SavedLoginAccount::query()
            ->where('user_id', $user->getKey())
            ->where('device_id', $data['device_id'])
            ->where('id', '!=', $account->id)
            ->delete();

        $account->load('user');

        return response()->json([
            'message' => 'Đã lưu tài khoản trên thiết bị này.',
            'account' => $this->savedAccountPayload($account, $token),
        ]);
    }

    public function loginWithSavedAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'device_id' => ['required', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'token' => ['required', 'string', 'max:255'],
        ]);

        $account = SavedLoginAccount::with('user')
            ->where('user_id', (int) $data['user_id'])
            ->where('device_id', $data['device_id'])
            ->where('token_hash', $this->hashSavedLoginToken($data['token']))
            ->first();

        if (!$account) {
            return $this->invalidSavedAccountResponse('Phiên đăng nhập nhanh không hợp lệ. Vui lòng đăng nhập lại bằng mật khẩu.');
        }

        if ($account->isExpired()) {
            $account->delete();

            return $this->invalidSavedAccountResponse('Tài khoản đã lưu đã hết hạn. Vui lòng đăng nhập lại bằng mật khẩu.', 410, true);
        }

        if (!$account->user || !$account->user->status) {
            return $this->invalidSavedAccountResponse('Tài khoản này không còn khả dụng. Vui lòng đăng nhập lại bằng mật khẩu.', 403, true);
        }

        Auth::login($account->user);

        $request->session()->forget([
            self::SESSION_SWITCHER_ID,
            self::SESSION_SWITCHER_NAME,
            self::SESSION_STARTED_AT,
        ]);
        $request->session()->regenerate();

        $account->forceFill([
            'device_name' => $data['device_name'] ?? $account->device_name,
            'last_used_at' => now(),
        ])->save();

        $account->refresh()->load('user');

        return response()->json([
            'message' => 'Đăng nhập nhanh thành công.',
            'redirect_url' => route($this->homeRouteFor($account->user)),
            'account' => $this->savedAccountPayload($account, $data['token']),
        ]);
    }

    public function destroySavedAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer'],
            'device_id' => ['required', 'string', 'max:120'],
            'token' => ['required', 'string', 'max:255'],
        ]);

        SavedLoginAccount::query()
            ->where('user_id', (int) $data['user_id'])
            ->where('device_id', $data['device_id'])
            ->where('token_hash', $this->hashSavedLoginToken($data['token']))
            ->delete();

        return response()->json([
            'message' => 'Đã xóa tài khoản đã lưu khỏi thiết bị này.',
        ]);
    }

    private function hashSavedLoginToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function homeRouteFor(User $user): string
    {
        return in_array($user->role, ['admin', 'staff'], true) ? 'admin.dashboard' : 'home';
    }

    private function invalidSavedAccountResponse(string $message, int $status = 401, bool $removeAccount = true): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'redirect_url' => route('login'),
            'remove_account' => $removeAccount,
        ], $status);
    }

    private function savedAccountPayload(SavedLoginAccount $account, string $token): array
    {
        return [
            'id' => $account->id,
            'user_id' => $account->user->getKey(),
            'name' => $account->user->name,
            'email' => $account->user->email,
            'role' => $account->user->role,
            'avatar_url' => null,
            'device_id' => $account->device_id,
            'device_name' => $account->device_name,
            'token' => $token,
            'expires_at' => optional($account->expires_at)->toIso8601String(),
            'last_used_at' => optional($account->last_used_at)->toIso8601String(),
        ];
    }
}
