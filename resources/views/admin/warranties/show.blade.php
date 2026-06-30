@extends('layouts.admin')

@section('title', 'Bảo hành ' . $warranty->warranty_code)

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
            <a href="{{ route('admin.warranties.index') }}" class="after-link">Quay lại danh sách</a>
            <h1 class="after-title">{{ $warranty->warranty_code }}</h1>
            <p class="after-subtitle">{{ $warranty->car_display_name }} · {{ $warranty->user?->name ?? 'N/A' }}</p>
        </div>
        <div class="after-actions">
            @can('services.create')
                <a class="after-button-secondary" href="{{ route('admin.service-appointments.create', ['warranty_id' => $warranty->id, 'service_type' => \App\Models\ServiceAppointment::TYPE_MAINTENANCE]) }}">Tạo lịch bảo dưỡng</a>
                <a class="after-button-secondary" href="{{ route('admin.service-appointments.create', ['warranty_id' => $warranty->id, 'service_type' => \App\Models\ServiceAppointment::TYPE_WARRANTY]) }}">Tạo lịch bảo hành</a>
            @endcan
            @can('warranties.edit')
                <a class="after-button" href="{{ route('admin.warranties.edit', $warranty) }}">Sửa</a>
            @endcan
        </div>
    </div>

    <div class="after-layout">
        <div class="after-stack">
            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Thông tin bảo hành</h2>
                    <span class="{{ $warranty->status_badge_class }}">{{ $warranty->status_label }}</span>
                </div>
                <div class="after-info-grid">
                    <div class="after-info-card"><span>Ngày bắt đầu</span><strong>{{ $warranty->start_date?->format('d/m/Y') }}</strong></div>
                    <div class="after-info-card"><span>Ngày kết thúc</span><strong>{{ $warranty->end_date?->format('d/m/Y') }}</strong></div>
                    <div class="after-info-card"><span>Còn lại</span><strong>{{ $warranty->days_remaining >= 0 ? number_format($warranty->days_remaining) . ' ngày' : 'Quá hạn ' . number_format(abs($warranty->days_remaining)) . ' ngày' }}</strong></div>
                    <div class="after-info-card"><span>Thời hạn</span><strong>{{ number_format($warranty->effective_warranty_months) }} tháng</strong></div>
                    <div class="after-info-card"><span>VIN</span><strong>{{ $warranty->vin ?: 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Biển số</span><strong>{{ $warranty->license_plate ?: 'N/A' }}</strong></div>
                    <div class="after-info-card"><span>Giới hạn km</span><strong>{{ $warranty->mileage_limit ? number_format($warranty->mileage_limit) . ' km' : 'Không giới hạn' }}</strong></div>
                    <div class="after-info-card"><span>Ghi chú</span><strong>{!! $warranty->note ? nl2br(e($warranty->note)) : 'N/A' !!}</strong></div>
                </div>
            </section>

            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Lịch hẹn dịch vụ</h2>
                    <span class="after-meta">{{ number_format($warranty->serviceAppointments->count()) }} lịch</span>
                </div>
                <div class="after-table-wrap">
                    <table class="after-table">
                        <thead>
                            <tr>
                                <th>Mã lịch</th>
                                <th>Loại</th>
                                <th>Ngày hẹn</th>
                                <th>Nhân viên</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warranty->serviceAppointments as $appointment)
                                <tr>
                                    <td><a class="after-code" href="{{ route('admin.service-appointments.show', $appointment) }}">{{ $appointment->appointment_code }}</a></td>
                                    <td>{{ $appointment->service_type_label }}</td>
                                    <td>{{ $appointment->appointment_date?->format('d/m/Y') }} {{ $appointment->appointment_time_label }}</td>
                                    <td>{{ $appointment->assignedStaff?->name ?? 'Chưa gán' }}</td>
                                    <td><span class="{{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span></td>
                                    <td><a class="after-button-ghost" href="{{ route('admin.service-appointments.show', $appointment) }}">Xem</a></td>
                                </tr>
                            @empty
                                <tr><td class="after-empty" colspan="6">Chưa có lịch hẹn dịch vụ.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Timeline lịch sử dịch vụ</h2>
                    <span class="after-meta">{{ number_format($warranty->serviceRecords->count()) }} hồ sơ</span>
                </div>
                <div class="after-timeline">
                    @forelse($warranty->serviceRecords as $record)
                        <div class="after-timeline-item">
                            <div class="after-timeline-main">
                                <a class="after-code" href="{{ route('admin.service-records.show', $record) }}">{{ $record->record_code }}</a>
                                <div class="after-main-text">{{ $record->service_type_label }} · {{ $record->service_date?->format('d/m/Y') }}</div>
                                <div class="after-sub-text">{{ $record->work_performed ?: $record->problem_description ?: 'Chưa có mô tả công việc' }}</div>
                            </div>
                            <div>
                                <span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span>
                                <div class="after-meta after-money">{{ number_format((float) $record->total_cost, 0, ',', '.') }} đ</div>
                            </div>
                        </div>
                    @empty
                        <div class="after-empty">Chưa có lịch sử bảo dưỡng / sửa chữa.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="after-stack">
            <section class="after-panel">
                <h2 class="after-panel-title">Khách hàng</h2>
                <div class="after-info-grid">
                    <div class="after-info-card after-field-full"><span>Họ tên</span><strong>{{ $warranty->user?->name ?? 'N/A' }}</strong></div>
                    <div class="after-info-card after-field-full"><span>Điện thoại</span><strong>{{ $warranty->user?->phone ?? 'N/A' }}</strong></div>
                    <div class="after-info-card after-field-full"><span>Email</span><strong>{{ $warranty->user?->email ?? 'N/A' }}</strong></div>
                </div>
            </section>

            <section class="after-panel">
                <h2 class="after-panel-title">Liên kết</h2>
                <div class="after-row-actions">
                    @if($warranty->order)
                        <a class="after-button-secondary" href="{{ route('admin.orders.show', $warranty->order_id) }}">Xem đơn hàng</a>
                    @endif
                    @if($warranty->car)
                        <a class="after-button-secondary" href="{{ route('admin.cars.show', $warranty->car_id) }}">Xem xe</a>
                    @endif
                </div>
            </section>

            @can('warranties.delete')
                <section class="after-panel">
                    <h2 class="after-panel-title">Xóa hồ sơ</h2>
                    <p class="after-subtitle">Lịch hẹn và lịch sử dịch vụ sẽ được giữ lại nhưng bỏ liên kết bảo hành.</p>
                    <form method="POST" action="{{ route('admin.warranties.destroy', $warranty) }}" onsubmit="return confirm('Xóa hồ sơ bảo hành này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="after-button-danger">Xóa bảo hành</button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection
