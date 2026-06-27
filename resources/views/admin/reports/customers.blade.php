@extends('layouts.admin')

@section('title', 'Báo cáo khách hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-customers.css')
    @endif
@endpush


@section('content')

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
                        <td class="admin-reports-customers-inline-4">{{ $u->name }}</td>
                        <td class="admin-reports-customers-inline-3">{{ $u->email }}</td>
                        <td>{{ $u->orders_count }}</td>
                        <td class="admin-reports-customers-inline-2">{{ number_format($u->orders_sum_total_price ?? 0, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="admin-reports-customers-inline-1">{{ $customers->links('pagination.lux') }}</div>
    @endif
</div>
@endsection