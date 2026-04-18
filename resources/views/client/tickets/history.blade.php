@extends('layouts.site')
@section('title', 'Lịch Sử Hỗ Trợ')

@section('content')
<div class="wrap" style="max-width: 1200px; padding: 3rem 1.25rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <h1 style="margin: 0; font-size: 2rem; color: #f8fafc;">Lịch Sử <span style="color: var(--accent);">Hỗ Trợ</span></h1>
        <a href="{{ route('ticket.create') }}" style="padding: 0.6rem 1.2rem; background: var(--accent); color: #000; border-radius: 8px; font-weight: bold; transition: 0.2s; text-decoration: none;">
            + Tạo Yêu Cầu Mới
        </a>
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
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        @forelse($tickets as $ticket)
            <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: transform 0.2s;">

                <div style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <h3 style="margin: 0 0 0.5rem; font-size: 1.2rem; color: var(--text);">{{ $ticket->subject }}</h3>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--muted);">Mã Ticket: #{{ $ticket->ticket_id }} • Gửi lúc: {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        @if($ticket->status == 'pending')
                            <span style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">⏳ Chờ xử lý</span>
                        @elseif($ticket->status == 'answered')
                            <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">✅ Đã trả lời</span>
                        @else
                            <span style="background: rgba(100, 116, 139, 0.15); color: #94a3b8; padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.85rem; font-weight: bold; white-space: nowrap;">🔒 Đã đóng</span>
                        @endif
                    </div>
                </div>

                <div style="padding: 1.5rem; background: #0a0d12;">
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: var(--muted); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Nội dung bạn gửi:</strong>
                        <p style="margin: 0; color: var(--text); line-height: 1.6; font-size: 0.95rem;">{!! nl2br(e($ticket->message)) !!}</p>
                    </div>

                    @if($ticket->admin_reply)
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed var(--border);">
                            <strong style="color: var(--accent); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">Phản hồi từ Lux Auto:</strong>
                            <div style="background: rgba(201, 169, 98, 0.05); padding: 1rem; border-radius: 8px; border-left: 3px solid var(--accent);">
                                <p style="margin: 0; color: var(--text); line-height: 1.6; font-size: 0.95rem;">{!! nl2br(e($ticket->admin_reply)) !!}</p>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div style="text-align: center; padding: 4rem 2rem; background: var(--surface); border: 1px dashed var(--border); border-radius: 16px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                <h3 style="margin: 0 0 0.5rem; color: var(--text);">Bạn chưa có yêu cầu hỗ trợ nào</h3>
                <p style="color: var(--muted); margin-bottom: 1.5rem;">Nếu cần giải đáp thắc mắc, hãy gửi yêu cầu cho chúng tôi.</p>
                <a href="{{ route('ticket.create') }}" style="color: var(--accent); font-weight: bold; text-decoration: underline;">Tạo Yêu Cầu Ngay</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
