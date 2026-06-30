@extends('layouts.admin')

@section('title', 'Chi tiết lịch lái thử ' . $booking->display_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-test-drives-show.css')
    @endif
@endpush

@php
    $carName = $booking->car ? trim(($booking->car->brand->name ?? '') . ' ' . $booking->car->name) : 'Chưa xác định';
    $appointmentTime = $booking->appointment_time ? substr((string) $booking->appointment_time, 0, 5) : null;
    $appointmentText = $booking->appointment_date
        ? $booking->appointment_date->format('d/m/Y') . ($appointmentTime ? ' ' . $appointmentTime : '')
        : 'Chưa đặt lịch';
    $currentSalesPerson = old('sales_person', $booking->sales_person);
    $timelineItems = collect([
        [
            'time' => $booking->created_at,
            'title' => 'Khách gửi yêu cầu',
            'meta' => $booking->user->name ?? 'Khách hàng',
            'note' => $booking->subject,
            'status' => 'pending',
        ],
    ])->merge($booking->statusHistories->map(function ($history) {
        return [
            'time' => $history->created_at,
            'title' => match ($history->new_status) {
                \App\Models\Ticket::STATUS_APPROVED => 'Admin duyệt',
                \App\Models\Ticket::STATUS_COMPLETED => 'Hoàn thành',
                \App\Models\Ticket::STATUS_REJECTED => 'Đã hủy',
                default => \App\Models\Ticket::labelForTestDriveStatus($history->new_status),
            },
            'meta' => $history->changedBy->name ?? 'Hệ thống',
            'note' => $history->note,
            'status' => $history->new_status,
        ];
    }));
    $relatedQuotes = $booking->quotes;
    $primaryQuote = $relatedQuotes->first();
@endphp

@section('content')
<div class="test-drive-detail">
    @if(session('success'))
        <div class="flash-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
        <div class="flash-alert is-warning">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
        <div class="flash-alert is-error">{{ $errors->first() }}</div>
    @endif

    <div class="detail-header">
        <div>
            <a class="back-link" href="{{ route('admin.test_drives.index') }}">Quay lại danh sách</a>
            <h1>{{ $booking->display_code }}</h1>
            <p>Ngày tạo: {{ $booking->created_at?->format('d/m/Y H:i') }}</p>
        </div>
        <div class="detail-actions">
            @if($booking->status === \App\Models\Ticket::STATUS_COMPLETED)
                @if($primaryQuote)
                    @can('quotes.view')
                        <a class="btn-secondary" href="{{ route('admin.quotes.show', $primaryQuote) }}">Xem báo giá</a>
                    @endcan
                @else
                    @can('quotes.create')
                        <a class="btn-primary" href="{{ route('admin.test_drives.quotes.create', $booking->ticket_id) }}">Tạo báo giá</a>
                    @endcan
                @endif
            @endif
            <span class="status-badge {{ $booking->test_drive_status_badge_class }}">{{ $booking->test_drive_status_label }}</span>
        </div>
    </div>

    <div class="detail-grid">
        <main class="detail-main">
            <section class="panel">
                <h2 class="panel-title">Thông tin yêu cầu</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span>Khách hàng</span>
                        <strong>{{ $booking->user->name ?? 'Khách vãng lai' }}</strong>
                        <small>{{ $booking->user->email ?? 'Chưa có email' }}</small>
                        <small>{{ $booking->user->phone ?? 'Chưa có SĐT' }}</small>
                    </div>
                    <div class="info-item">
                        <span>Xe lái thử</span>
                        <strong>{{ $carName }}</strong>
                        <small>{{ $booking->car?->license_plate ?: 'Chưa có biển số' }}</small>
                        @if($booking->car?->vin)
                            <small>VIN {{ $booking->car->vin }}</small>
                        @endif
                    </div>
                    <div class="info-item">
                        <span>Lịch hẹn</span>
                        <strong>{{ $appointmentText }}</strong>
                        <small>{{ $booking->showroom ?: 'Chưa chọn showroom' }}</small>
                    </div>
                    <div class="info-item">
                        <span>Nhân viên phụ trách</span>
                        <strong>{{ $booking->sales_person ?: 'Chưa phân công' }}</strong>
                    </div>
                    @if($booking->liveSession)
                        <div class="info-item">
                            <span>Nguồn livestream</span>
                            <strong>
                                @can('live.view')
                                    <a href="{{ route('admin.live.show', $booking->liveSession) }}">{{ $booking->liveSession->live_code }}</a>
                                @else
                                    {{ $booking->liveSession->live_code }}
                                @endcan
                            </strong>
                            <small>{{ $booking->liveSession->title }}</small>
                            @if($booking->liveLead)
                                <small>
                                    @can('live.leads.view')
                                        <a href="{{ route('admin.live.leads.show', $booking->liveLead) }}">Live lead #{{ $booking->liveLead->id }}</a>
                                    @else
                                        Live lead #{{ $booking->liveLead->id }}
                                    @endcan
                                </small>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="request-box">
                    <div class="request-title">{{ $booking->subject }}</div>
                    <div class="request-message">{!! nl2br(e($booking->message)) !!}</div>
                </div>
            </section>

            @can('quotes.view')
                @if($relatedQuotes->isNotEmpty())
                    <section class="panel">
                        <h2 class="panel-title">Báo giá liên quan</h2>
                        <div class="quote-list">
                            @foreach($relatedQuotes as $quote)
                                <a class="quote-row" href="{{ route('admin.quotes.show', $quote) }}">
                                    <div>
                                        <strong>{{ $quote->quote_code }}</strong>
                                        <span>
                                            {{ $quote->customer?->full_name ?? 'Khách đã xóa' }}
                                            · {{ $quote->car?->title ?? 'Xe đã xóa' }}
                                        </span>
                                        <small>{{ $quote->user->name ?? 'Hệ thống' }} · {{ $quote->created_at?->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div class="quote-row-side">
                                        <span class="quote-status-pill {{ $quote->statusClass() }}">{{ $quote->statusLabel() }}</span>
                                        <strong>{{ $quote->money('total_price') }}</strong>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endcan

            <section class="panel">
                <h2 class="panel-title">Timeline xử lý</h2>
                <div class="timeline">
                    @foreach($timelineItems as $item)
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <strong>{{ $item['time']?->format('d/m') ?? '--/--' }}</strong>
                                <span>{{ $item['time']?->format('H:i') ?? '--:--' }}</span>
                            </div>
                            <div class="timeline-marker {{ \App\Models\Ticket::badgeClassForTestDriveStatus($item['status']) }}"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">{{ $item['title'] }}</div>
                                <div class="timeline-meta">{{ $item['meta'] }}</div>
                                @if($item['note'])
                                    <p>{{ $item['note'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title">Ghi chú nội bộ</h2>
                <div class="note-list">
                    @forelse($booking->notes as $note)
                        <div class="note-message">
                            <div class="note-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($note->user->name ?? 'A', 0, 1)) }}</div>
                            <div class="note-bubble">
                                <div class="note-head">
                                    <strong>{{ $note->user->name ?? 'Admin' }}</strong>
                                    <span>{{ $note->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <p>{!! nl2br(e($note->note)) !!}</p>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Chưa có ghi chú nội bộ.</div>
                    @endforelse
                </div>

                @can('test_drives.edit')
                    <form class="note-form" method="post" action="{{ route('admin.test_drives.notes.store', $booking->ticket_id) }}">
                        @csrf
                        <textarea name="note" rows="3" placeholder="Ví dụ: Khách thích màu trắng, muốn lái bản Turbo, đã gọi xác nhận..." required>{{ old('note') }}</textarea>
                        <button class="btn-primary" type="submit">Thêm ghi chú</button>
                    </form>
                @endcan
            </section>

            <section class="panel">
                <h2 class="panel-title">Tài liệu</h2>

                @can('test_drives.edit')
                    <form class="file-form" method="post" action="{{ route('admin.test_drives.files.store', $booking->ticket_id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple required>
                        <button class="btn-primary" type="submit">Upload</button>
                    </form>
                @endcan

                <div class="file-list">
                    @forelse($booking->files as $file)
                        <div class="file-row">
                            <div>
                                <strong>{{ $file->file_name }}</strong>
                                <span>{{ $file->uploadedBy->name ?? 'Admin' }} · {{ $file->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="file-actions">
                                <a href="{{ route('admin.test_drives.files.view', [$booking->ticket_id, $file]) }}" target="_blank" rel="noopener">Xem</a>
                                <a href="{{ route('admin.test_drives.files.download', [$booking->ticket_id, $file]) }}">Download</a>
                                @can('test_drives.delete')
                                    <form method="post" action="{{ route('admin.test_drives.files.destroy', [$booking->ticket_id, $file]) }}" onsubmit="return confirm('Xóa tài liệu này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Xóa</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Chưa có tài liệu.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title">Nhật ký thao tác</h2>
                <div class="activity-list">
                    @forelse($booking->activityLogs as $log)
                        <div class="activity-item">
                            <div class="activity-main">
                                <strong>{{ $log->user->name ?? 'Hệ thống' }}</strong>
                                <span>{{ $log->action_label }}</span>
                                <small>{{ $log->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="activity-detail">
                                @if($log->old_value || $log->new_value)
                                    <span>{{ $log->old_value ?: 'N/A' }}</span>
                                    <b>→</b>
                                    <span>{{ $log->new_value ?: 'N/A' }}</span>
                                @else
                                    <span>{{ $log->description ?: 'Đã ghi nhận thao tác' }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Chưa có nhật ký thao tác.</div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="detail-side">
            <section class="panel">
                <h2 class="panel-title">Thông tin lịch hẹn</h2>
                @can('test_drives.edit')
                    <form class="side-form" method="post" action="{{ route('admin.test_drives.updateAppointment', $booking->ticket_id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-field">
                            <label for="appointment-date">Ngày hẹn</label>
                            <input id="appointment-date" type="date" name="appointment_date" value="{{ old('appointment_date', $booking->appointment_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="form-field">
                            <label for="appointment-time">Giờ hẹn</label>
                            <input id="appointment-time" type="time" name="appointment_time" value="{{ old('appointment_time', $appointmentTime) }}">
                        </div>
                        <div class="form-field">
                            <label for="showroom">Showroom</label>
                            <input id="showroom" type="text" name="showroom" value="{{ old('showroom', $booking->showroom) }}" maxlength="255" placeholder="Lux Auto Quận 1">
                        </div>
                        <div class="form-field">
                            <label for="sales-person">Nhân viên phụ trách</label>
                            <select id="sales-person" name="sales_person">
                                <option value="">Chưa phân công</option>
                                @if($currentSalesPerson && !in_array($currentSalesPerson, $salesPeople, true))
                                    <option value="{{ $currentSalesPerson }}" selected>{{ $currentSalesPerson }}</option>
                                @endif
                                @foreach($salesPeople as $person)
                                    <option value="{{ $person }}" @selected($currentSalesPerson === $person)>{{ $person }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn-primary" type="submit">Lưu lịch hẹn</button>
                    </form>
                @else
                    <div class="readonly-list">
                        <div><span>Ngày giờ</span><strong>{{ $appointmentText }}</strong></div>
                        <div><span>Showroom</span><strong>{{ $booking->showroom ?: 'N/A' }}</strong></div>
                        <div><span>Nhân viên</span><strong>{{ $booking->sales_person ?: 'N/A' }}</strong></div>
                    </div>
                @endcan
            </section>

            <section class="panel">
                <h2 class="panel-title">Cập nhật trạng thái</h2>
                @can('test_drives.edit')
                    @if(count($nextStatusOptions) > 0)
                        <form class="side-form" method="post" action="{{ route('admin.test_drives.updateStatus', $booking->ticket_id) }}">
                            @csrf
                            <div class="form-field">
                                <label for="status">Trạng thái mới</label>
                                <select id="status" name="status" required>
                                    @foreach($nextStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="status-note">Ghi chú</label>
                                <textarea id="status-note" name="note" rows="3" maxlength="1000">{{ old('note') }}</textarea>
                            </div>
                            <button class="btn-primary" type="submit">Lưu trạng thái</button>
                        </form>
                    @else
                        <div class="closed-state">
                            <strong>{{ $booking->test_drive_status_label }}</strong>
                            <span>Không còn trạng thái hợp lệ tiếp theo.</span>
                        </div>
                    @endif
                @else
                    <div class="closed-state">
                        <strong>{{ $booking->test_drive_status_label }}</strong>
                        <span>Bạn không có quyền cập nhật.</span>
                    </div>
                @endcan
            </section>
        </aside>
    </div>
</div>
@endsection
