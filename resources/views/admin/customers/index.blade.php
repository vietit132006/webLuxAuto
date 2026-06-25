@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-customers.css')
    @endif
@endpush

@section('content')
@php
    $countFor = fn (string $status): int => (int) ($statusCounts[$status] ?? 0);
@endphp

<div class="admin-customers-page">
    <div class="admin-customers-head">
        <div>
            <h1>Quản lý khách hàng</h1>
            <p>Bán hàng / Khách hàng</p>
        </div>

        @can('customers.create')
            <a class="admin-customers-primary" href="{{ route('admin.customers.create') }}">Thêm khách hàng</a>
        @endcan
    </div>

    @if(session('success'))
        <div class="admin-customers-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="admin-customers-alert is-error">{{ session('error') }}</div>
    @endif

    <div class="customer-stats-grid">
        <div class="customer-stat">
            <span>Tổng khách</span>
            <strong>{{ number_format($totalCustomers) }}</strong>
        </div>
        <div class="customer-stat">
            <span>Mới</span>
            <strong>{{ number_format($countFor(\App\Models\Customer::STATUS_NEW)) }}</strong>
        </div>
        <div class="customer-stat">
            <span>Đang tư vấn</span>
            <strong>{{ number_format($countFor(\App\Models\Customer::STATUS_CONSULTING)) }}</strong>
        </div>
        <div class="customer-stat">
            <span>Đã mua</span>
            <strong>{{ number_format($countFor(\App\Models\Customer::STATUS_PURCHASED)) }}</strong>
        </div>
    </div>

    <form class="customer-filter" method="get" action="{{ route('admin.customers.index') }}">
        <div class="customer-filter-field is-wide">
            <label for="q">Tìm kiếm</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Tên, mã, SĐT, email, xe quan tâm">
        </div>

        <div class="customer-filter-field">
            <label for="source">Nguồn khách</label>
            <select id="source" name="source">
                <option value="">Tất cả nguồn</option>
                @foreach($sourceOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['source'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="customer-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả trạng thái</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="customer-filter-actions">
            <button type="submit">Lọc</button>
            <a href="{{ route('admin.customers.index') }}">Xóa lọc</a>
        </div>
    </form>

    <div class="customers-table-wrap">
        <table class="customers-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Khách hàng</th>
                    <th>Nguồn</th>
                    <th>Trạng thái</th>
                    <th>Xe quan tâm</th>
                    <th>Chăm sóc</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    @php
                        $statusClass = 'customers-status-' . str_replace('_', '-', $customer->status);
                    @endphp
                    <tr>
                        <td class="customers-code">{{ $customer->customer_code }}</td>
                        <td>
                            <div class="customers-name">{{ $customer->full_name }}</div>
                            <div class="customers-meta">
                                {{ $customer->phone }}
                                @if($customer->email)
                                    <span>{{ $customer->email }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="customers-source">{{ $customer->source ?: 'Chưa rõ' }}</span>
                        </td>
                        <td>
                            <span class="customers-status {{ $statusClass }}">{{ $customer->statusLabel() }}</span>
                        </td>
                        <td class="customers-muted">{{ $customer->interested_car ?: '---' }}</td>
                        <td class="customers-muted">
                            {{ $customer->interactions_count }} ghi chú
                            @if($customer->interactions_max_created_at)
                                <span>{{ \Illuminate\Support\Carbon::parse($customer->interactions_max_created_at)->format('d/m/Y H:i') }}</span>
                            @endif
                        </td>
                        <td class="customers-muted">{{ $customer->created_at?->format('d/m/Y') }}</td>
                        <td class="customers-actions">
                            <a href="{{ route('admin.customers.show', $customer) }}">Xem</a>
                            @can('customers.edit')
                                <a href="{{ route('admin.customers.edit', $customer) }}">Sửa</a>
                            @endcan
                            @can('customers.delete')
                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="post" onsubmit="return confirm('Xóa khách hàng này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="is-danger" type="submit">Xóa</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="customers-empty">Chưa có khách hàng.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="customers-pagination">
            {{ $customers->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
