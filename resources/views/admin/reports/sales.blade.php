@extends('layouts.admin')

@section('title', 'Báo cáo doanh số')

@section('content')
<style>
    .rep-head { margin-bottom: 1.5rem; }
    .rep-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.35rem; }
    .rep-sub { color: var(--muted); font-size: 0.9rem; }
    .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem; }
    .stat-box .lbl { color: var(--muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em; }
    .stat-box .val { font-size: 1.5rem; font-weight: 800; color: var(--accent); margin-top: 0.35rem; }
    .chart-row { display: flex; align-items: flex-end; gap: 0.5rem; height: 140px; margin-bottom: 2rem; padding: 1rem; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; }
    .chart-bar { flex: 1; background: rgba(201, 169, 98, 0.25); border-radius: 6px 6px 0 0; position: relative; min-height: 4px; transition: 0.2s; }
    .chart-bar:hover { background: rgba(201, 169, 98, 0.45); }
    .chart-bar span { position: absolute; bottom: -1.4rem; left: 50%; transform: translateX(-50%); font-size: 0.65rem; color: var(--muted); white-space: nowrap; }
    .filter-form { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; margin-bottom: 1rem; }
    .filter-form label { font-size: 0.85rem; color: var(--muted); }
    .filter-form input { padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text); }
    .filter-form button { padding: 0.45rem 1rem; border-radius: 6px; border: none; background: var(--accent); color: #0c0f14; font-weight: 600; cursor: pointer; }
    .table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
    .admin-table th, .admin-table td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); }
    .admin-table th { color: var(--muted); font-size: 0.8rem; text-transform: uppercase; }
</style>

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
                <div class="val" style="font-size: 1.1rem; color: var(--text);">
                    {{ $row->cnt ?? 0 }} đơn
                    <span style="display: block; font-size: 0.85rem; color: var(--muted); font-weight: 500;">
                        {{ number_format($row->sum_price ?? 0, 0, ',', '.') }} đ
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">6 tháng gần đây (doanh thu hoàn tất)</h2>
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
                        <td style="color: var(--accent); font-weight: 600;">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                        <td>
                            @if($order->status == 2) <span style="color: #4ade80;">Hoàn tất</span>
                            @elseif($order->status == 1) Đã cọc
                            @elseif($order->status == 3) Hủy
                            @else Chờ @endif
                        </td>
                        <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">Không có đơn trong tháng này.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($monthlyRows->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">{{ $monthlyRows->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
