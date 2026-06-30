@extends('layouts.admin')

@section('title', 'Lịch sử dịch vụ ' . $record->record_code)

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
            <a href="{{ route('admin.service-records.index') }}" class="after-link">Quay lại danh sách</a>
            <h1 class="after-title">{{ $record->record_code }}</h1>
            <p class="after-subtitle">{{ $record->service_type_label }} · {{ $record->service_date?->format('d/m/Y') }}</p>
        </div>
        <div class="after-actions">
            @can('service_records.edit')
                <a class="after-button" href="{{ route('admin.service-records.edit', $record) }}">Sửa lịch sử</a>
            @endcan
        </div>
    </div>

    <div class="after-layout">
        <div class="after-stack">
            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Thông tin dịch vụ</h2>
                    <span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span>
                </div>
                <div class="after-info-grid">
                    <div class="after-info-card"><span>Khách hàng</span><strong>{{ $record->user?->name ?? 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Xe</span><strong>{{ $record->car?->name ?? 'Chưa gán xe' }}</strong></div>
                    <div class="after-info-card"><span>VIN</span><strong>{{ $record->car?->vin ?? 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Số km</span><strong>{{ $record->mileage ? number_format($record->mileage) . ' km' : 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Người xử lý</span><strong>{{ $record->handledBy?->name ?? 'Chưa gán' }}</strong></div>
                    <div class="after-info-card"><span>Tổng chi phí</span><strong class="after-money">{{ number_format((float) $record->total_cost, 0, ',', '.') }} đ</strong></div>
                    <div class="after-info-card"><span>Chi phí công</span><strong>{{ number_format((float) $record->labor_cost, 0, ',', '.') }} đ</strong></div>
                    <div class="after-info-card"><span>Chi phí phụ tùng</span><strong>{{ number_format((float) $record->parts_cost, 0, ',', '.') }} đ</strong></div>
                    <div class="after-info-card after-field-wide"><span>Bảo dưỡng tiếp</span><strong>{{ $record->next_service_date?->format('d/m/Y') ?? 'Chưa hẹn' }}{{ $record->next_service_mileage ? ' · ' . number_format($record->next_service_mileage) . ' km' : '' }}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Vấn đề khách báo</span><strong>{!! $record->problem_description ? nl2br(e($record->problem_description)) : 'N/A' !!}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Công việc đã làm</span><strong>{!! $record->work_performed ? nl2br(e($record->work_performed)) : 'N/A' !!}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Phụ tùng thay thế</span><strong>{!! $record->parts_replaced ? nl2br(e($record->parts_replaced)) : 'N/A' !!}</strong></div>
                    <div class="after-info-card after-field-wide"><span>Ghi chú</span><strong>{!! $record->note ? nl2br(e($record->note)) : 'N/A' !!}</strong></div>
                </div>
            </section>

            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Tài liệu dịch vụ</h2>
                    <span class="after-meta">PDF, JPG, PNG, WEBP tối đa 5MB/file</span>
                </div>

                @can('service_records.edit')
                    <form method="POST" action="{{ route('admin.service-records.files.store', $record) }}" enctype="multipart/form-data" class="after-form">
                        @csrf
                        <input type="file" name="service_files[]" class="after-control" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple required>
                        <div class="after-filter-actions"><button type="submit" class="after-button">Tải lên</button></div>
                    </form>
                @endcan

                <div class="after-timeline">
                    @forelse($record->files as $file)
                        <div class="after-file-item">
                            <div class="after-file-main">
                                <span class="after-main-text">{{ $file->file_name }}</span>
                                <span class="after-sub-text">{{ $file->uploadedBy?->name ?? 'Hệ thống' }} · {{ $file->created_at?->format('H:i - d/m/Y') }}</span>
                            </div>
                            <div class="after-file-actions">
                                <a class="after-button-ghost" href="{{ route('admin.service-files.view', $file) }}" target="_blank" rel="noopener">Xem</a>
                                <a class="after-button-ghost" href="{{ route('admin.service-files.download', $file) }}">Tải xuống</a>
                                @can('service_records.edit')
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
            @if($record->serviceAppointment)
                <section class="after-panel">
                    <h2 class="after-panel-title">Lịch hẹn nguồn</h2>
                    <p class="after-subtitle">{{ $record->serviceAppointment->appointment_code }}</p>
                    <a class="after-button-secondary" href="{{ route('admin.service-appointments.show', $record->serviceAppointment) }}">Xem lịch hẹn</a>
                </section>
            @endif

            @if($record->warranty)
                <section class="after-panel">
                    <h2 class="after-panel-title">Bảo hành</h2>
                    <p class="after-subtitle">{{ $record->warranty->warranty_code }}</p>
                    <a class="after-button-secondary" href="{{ route('admin.warranties.show', $record->warranty) }}">Xem bảo hành</a>
                </section>
            @endif

            @can('service_records.delete')
                <section class="after-panel">
                    <h2 class="after-panel-title">Xóa lịch sử</h2>
                    <form method="POST" action="{{ route('admin.service-records.destroy', $record) }}" onsubmit="return confirm('Xóa lịch sử dịch vụ này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="after-button-danger">Xóa lịch sử</button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection
