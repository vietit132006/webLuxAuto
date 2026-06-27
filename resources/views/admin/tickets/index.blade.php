@extends('layouts.admin')
@section('title', 'Quản lý Hỗ trợ Khách hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-tickets-index.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="header-actions admin-tickets-index-inline-31">
        <h1 class="page-title admin-tickets-index-inline-30">Quản lý <span class="admin-tickets-index-inline-29">Hỗ trợ (Tickets)</span></h1>
        <p class="admin-tickets-index-inline-28">Giải đáp thắc mắc và hỗ trợ khách hàng nhanh chóng</p>
    </div>

    @if(session('success'))
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

    <div class="table-responsive admin-tickets-index-inline-27">
        <table class="admin-tickets-index-inline-26">
            <thead>
                <tr class="admin-tickets-index-inline-25">
                    <th class="admin-tickets-index-inline-20">Mã Ticket</th>
                    <th class="admin-tickets-index-inline-20">Khách hàng</th>
                    <th class="admin-tickets-index-inline-20">Tiêu đề</th>
                    <th class="admin-tickets-index-inline-20">Trạng thái</th>
                    <th class="admin-tickets-index-inline-20">Ngày gửi</th>
                    <th class="admin-tickets-index-inline-15">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr style="border-top: 1px solid var(--border); transition: background 0.2s; background: {{ $ticket->status == 'pending' ? 'rgba(245, 158, 11, 0.03)' : 'transparent' }};">
                        <td class="admin-tickets-index-inline-24">#{{ $ticket->ticket_id }}</td>
                        <td class="admin-tickets-index-inline-20">
                            <div class="admin-tickets-index-inline-23">{{ $ticket->user->name ?? 'Khách vô danh' }}</div>
                            <div class="admin-tickets-index-inline-22">{{ $ticket->user->email ?? '' }}</div>
                        </td>
                        <td class="admin-tickets-index-inline-21">{{ Str::limit($ticket->subject, 40) }}</td>
                        <td class="admin-tickets-index-inline-20">
                            @if($ticket->status == 'pending')
                                <span class="admin-tickets-index-inline-19">Chờ xử lý</span>
                            @elseif($ticket->status == 'answered')
                                <span class="admin-tickets-index-inline-18">Đã trả lời</span>
                            @else
                                <span class="admin-tickets-index-inline-17">Đã đóng</span>
                            @endif
                        </td>
                        <td class="admin-tickets-index-inline-16">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td class="admin-tickets-index-inline-15">
                            <button class="admin-tickets-index-inline-14" onclick="toggleReplyForm({{ $ticket->ticket_id }})">
                                Xem / Phản hồi
                            </button>
                        </td>
                    </tr>

                    <tr id="reply-row-{{ $ticket->ticket_id }}" style="display: none; background: rgba(0,0,0,0.2);">
                        <td class="admin-tickets-index-inline-13" colspan="6">
                            <div class="admin-tickets-index-inline-12">

                                <div>
                                    <h4 class="admin-tickets-index-inline-11">Nội dung khách hỏi:</h4>
                                    <div class="admin-tickets-index-inline-10">
                                        <h3 class="admin-tickets-index-inline-9">{{ $ticket->subject }}</h3>
                                        <p class="admin-tickets-index-inline-8">{!! nl2br(e($ticket->message)) !!}</p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="admin-tickets-index-inline-7">Nhập phản hồi:</h4>
                                    <form action="{{ route('admin.tickets.reply', $ticket->ticket_id) }}" method="POST">
                                        @csrf
                                        <textarea class="admin-tickets-index-inline-6" name="admin_reply" rows="5" placeholder="Nhập câu trả lời của bạn..." required>{{ $ticket->admin_reply }}</textarea>

                                        <div class="admin-tickets-index-inline-5">
                                            <select class="admin-tickets-index-inline-4" name="status">
                                                <option value="answered" {{ $ticket->status == 'answered' ? 'selected' : '' }}>Chuyển thành Đã Trả Lời</option>
                                                <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Giữ trạng thái Đang Chờ</option>
                                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Đóng Ticket này</option>
                                            </select>

                                            <button class="admin-tickets-index-inline-3" type="submit">
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
                        <td class="admin-tickets-index-inline-2" colspan="6">Không có yêu cầu hỗ trợ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-tickets-index-inline-1">
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

@endsection