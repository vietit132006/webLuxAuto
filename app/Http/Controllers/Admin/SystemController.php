<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SystemController extends Controller
{
    // --- 1. Đăng nhập 2 lớp (2FA) ---
    public function showTwoFactor()
    {
        return view('admin.system.2fa', [
            'isEnabled' => Auth::user()->is_2fa_enabled
        ]);
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = Auth::user();
        $user->is_2fa_enabled = ! $user->is_2fa_enabled;
        $user->save();

        SystemLog::create([
            'user_id' => $user->id,
            'action' => ($user->is_2fa_enabled ? 'Bật' : 'Tắt') . ' xác thực 2 lớp',
        ]);

        return back()->with('success', ($user->is_2fa_enabled ? 'Đã bật' : 'Đã tắt') . ' xác thực 2 lớp thành công!');
    }

    // --- 2. Sao lưu dữ liệu (Backup) ---
    public function showBackup()
    {
        return view('admin.system.backup');
    }

    public function downloadBackup()
    {
        // Đây là bản demo sao lưu đơn giản (chỉ xuất JSON cho gọn vì mysqldump có thể không có sẵn)
        $tables = ['users', 'cars', 'brands', 'orders', 'order_details', 'reviews', 'news', 'settings'];
        $data = [];
        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get();
        }

        $filename = 'backup_' . date('Y_m_d_His') . '.json';
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Thực hiện sao lưu dữ liệu hệ thống',
        ]);

        return Response::make($content, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // --- 3. Kiểm tra nhật ký (Audit Logs) ---
    public function logs()
    {
        $logs = SystemLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.system.logs', compact('logs'));
    }

    // --- 4. Cấu hình xe (Car Configuration) ---
    public function carConfig()
    {
        $settings = Setting::where('group', 'car')->pluck('value', 'key');
        return view('admin.system.car_config', compact('settings'));
    }

    public function updateCarConfig(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'car'],
                ['value' => $value]
            );
        }

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Cập nhật cấu hình xe',
        ]);

        return back()->with('success', 'Cập nhật cấu hình xe thành công!');
    }
}
