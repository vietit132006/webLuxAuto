@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')

@section('content')
<style>
    .rep-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1rem; }
    .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; }
    .stat-box .lbl { color: var(--muted); font-size: 0.8rem; }
    .stat-box .val { font-size: 1.35rem; font-weight: 800; color: var(--accent); }
    .table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th, .admin-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); text-align: left; }
    .admin-table th { color: var(--muted); font-size: 0.75rem; text-transform: uppercase; }
    .low { color: #fbbf24; font-weight: 600; }
    .out { color: #f87171; font-weight: 600; }
    .ok { color: #4ade80; }
</style>

<div class="wrap">
    <h1 class="rep-title">Báo cáo tồn kho</h1>

    <div class="stat-row">
        <div class="stat-box">
            <div class="lbl">Tổng chiếc trong kho</div>
            <div class="val">{{ number_format($totalUnits) }}</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Dòng sắp hết (1–2 chiếc)</div>
            <div class="val">{{ $lowStock }}</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Hết hàng</div>
            <div class="val">{{ $outOfStock }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Xe</th>
                    <th>Hãng</th>
                    <th>Giá</th>
                    <th>Tồn</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cars as $car)
                    <tr>
                        <td style="font-weight: 600;">{{ $car->name }}</td>
                        <td>{{ $car->brand->name ?? '—' }}</td>
                        <td style="color: var(--accent);">{{ number_format($car->price, 0, ',', '.') }} đ</td>
                        <td>
                            @if($car->stock <= 0)
                                <span class="out">0</span>
                            @elseif($car->stock <= 2)
                                <span class="low">{{ $car->stock }}</span>
                            @else
                                <span class="ok">{{ $car->stock }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($cars->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">{{ $cars->links('pagination.lux') }}</div>
    @endif

    <p style="margin-top: 1.5rem;">
        <a href="{{ route('admin.reports.inventory_check') }}" style="color: var(--accent); font-weight: 600;">→ Đi tới kiểm tra &amp; ghi nhận tồn</a>
    </p>
</div>
@endsection
