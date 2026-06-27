@extends('layouts.admin')

@section('title', $customer->full_name)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-customers.css')
    @endif
@endpush

@section('content')
@php
    $statusClass = 'customers-status-' . str_replace('_', '-', $customer->status);
@endphp

<div class="admin-customers-page">
    <div class="admin-customers-head">
        <div>
            <h1>{{ $customer->full_name }}</h1>
            <p>Bán hàng / Khách hàng / {{ $customer->customer_code }}</p>
        </div>

        <div class="customer-head-actions">
            <a class="admin-customers-secondary" href="{{ route('admin.customers.index') }}">Danh sách</a>
            @can('customers.edit')
                <a class="admin-customers-primary" href="{{ route('admin.customers.edit', $customer) }}">Sửa khách hàng</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="admin-customers-alert is-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-customers-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="customer-detail-panel">
        <div class="customer-profile">
            <div>
                <span class="customers-code">{{ $customer->customer_code }}</span>
                <h2>{{ $customer->full_name }}</h2>
                <p>{{ $customer->phone }}{{ $customer->email ? ' / ' . $customer->email : '' }}</p>
            </div>
            <span class="customers-status {{ $statusClass }}">{{ $customer->statusLabel() }}</span>
        </div>

        <dl class="customer-detail-grid">
            <div>
                <dt>Nguồn khách</dt>
                <dd>{{ $customer->source ?: 'Chưa rõ' }}</dd>
            </div>
            <div>
                <dt>Xe quan tâm</dt>
                <dd>{{ $customer->interested_car ?: '---' }}</dd>
            </div>
            <div>
                <dt>Giới tính</dt>
                <dd>{{ $customer->genderLabel() }}</dd>
            </div>
            <div>
                <dt>Ngày sinh</dt>
                <dd>{{ $customer->birthday?->format('d/m/Y') ?: '---' }}</dd>
            </div>
            <div>
                <dt>Tỉnh/thành</dt>
                <dd>{{ $customer->province ?: '---' }}</dd>
            </div>
            <div>
                <dt>Nghề nghiệp</dt>
                <dd>{{ $customer->occupation ?: '---' }}</dd>
            </div>
            <div class="is-wide">
                <dt>Địa chỉ</dt>
                <dd>{{ $customer->address ?: '---' }}</dd>
            </div>
            <div>
                <dt>Người tạo</dt>
                <dd>{{ $customer->creator->name ?? 'Hệ thống' }}</dd>
            </div>
            <div>
                <dt>Ngày tạo</dt>
                <dd>{{ $customer->created_at?->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </section>

    @if($customer->note)
        <section class="customer-note-panel">
            <h2>Ghi chú khách hàng</h2>
            <p>{{ $customer->note }}</p>
        </section>
    @endif

    @can('customers.edit')
        <section class="customer-note-panel">
            <h2>Thêm ghi chú chăm sóc</h2>
            <form class="customer-interaction-form" method="post" action="{{ route('admin.customers.interactions.store', $customer) }}">
                @csrf
                <textarea name="note" rows="4" required>{{ old('note') }}</textarea>
                <button class="admin-customers-primary" type="submit">Thêm ghi chú</button>
            </form>
        </section>
    @endcan

    <section class="customer-timeline-panel">
        <div class="customer-section-title">
            <h2>Lịch sử chăm sóc</h2>
            <span>{{ $interactions->total() }} ghi chú</span>
        </div>

        <div class="customer-timeline">
            @forelse($interactions as $interaction)
                <article class="customer-timeline-item">
                    <div class="customer-timeline-dot"></div>
                    <div class="customer-timeline-body">
                        <div class="customer-timeline-meta">
                            <strong>{{ $interaction->creator->name ?? 'Hệ thống' }}</strong>
                            <span>{{ $interaction->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        <p>{{ $interaction->note }}</p>
                    </div>
                </article>
            @empty
                <div class="customers-empty">Chưa có ghi chú chăm sóc.</div>
            @endforelse
        </div>

        @if($interactions->hasPages())
            <div class="customers-pagination">
                {{ $interactions->links('pagination.lux') }}
            </div>
        @endif
    </section>
</div>
@endsection
