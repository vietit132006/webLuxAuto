@extends('layouts.site')
@section('title', 'Lịch sử giao dịch')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-orders-history.css')
    @endif
@endpush


@section('content')

<div class="history-wrap">
    <h1 class="page-title">Lịch sử giao dịch</h1>

    @if($orders->isEmpty())
        <div class="empty-state">
            <h3 class="client-orders-history-inline-8">Bạn chưa có giao dịch nào!</h3>
            <p class="client-orders-history-inline-7">Hãy khám phá các dòng xe sang trọng tại Lux Auto và trải nghiệm dịch vụ đẳng cấp của chúng tôi.</p>
            <a class="client-orders-history-inline-6" href="{{ route('cars.index') }}" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">Khám phá xe ngay</a>
        </div>
    @else
        <div class="history-grid">
            @foreach($orders as $order)
                <div class="history-card">
                    <div class="h-card-header">
                        <div>
                            <span class="h-code">Mã đơn: {{ $order->display_code }}</span>
                            <span class="client-orders-history-inline-5">|</span>
                            <span class="h-date">Ngày đặt: {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div>
                            @if($order->status == 0) <span class="badge badge-0">⏳ Đang chờ xác nhận</span>
                            @elseif($order->status == 1) <span class="badge badge-1">💸 Đã đặt cọc thành công</span>
                            @elseif($order->status == 2) <span class="badge badge-2">✅ Giao dịch hoàn tất</span>
                            @elseif($order->status == 3) <span class="badge badge-3">❌ Đã hủy</span>
                            @endif
                        </div>
                    </div>

                    @foreach($order->details as $detail)
                    <div class="h-card-body">
                        @if($detail->car && $detail->car->image)
                            <img src="{{ asset('storage/' . $detail->car->image) }}" alt="Car" class="h-car-img">
                        @else
                            <div class="client-orders-history-inline-4">NO IMAGE</div>
                        @endif

                        <div class="h-car-info">
                            <h4 class="h-car-name">
                                <a class="client-orders-history-inline-3" href="{{ $detail->car ? route('cars.show_public', $detail->car->car_id) : '#' }}" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='inherit'">
                                    {{ $detail->car ? $detail->car->name : 'Xe đã bị gỡ khỏi hệ thống' }}
                                </a>
                            </h4>
                            <div class="h-price">Giá xe: {{ number_format($detail->price, 0, ',', '.') }} đ</div>
                            <div class="client-orders-history-inline-2">Số tiền cần cọc: 20.000.000 đ</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if ($orders->hasPages())
            <div class="client-orders-history-inline-1">
                {{ $orders->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection
