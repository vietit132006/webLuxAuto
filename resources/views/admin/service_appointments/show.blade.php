@extends('layouts.admin')

@section('title', 'Lịch dịch vụ ' . $appointment->appointment_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
<div class="after-page">
    @include('admin.warranties.partials.flash')

    <div class="after-header">
        <div>
            <a href="{{ route('admin.service-appointments.index') }}" class="after-link">Quay lại danh sách</a>
            <h1 class="after-title">{{ $appointment->appointment_code }}</h1>
            <p class="after-subtitle">{{ $appointment->service_type_label }} · {{ $appointment->appointment_date?->format('d/m/Y') }} {{ $appointment->appointment_time_label }}</p>
        </div>
        <div class="after-actions">
            @can('service_records.create')
                <a class="after-button-secondary" href="{{ route('admin.service-records.create', ['appointment_id' => $appointment->id]) }}">Tạo lịch sử dịch vụ</a>
            @endcan
            @can('services.edit')
                <a class="after-button" href="{{ route('admin.service-appointments.edit', $appointment) }}">Sửa lịch hẹn</a>
            @endcan
        </div>
    </div>

    <div class="after-layout">
        <div class="after-stack">
            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Thông tin lịch hẹn</h2>
                    <span class="{{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span>
                </div>
                <div class="after-info-grid">
                    <div class="after-info-card"><span>Khách hàng</span><strong>{{ $appointment->user?->name ?? 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Điện thoại</span><strong>{{ $appointment->user?->phone ?? 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Xe</span><strong>{{ $appointment->car?->name ?? 'Chưa gán xe' }}</strong></div>
                    <div class="after-info-card"><span>VIN</span><strong>{{ $appointment->car?->vin ?? 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Nhân viên</span><strong>{{ $appointment->assignedStaff?->name ?? 'Chưa gán' }}</strong></div>
                    <div class="after-info-card"><span>Địa điểm</span><strong>{{ $appointment->service_location ?: 'N/A' }}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Ghi chú khách hàng</span><strong>{!! $appointment->customer_note ? nl2br(e($appointment->customer_note)) : 'N/A' !!}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Ghi chú nội bộ</span><strong>{!! $appointment->internal_note ? nl2br(e($appointment->internal_note)) : 'N/A' !!}</strong></div>
                </div>
            </section>

            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Lịch sử dịch vụ đã tạo</h2>
                    <span class="after-meta">{{ number_format($appointment->serviceRecords->count()) }} hồ sơ</span>
                </div>
                <div class="after-timeline">
                    @forelse($appointment->serviceRecords as $record)
                        <div class="after-timeline-item">
                            <div>
                                <a class="after-code" href="{{ route('admin.service-records.show', $record) }}">{{ $record->record_code }}</a>
                                <div class="after-sub-text">{{ $record->service_date?->format('d/m/Y') }} · {{ $record->handledBy?->name ?? 'Chưa gán' }}</div>
                            </div>
                            <span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span>
                        </div>
                    @empty
                        <div class="after-empty">Chưa có lịch sử dịch vụ cho lịch hẹn này.</div>
                    @endforelse
                </div>
            </section>

            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Tài liệu dịch vụ</h2>
                    <span class="after-meta">PDF, JPG, PNG, WEBP tối đa 5MB/file</span>
                </div>

                @can('services.edit')
                    <form method="POST" action="{{ route('admin.service-appointments.files.store', $appointment) }}" enctype="multipart/form-data" class="after-form">
                        @csrf
                        <input type="file" name="service_files[]" class="after-control" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple required>
                        <div class="after-filter-actions"><button type="submit" class="after-button">Tải lên</button></div>
                    </form>
                @endcan

                <div class="after-timeline">
                    @forelse($appointment->files as $file)
                        <div class="after-file-item">
                            <div class="after-file-main">
                                <span class="after-main-text">{{ $file->file_name }}</span>
                                <span class="after-sub-text">{{ $file->uploadedBy?->name ?? 'Hệ thống' }} · {{ $file->created_at?->format('H:i - d/m/Y') }}</span>
                            </div>
                            <div class="after-file-actions">
                                <a class="after-button-ghost" href="{{ route('admin.service-files.view', $file) }}" target="_blank" rel="noopener">Xem</a>
                                <a class="after-button-ghost" href="{{ route('admin.service-files.download', $file) }}">Tải xuống</a>
                                @can('services.edit')
                                    <form method="POST" action="{{ route('admin.service-files.destroy', $file) }}" onsubmit="return confirm('Xóa tài liệu này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="after-button-danger">Xóa</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="after-empty">Chưa có tài liệu dịch vụ.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="after-stack">
            @if($appointment->warranty)
                <section class="after-panel">
                    <h2 class="after-panel-title">Bảo hành liên quan</h2>
                    <p class="after-subtitle">{{ $appointment->warranty->warranty_code }} · {{ $appointment->warranty->status_label }}</p>
                    <a class="after-button-secondary" href="{{ route('admin.warranties.show', $appointment->warranty) }}">Xem bảo hành</a>
                </section>
            @endif

            @can('services.delete')
                <section class="after-panel">
                    <h2 class="after-panel-title">Xóa lịch hẹn</h2>
                    <form method="POST" action="{{ route('admin.service-appointments.destroy', $appointment) }}" onsubmit="return confirm('Xóa lịch hẹn này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="after-button-danger">Xóa lịch hẹn</button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection
