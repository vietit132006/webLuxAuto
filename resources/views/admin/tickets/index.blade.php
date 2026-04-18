@extends('layouts.admin')
@section('title', 'Quản lý Hỗ trợ Khách hàng')

@section('content')
<div class="wrap">
    <div class="header-actions" style="margin-bottom: 2rem;">
        <h1 class="page-title" style="margin: 0; font-size: 1.8rem; color: #f8fafc;">Quản lý <span style="color: var(--accent);">Hỗ trợ (Tickets)</span></h1>
        <p style="color: var(--muted); margin-top: 0.5rem;">Giải đáp thắc mắc và hỗ trợ khách hàng nhanh chóng</p>
    </div>

    @if(session('success'))
      <style>
            .ticket-flash-alert {
                padding: 1rem 1.5rem;
                margin-bottom: 2rem;
                background-color: #d1fae5;
                color: #065f46;
                border: 1px solid #34d399;
                border-radius: 8px;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: opacity 0.5s ease, transform 0.5s ease;
            }
            .ticket-flash-alert.hide {
                opacity: 0;
                transform: translateY(-10px);
                pointer-events: none;
            }
            .btn-close-ticket-alert {
                background: none;
                border: none;
                color: #065f46;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                padding: 0 0 0 1rem;
                transition: transform 0.2s;
            }
            .btn-close-ticket-alert:hover {
                transform: scale(1.2);
            }
        </style>
        <div id="ticket-success-alert" class="ticket-flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-ticket-alert" onclick="closeTicketAlert()" aria-label="Đóng">&times;</button>
        </div>

                <script>
            function closeTicketAlert() {
                const alertBox = document.getElementById('ticket-success-alert');
                if (alertBox) {
                    alertBox.classList.add('hide');
                    setTimeout(() => { alertBox.remove(); }, 500);
                }
            }
            // Tự động tắt sau 2 giây
            setTimeout(() => { closeTicketAlert(); }, 2000);
        </script>

    @endif

    <div class="table-responsive" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: rgba(255,255,255,0.05); color: var(--muted); text-transform: uppercase; font-size: 0.8rem;">
                    <th style="padding: 1rem;">Mã Ticket</th>
                    <th style="padding: 1rem;">Khách hàng</th>
                    <th style="padding: 1rem;">Tiêu đề</th>
                    <th style="padding: 1rem;">Trạng thái</th>
                    <th style="padding: 1rem;">Ngày gửi</th>
                    <th style="padding: 1rem; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr style="border-top: 1px solid var(--border); transition: background 0.2s; background: {{ $ticket->status == 'pending' ? 'rgba(245, 158, 11, 0.03)' : 'transparent' }};">
                        <td style="padding: 1rem; font-weight: bold; color: var(--text);">#{{ $ticket->ticket_id }}</td>
                        <td style="padding: 1rem;">
                            <div style="font-weight: bold; color: var(--text);">{{ $ticket->user->name ?? 'Khách vô danh' }}</div>
                            <div style="font-size: 0.8rem; color: var(--muted);">{{ $ticket->user->email ?? '' }}</div>
                        </td>
                        <td style="padding: 1rem; font-weight: 500; color: var(--text);">{{ Str::limit($ticket->subject, 40) }}</td>
                        <td style="padding: 1rem;">
                            @if($ticket->status == 'pending')
                                <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Chờ xử lý</span>
                            @elseif($ticket->status == 'answered')
                                <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Đã trả lời</span>
                            @else
                                <span style="background: rgba(100, 116, 139, 0.15); color: #94a3b8; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Đã đóng</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; color: var(--muted); font-size: 0.9rem;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding: 1rem; text-align: right;">
                            <button onclick="toggleReplyForm({{ $ticket->ticket_id }})" style="background: var(--accent); color: #000; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                                Xem / Phản hồi
                            </button>
                        </td>
                    </tr>

                    <tr id="reply-row-{{ $ticket->ticket_id }}" style="display: none; background: rgba(0,0,0,0.2);">
                        <td colspan="6" style="padding: 0;">
                            <div style="padding: 2rem; border-top: 1px dashed var(--border); display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

                                <div>
                                    <h4 style="color: var(--muted); margin: 0 0 1rem; font-size: 0.9rem; text-transform: uppercase;">Nội dung khách hỏi:</h4>
                                    <div style="background: #0a0d12; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                                        <h3 style="margin: 0 0 1rem; color: var(--text);">{{ $ticket->subject }}</h3>
                                        <p style="margin: 0; line-height: 1.6; color: var(--text); font-size: 0.95rem;">{!! nl2br(e($ticket->message)) !!}</p>
                                    </div>
                                </div>

                                <div>
                                    <h4 style="color: var(--accent); margin: 0 0 1rem; font-size: 0.9rem; text-transform: uppercase;">Nhập phản hồi:</h4>
                                    <form action="{{ route('admin.tickets.reply', $ticket->ticket_id) }}" method="POST">
                                        @csrf
                                        <textarea name="admin_reply" rows="5" placeholder="Nhập câu trả lời của bạn..." required
                                            style="width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid var(--border); background: #0a0d12; color: var(--text); margin-bottom: 1rem; resize: vertical; font-family: inherit;">{{ $ticket->admin_reply }}</textarea>

                                        <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center;">
                                            <select name="status" style="padding: 0.8rem; background: #0a0d12; border: 1px solid var(--border); color: var(--text); border-radius: 6px;">
                                                <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>Chuyển thành Đã Trả Lời</option>
                                                <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Giữ trạng thái Đang Chờ</option>
                                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Đóng Ticket này</option>
                                            </select>

                                            <button type="submit" style="background: var(--accent); color: #000; font-weight: bold; border: none; padding: 0.8rem 1.5rem; border-radius: 6px; cursor: pointer;">
                                                📤 Gửi Phản Hồi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 3rem; text-align: center; color: var(--muted);">Không có yêu cầu hỗ trợ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $tickets->links() }}
    </div>
</div>

<script>
    function toggleReplyForm(id) {
        const row = document.getElementById('reply-row-' + id);
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }
</script>

<style>
    textarea:focus, select:focus { border-color: var(--accent) !important; outline: none; }
</style>
@endsection
