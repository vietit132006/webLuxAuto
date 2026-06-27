@extends('layouts.admin')

@section('title', 'Báo cáo doanh số')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-sales.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <div class="rep-head">
        <h1 class="rep-title">Báo cáo doanh số</h1>
        <p class="rep-sub">Tổng hợp đơn hàng theo trạng thái và doanh thu giao xe (hoàn tất).</p>
    </div>

    <div class="stat-row">
        <div class="stat-box">
            <div class="lbl">Doanh thu (đơn hoàn tất)</div>
            <div class="val">{{ number_format($totalCompleted, 0, ',', '.') }} đ</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Giá trị đơn đã cọc</div>
            <div class="val">{{ number_format($totalDeposited, 0, ',', '.') }} đ</div>
        </div>
        @foreach([0 => 'Chờ xử lý', 1 => 'Đã cọc', 2 => 'Hoàn tất', 3 => 'Đã hủy'] as $st => $label)
            @php
                $row = $byStatus->get($st) ?? $byStatus->get((string) $st);
            @endphp
            <div class="stat-box">
                <div class="lbl">{{ $label }}</div>
                <div class="val admin-reports-sales-inline-7">
                    {{ $row->cnt ?? 0 }} đơn
                    <span class="admin-reports-sales-inline-6">
                        {{ number_format($row->sum_price ?? 0, 0, ',', '.') }} đ
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="admin-reports-sales-inline-5">6 tháng gần đây (doanh thu hoàn tất)</h2>
    @php $maxRev = max($chartData->pluck('revenue')->max(), 1); @endphp
    <div class="chart-row">
        @foreach($chartData as $row)
            @php $h = ($row['revenue'] / $maxRev) * 100; @endphp
            <div class="chart-bar" style="height: {{ max(4, $h) }}%;" title="{{ number_format($row['revenue'], 0, ',', '.') }} đ">
                <span>{{ $row['label'] }}</span>
            </div>
        @endforeach
    </div>

    <form class="filter-form" method="get" action="{{ route('admin.reports.sales') }}">
        <div>
            <label for="month">Xem đơn theo tháng</label><br>
            <input type="month" id="month" name="month" value="{{ $month }}">
        </div>
        <button type="submit">Lọc</button>
    </form>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Khách</th>
                    <th>Giá trị</th>
                    <th>Trạng thái</th>
                    <th>Ngày</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyRows as $order)
                    <tr>
                        <td>#{{ $order->order_id }}</td>
                        <td>{{ $order->user->name ?? '—' }}</td>
                        <td class="admin-reports-sales-inline-4">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                        <td>
                            @if($order->status == 2) <span class="admin-reports-sales-inline-3">Hoàn tất</span>
                            @elseif($order->status == 1) Đã cọc
                            @elseif($order->status == 3) Hủy
                            @else Chờ @endif
                        </td>
                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="admin-reports-sales-inline-2" colspan="5">Không có đơn trong tháng này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($monthlyRows->hasPages())
        <div class="admin-reports-sales-inline-1">{{ $monthlyRows->links('pagination.lux') }}</div>
    @endif
</div>
@endsection