@extends('layouts.site')
@section('title', 'Lịch Sử Hỗ Trợ')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-tickets-history.css')
    @endif
@endpush


@section('content')
<div class="wrap client-tickets-history-inline-25">
    <div class="client-tickets-history-inline-24">
        <h1 class="client-tickets-history-inline-23">Lịch Sử <span class="client-tickets-history-inline-22">Hỗ Trợ</span></h1>
        <a class="client-tickets-history-inline-21" href="{{ route('ticket.create') }}">
            + Tạo Yêu Cầu Mới
        </a>
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
    <div class="client-tickets-history-inline-20">
        @forelse($tickets as $ticket)
            <div class="client-tickets-history-inline-19">

                <div class="client-tickets-history-inline-18">
                    <div>
                        <h3 class="client-tickets-history-inline-17">{{ $ticket->subject }}</h3>
                        <p class="client-tickets-history-inline-16">Mã Ticket: #{{ $ticket->ticket_id }} • Gửi lúc: {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        @if($ticket->status == 'pending')
                            <span class="client-tickets-history-inline-15">⏳ Chờ xử lý</span>
                        @elseif($ticket->status == 'answered')
                            <span class="client-tickets-history-inline-14">✅ Đã trả lời</span>
                        @else
                            <span class="client-tickets-history-inline-13">🔒 Đã đóng</span>
                        @endif
                    </div>
                </div>

                <div class="client-tickets-history-inline-12">
                    <div class="client-tickets-history-inline-11">
                        <strong class="client-tickets-history-inline-10">Nội dung bạn gửi:</strong>
                        <p class="client-tickets-history-inline-6">{!! nl2br(e($ticket->message)) !!}</p>
                    </div>

                    @if($ticket->admin_reply)
                        <div class="client-tickets-history-inline-9">
                            <strong class="client-tickets-history-inline-8">Phản hồi từ Lux Auto:</strong>
                            <div class="client-tickets-history-inline-7">
                                <p class="client-tickets-history-inline-6">{!! nl2br(e($ticket->admin_reply)) !!}</p>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div class="client-tickets-history-inline-5">
                <div class="client-tickets-history-inline-4">📭</div>
                <h3 class="client-tickets-history-inline-3">Bạn chưa có yêu cầu hỗ trợ nào</h3>
                <p class="client-tickets-history-inline-2">Nếu cần giải đáp thắc mắc, hãy gửi yêu cầu cho chúng tôi.</p>
                <a class="client-tickets-history-inline-1" href="{{ route('ticket.create') }}">Tạo Yêu Cầu Ngay</a>
            </div>
        @endforelse
    </div>
</div>
@endsection