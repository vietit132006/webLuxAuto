<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TicketController extends Controller
{
    // Xem lịch sử hỗ trợ
    public function history()
    {
        // Chỉ lấy các ticket của người đang đăng nhập
        $tickets = Ticket::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('client.tickets.history', compact('tickets'));
    }

    // Hiển thị form tạo mới
    public function create()
    {
        $car = null;
        $carId = request()->query('car_id');
        if ($carId) {
            $car = Car::with('brand')->find($carId);
        }

        $type = request()->query('type', 'support');
        if (!in_array($type, ['support', 'test_drive'], true)) {
            $type = 'support';
        }

        return view('client.tickets.create', compact('car', 'type'));
    }

    // Lưu ticket vào DB
    public function store(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required|in:support,test_drive',
            'car_id' => 'nullable|exists:cars,car_id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // DB chưa migrate cột mới -> tránh 500 và hướng dẫn xử lý
        if (!Schema::hasColumn('support_tickets', 'ticket_type') || !Schema::hasColumn('support_tickets', 'car_id')) {
            return back()->withInput()->withErrors([
                'car_id' => 'Hệ thống chưa cập nhật bảng ticket. Vui lòng chạy: php artisan migrate',
            ]);
        }

        if ($request->ticket_type === 'test_drive' && empty($request->car_id)) {
            return back()->withInput()->withErrors(['car_id' => 'Vui lòng chọn xe cần đặt lịch lái thử.']);
        }

        Ticket::create([
            'user_id' => Auth::id(),
            'ticket_type' => $request->ticket_type,
            'car_id' => $request->car_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending' // Mặc định là chờ xử lý
        ]);

        return redirect()->route('ticket.history')->with('success', 'Đã gửi yêu cầu hỗ trợ thành công. Chúng tôi sẽ phản hồi sớm nhất!');
    }
}
