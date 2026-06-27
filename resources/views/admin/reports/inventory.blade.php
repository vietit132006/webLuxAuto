@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-inventory.css')
    @endif
@endpush


@section('content')

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
                        <td class="admin-reports-inventory-inline-5">{{ $car->name }}</td>
                        <td>{{ $car->brand->name ?? '—' }}</td>
                        <td class="admin-reports-inventory-inline-4">{{ number_format($car->price, 0, ',', '.') }} đ</td>
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
        <div class="admin-reports-inventory-inline-3">{{ $cars->links('pagination.lux') }}</div>
    @endif

    <p class="admin-reports-inventory-inline-2">
        <a class="admin-reports-inventory-inline-1" href="{{ route('admin.reports.inventory_check') }}">→ Đi tới kiểm tra &amp; ghi nhận tồn</a>
    </p>
</div>
@endsection