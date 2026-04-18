<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
    // Hiển thị danh sách tất cả ticket cho Admin
    public function index()
    {
        // Kèm theo thông tin user gửi, xếp cái mới nhất hoặc chưa trả lời lên đầu
        $tickets = Ticket::with('user')->orderByRaw("FIELD(status, 'pending', 'answered', 'closed')")->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.tickets.index', compact('tickets'));
    }

    // Admin cập nhật câu trả lời
    public function reply(Request $request, $id)
    {
        $request->validate(['admin_reply' => 'required|string']);

        $ticket = Ticket::findOrFail($id);
        $ticket->update([
            'admin_reply' => $request->admin_reply,
            'status' => $request->status ?? 'answered' // Chuyển trạng thái thành đã trả lời
        ]);

        return back()->with('success', 'Đã phản hồi ticket của khách hàng!');
    }
}
