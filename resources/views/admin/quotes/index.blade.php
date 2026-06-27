@extends('layouts.admin')

@section('title', 'Quản lý báo giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-quotes.css')
    @endif
@endpush

@section('content')
@php
    $countFor = fn (string $status): int => (int) ($statusCounts[$status] ?? 0);
@endphp

<div class="admin-quotes-page">
    <div class="admin-quotes-head">
        <div>
            <h1>Quản lý báo giá</h1>
            <p>Bán hàng / Báo giá</p>
        </div>

        @can('quotes.create')
            <a class="admin-quotes-primary" href="{{ route('admin.quotes.create') }}">Tạo báo giá</a>
        @endcan
    </div>

    @if(session('success'))
        <div class="admin-quotes-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="admin-quotes-alert is-error">{{ session('error') }}</div>
    @endif

    <div class="quote-stats-grid">
        <div class="quote-stat">
            <span>Tổng báo giá</span>
            <strong>{{ number_format($totalQuotes) }}</strong>
        </div>
        <div class="quote-stat">
            <span>Đã gửi</span>
            <strong>{{ number_format($countFor(\App\Models\Quote::STATUS_SENT)) }}</strong>
        </div>
        <div class="quote-stat">
            <span>Đã chấp nhận</span>
            <strong>{{ number_format($countFor(\App\Models\Quote::STATUS_ACCEPTED)) }}</strong>
        </div>
        <div class="quote-stat">
            <span>Hết hạn</span>
            <strong>{{ number_format($countFor(\App\Models\Quote::STATUS_EXPIRED)) }}</strong>
        </div>
    </div>

    <form class="quote-filter" method="get" action="{{ route('admin.quotes.index') }}">
        <div class="quote-filter-field is-wide">
            <label for="q">Tìm kiếm</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Mã báo giá, khách hàng, SĐT, VIN, biển số">
        </div>

        <div class="quote-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả trạng thái</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="quote-filter-field">
            <label for="customer_id">Khách hàng</label>
            <select id="customer_id" name="customer_id">
                <option value="">Tất cả khách hàng</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->customer_id }}" @selected((string) $filters['customer_id'] === (string) $customer->customer_id)>
                        {{ $customer->full_name }} - {{ $customer->phone }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="quote-filter-actions">
            <button type="submit">Lọc</button>
            <a href="{{ route('admin.quotes.index') }}">Xóa lọc</a>
        </div>
    </form>

    <div class="quotes-table-wrap">
        <table class="quotes-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>Người lập</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hết hạn</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td class="quotes-code">{{ $quote->quote_code }}</td>
                        <td>
                            <div class="quotes-name">{{ $quote->customer?->full_name ?? 'Khách đã xóa' }}</div>
                            <div class="quotes-meta">
                                {{ $quote->customer?->phone ?? '---' }}
                                @if($quote->customer?->customer_code)
                                    <span>{{ $quote->customer->customer_code }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="quotes-name">{{ $quote->car?->title ?? 'Xe đã xóa' }}</div>
                            <div class="quotes-meta">
                                {{ $quote->car?->license_plate ?: 'Chưa có biển số' }}
                                @if($quote->car?->vin)
                                    <span>VIN {{ $quote->car->vin }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="quotes-muted">{{ $quote->user->name ?? 'Hệ thống' }}</td>
                        <td class="quotes-total">{{ $quote->money('total_price') }}</td>
                        <td>
                            <span class="quotes-status {{ $quote->statusClass() }}">{{ $quote->statusLabel() }}</span>
                        </td>
                        <td class="quotes-muted">{{ $quote->expired_at?->format('d/m/Y') ?: '---' }}</td>
                        <td class="quotes-actions">
                            <a href="{{ route('admin.quotes.show', $quote) }}">Xem</a>
                            <a href="{{ route('admin.quotes.pdf', $quote) }}">PDF</a>
                            @can('quotes.edit')
                                <form action="{{ route('admin.quotes.send', $quote) }}" method="post">
                                    @csrf
                                    <button type="submit">Gửi</button>
                                </form>
                                <a href="{{ route('admin.quotes.edit', $quote) }}">Sửa</a>
                            @endcan
                            @can('quotes.delete')
                                <form action="{{ route('admin.quotes.destroy', $quote) }}" method="post" onsubmit="return confirm('Xóa báo giá này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="is-danger" type="submit">Xóa</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="quotes-empty">Chưa có báo giá.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotes->hasPages())
        <div class="quotes-pagination">
            {{ $quotes->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
