@extends('layouts.admin')

@section('title', 'Báo cáo khách hàng')

@section('content')
<style>
    .rep-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1rem; }
    .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; }
    .stat-box .lbl { color: var(--muted); font-size: 0.8rem; }
    .stat-box .val { font-size: 1.35rem; font-weight: 800; color: var(--accent); }
    .table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th, .admin-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); text-align: left; }
    .admin-table th { color: var(--muted); font-size: 0.75rem; text-transform: uppercase; }
</style>

<div class="wrap">
    <h1 class="rep-title">Báo cáo khách hàng</h1>

    <div class="stat-row">
        <div class="stat-box">
            <div class="lbl">Tổng khách (role customer)</div>
            <div class="val">{{ number_format($totalCustomers) }}</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Đã từng đặt đơn</div>
            <div class="val">{{ number_format($activeBuyers) }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Khách</th>
                    <th>Email</th>
                    <th>Số đơn</th>
                    <th>Tổng giá trị đơn (tham chiếu)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $u)
                    <tr>
                        <td style="font-weight: 600;">{{ $u->name }}</td>
                        <td style="color: var(--muted); font-size: 0.9rem;">{{ $u->email }}</td>
                        <td>{{ $u->orders_count }}</td>
                        <td style="color: var(--accent); font-weight: 600;">{{ number_format($u->orders_sum_total_price ?? 0, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">{{ $customers->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
