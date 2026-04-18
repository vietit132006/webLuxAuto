<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('client.tickets.create');
    }

    // Lưu ticket vào DB
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Ticket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending' // Mặc định là chờ xử lý
        ]);

        return redirect()->route('ticket.history')->with('success', 'Đã gửi yêu cầu hỗ trợ thành công. Chúng tôi sẽ phản hồi sớm nhất!');
    }
}
