@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng ' . $order->display_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-show.css')
    @endif
@endpush

@php
    $depositAmount = (float) ($order->deposit_amount ?? 0);
    $depositDateInput = old('deposit_date', $order->deposit_date ? $order->deposit_date->format('Y-m-d\TH:i') : '');
    $depositMethod = old('deposit_method', $order->deposit_method);
    $depositReference = old('deposit_reference', $order->deposit_reference);
    $depositNote = old('deposit_note', $order->deposit_note);
    $needsDepositInfo = $depositAmount <= 0 || !$order->deposit_date || blank($order->deposit_method) || blank($order->deposit_note);
    $needsDepositForStatus = $needsDepositInfo && \App\Models\Order::normalizeStatus($order->status) !== \App\Models\Order::STATUS_DEPOSITED;
    $isDeliveryOldInput = old('_delivery_form') === '1';
    $selectedStatus = (string) old('status', $order->status);
    if ($isDeliveryOldInput) {
        $selectedStatus = (string) $order->status;
    }
    $showStatusDepositFields = $needsDepositForStatus && $selectedStatus === (string) \App\Models\Order::STATUS_DEPOSITED;
    $deliveryIsDelivered = $delivery->status === \App\Models\Delivery::STATUS_DELIVERED;
    $selectedDeliveryStatus = (string) ($isDeliveryOldInput ? old('status', $delivery->status) : $delivery->status);
    $deliveryExpectedInput = old('expected_delivery_date', $delivery->expected_delivery_date ? $delivery->expected_delivery_date->format('Y-m-d\TH:i') : '');
    $deliveryActualInput = old('actual_delivery_date', $delivery->actual_delivery_date ? $delivery->actual_delivery_date->format('Y-m-d\TH:i') : '');
    $deliveryLocationInput = old('delivery_location', $delivery->delivery_location);
    $deliveryStaffInput = old('delivery_staff_id', $delivery->delivery_staff_id);
    $deliveryNoteInput = old('note', $delivery->note);
    $deliveryChecklist = $isDeliveryOldInput ? old('checklist_data', []) : ($delivery->checklist_data ?: []);
    $deliveryChecklistTotal = count($deliveryChecklistOptions);
    $deliveryChecklistDone = collect($deliveryChecklistOptions)->keys()->filter(fn ($key) => !empty($deliveryChecklist[$key]))->count();
@endphp

@section('content')
<div class="wrap">
    @if(session('success'))
        <div class="flash-alert">
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="error-alert">
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    <div class="order-header">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="back-link">Quay lại danh sách</a>
            <h1 class="order-id">{{ $order->display_code }}</h1>
            <div class="order-date">Ngày tạo: {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}</div>
        </div>
        <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
    </div>

    <div class="order-grid">
        <div class="main-content">
            <section class="panel">
                <h2 class="panel-title">Danh sách xe</h2>

                <div class="car-list">
                    @forelse($order->details as $detail)
                        <div class="car-item">
                            @if($detail->car && $detail->car->image)
                                <img src="{{ asset('storage/' . $detail->car->image) }}" class="car-img" alt="{{ $detail->car->name }}">
                            @else
                                <div class="car-img-placeholder">NO IMAGE</div>
                            @endif

                            <div class="car-info">
                                <div class="car-name">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                                <div class="car-meta">Số lượng: {{ $detail->quantity }}</div>
                            </div>

                            <div class="car-price">{{ number_format((float) $detail->price, 0, ',', '.') }} đ</div>
                        </div>
                    @empty
                        <div class="empty-state">Đơn hàng chưa có xe.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel delivery-panel">
                <div class="delivery-panel-head">
                    <div>
                        <h2 class="panel-title">Thông tin giao xe</h2>
                        <div class="delivery-subtitle">
                            Checklist {{ $deliveryChecklistDone }}/{{ $deliveryChecklistTotal }}
                            @if($delivery->stock_deducted_at)
                                · Trừ kho: {{ $delivery->stock_deducted_at->format('H:i - d/m/Y') }}
                            @endif
                        </div>
                    </div>
                    <span class="delivery-badge {{ $delivery->status_badge_class }}">{{ $delivery->status_label }}</span>
                </div>

                @if($deliveryIsDelivered)
                    <div class="delivery-lock-alert">
                        Xe đã được giao, trạng thái giao xe đã khóa. Nếu cần hoàn kho, vui lòng dùng chức năng điều chỉnh tồn kho.
                    </div>
                @elseif(!$canManageDelivery)
                    <div class="delivery-lock-alert is-muted">
                        Bạn chỉ có quyền xem thông tin giao xe.
                    </div>
                @endif

                <form action="{{ route('admin.orders.updateDelivery', $order->order_id) }}" method="POST" class="delivery-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_delivery_form" value="1">

                    <fieldset class="delivery-fieldset" @disabled(!$canManageDelivery || $deliveryIsDelivered)>
                        <div class="delivery-form-grid">
                            <div class="form-field">
                                <label for="expected_delivery_date" class="form-label">Ngày giao dự kiến</label>
                                <input id="expected_delivery_date" type="datetime-local" name="expected_delivery_date" class="form-control" value="{{ $deliveryExpectedInput }}">
                            </div>

                            <div class="form-field">
                                <label for="actual_delivery_date" class="form-label">Ngày giao thực tế</label>
                                <input id="actual_delivery_date" type="datetime-local" name="actual_delivery_date" class="form-control" value="{{ $deliveryActualInput }}">
                            </div>

                            <div class="form-field">
                                <label for="delivery_location" class="form-label">Địa điểm giao xe</label>
                                <input id="delivery_location" type="text" name="delivery_location" class="form-control" value="{{ $deliveryLocationInput }}" maxlength="255">
                            </div>

                            <div class="form-field">
                                <label for="delivery_staff_id" class="form-label">Nhân viên bàn giao</label>
                                <select id="delivery_staff_id" name="delivery_staff_id" class="form-control">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach($deliveryStaffOptions as $staff)
                                        <option value="{{ $staff->user_id }}" @selected((string) $deliveryStaffInput === (string) $staff->user_id)>
                                            {{ $staff->name }}{{ $staff->email ? ' - ' . $staff->email : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-field">
                                <label for="delivery_status" class="form-label">Trạng thái giao xe</label>
                                <select id="delivery_status" name="status" class="form-control">
                                    @foreach($deliveryStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($selectedDeliveryStatus === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-field form-field-wide">
                                <label for="delivery_note" class="form-label">Ghi chú bàn giao</label>
                                <textarea id="delivery_note" name="note" class="form-control" rows="3">{{ $deliveryNoteInput }}</textarea>
                            </div>
                        </div>

                        <div class="delivery-checklist-block">
                            <div class="delivery-section-title">Checklist bàn giao</div>
                            <div class="delivery-checklist">
                                @foreach($deliveryChecklistOptions as $key => $label)
                                    @php($isChecked = !empty($deliveryChecklist[$key]))
                                    <label class="delivery-check-item{{ $isChecked ? ' is-done' : '' }}">
                                        <input type="checkbox" name="checklist_data[{{ $key }}]" value="1" @checked($isChecked)>
                                        <span class="check-mark" aria-hidden="true"></span>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </fieldset>

                    @if($canManageDelivery && !$deliveryIsDelivered)
                        <button type="submit" class="btn-submit">Lưu thông tin giao xe</button>
                    @endif
                </form>

                <div class="delivery-files-block">
                    <div class="delivery-section-title">Tài liệu giao xe</div>

                    @can('orders.edit')
                        <form action="{{ route('admin.orders.deliveryFiles.store', $order->order_id) }}" method="POST" enctype="multipart/form-data" class="delivery-upload-form">
                            @csrf
                            <label for="delivery_files" class="file-upload-label">Upload PDF, JPG, PNG, WEBP tối đa 5MB/file</label>
                            <div class="delivery-upload-row">
                                <input id="delivery_files" type="file" name="delivery_files[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" multiple>
                                <button type="submit" class="btn-submit btn-upload">Tải lên</button>
                            </div>
                        </form>
                    @endcan

                    <div class="delivery-file-list">
                        @forelse($delivery->files as $file)
                            <div class="delivery-file-item">
                                <div class="delivery-file-main">
                                    <div class="delivery-file-name">{{ $file->file_name }}</div>
                                    <div class="delivery-file-meta">
                                        {{ $file->uploadedBy->name ?? 'Hệ thống' }}
                                        · {{ $file->created_at ? $file->created_at->format('H:i - d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                                <div class="delivery-file-actions">
                                    <a href="{{ route('admin.orders.deliveryFiles.view', $file) }}" target="_blank" rel="noopener" class="btn-file">Xem</a>
                                    <a href="{{ route('admin.orders.deliveryFiles.download', $file) }}" class="btn-file">Tải xuống</a>
                                    @can('orders.edit')
                                        <form action="{{ route('admin.orders.deliveryFiles.destroy', $file) }}" method="POST" onsubmit="return confirm('Xóa tài liệu giao xe này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-file btn-file-danger">Xóa</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">Chưa có tài liệu giao xe.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title">Lịch sử trạng thái</h2>

                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Trạng thái cũ</th>
                                <th>Trạng thái mới</th>
                                <th>Người cập nhật</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->statusHistories as $history)
                                <tr>
                                    <td>{{ $history->created_at ? $history->created_at->format('H:i - d/m/Y') : 'N/A' }}</td>
                                    <td>{{ \App\Models\Order::labelForStatus($history->old_status) }}</td>
                                    <td>
                                        <span class="badge badge-{{ \App\Models\Order::normalizeStatus($history->new_status) ?? 'unknown' }}">
                                            {{ \App\Models\Order::labelForStatus($history->new_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="history-user">{{ $history->user->name ?? 'Hệ thống' }}</div>
                                        <div class="history-email">{{ $history->user->email ?? '' }}</div>
                                    </td>
                                    <td>{{ $history->note ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Chưa có lịch sử trạng thái.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="sidebar-content">
            <section class="panel">
                <h2 class="panel-title">Khách hàng</h2>
                <div class="info-group">
                    <div class="info-label">Họ tên</div>
                    <div class="info-value">{{ $order->user->name ?? 'Khách ẩn danh' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $order->user->email ?? 'N/A' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value">{{ $order->user->phone ?? 'N/A' }}</div>
                </div>
            </section>

            @if($order->quote)
                <section class="panel source-panel">
                    <h2 class="panel-title">Nguồn tạo đơn</h2>
                    <div class="info-group">
                        <div class="info-label">Báo giá</div>
                        <div class="info-value">
                            @can('quotes.view')
                                <a class="order-source-link" href="{{ route('admin.quotes.show', $order->quote) }}">
                                    Từ báo giá {{ $order->quote->quote_code }}
                                </a>
                            @else
                                Từ báo giá {{ $order->quote->quote_code }}
                            @endcan
                        </div>
                    </div>
                </section>
            @endif

            <section class="panel">
                <h2 class="panel-title">Thanh toán</h2>
                <div class="summary-row">
                    <span>Tổng tiền</span>
                    <strong>{{ number_format((float) $order->total_price, 0, ',', '.') }} đ</strong>
                </div>
                <div class="summary-row">
                    <span>Tiền cọc</span>
                    <strong>{{ number_format($depositAmount, 0, ',', '.') }} đ</strong>
                </div>
                <div class="summary-total">
                    <span>Còn lại phải thanh toán</span>
                    <strong>{{ number_format((float) $order->remaining_amount, 0, ',', '.') }} đ</strong>
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title">Thông tin đặt cọc</h2>
                <div class="info-group">
                    <div class="info-label">Tiền cọc</div>
                    <div class="info-value">{{ number_format($depositAmount, 0, ',', '.') }} đ</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Ngày cọc</div>
                    <div class="info-value">{{ $order->deposit_date ? $order->deposit_date->format('H:i - d/m/Y') : 'N/A' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Phương thức thanh toán</div>
                    <div class="info-value">{{ $order->deposit_method_label }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Mã giao dịch</div>
                    <div class="info-value">{{ $order->deposit_reference ?: 'N/A' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Ghi chú</div>
                    <div class="info-value note-value">{!! $order->deposit_note ? nl2br(e($order->deposit_note)) : 'N/A' !!}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Người xác nhận</div>
                    <div class="info-value">{{ $order->depositConfirmer->name ?? 'N/A' }}</div>
                </div>

                @can('orders.edit')
                    <form action="{{ route('admin.orders.updateDeposit', $order->order_id) }}" method="POST" class="deposit-update-form">
                        @csrf
                        @method('PATCH')

                        <div class="deposit-form-grid">
                            <div class="form-field">
                                <label for="deposit_amount_update" class="form-label">Tiền cọc</label>
                                <input id="deposit_amount_update" type="number" name="deposit_amount" class="form-control" min="0" step="1000" value="{{ old('deposit_amount', $depositAmount > 0 ? (int) $depositAmount : '') }}" required>
                            </div>

                            <div class="form-field">
                                <label for="deposit_date_update" class="form-label">Ngày cọc</label>
                                <input id="deposit_date_update" type="datetime-local" name="deposit_date" class="form-control" value="{{ $depositDateInput }}" required>
                            </div>

                            <div class="form-field">
                                <label for="deposit_method_update" class="form-label">Phương thức</label>
                                <select id="deposit_method_update" name="deposit_method" class="form-control" required>
                                    <option value="">Chọn phương thức</option>
                                    @foreach($depositMethodOptions as $value => $label)
                                        <option value="{{ $value }}" @selected((string) $depositMethod === (string) $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-field">
                                <label for="deposit_reference_update" class="form-label">Mã giao dịch</label>
                                <input id="deposit_reference_update" type="text" name="deposit_reference" class="form-control" value="{{ $depositReference }}" maxlength="255">
                            </div>

                            <div class="form-field form-field-wide">
                                <label for="deposit_note_update" class="form-label">Ghi chú</label>
                                <textarea id="deposit_note_update" name="deposit_note" class="form-control" rows="3">{{ $depositNote }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Lưu thông tin cọc</button>
                    </form>
                @endcan
            </section>

            @can('orders.edit')
                <section class="panel">
                    <h2 class="panel-title">Cập nhật trạng thái</h2>
                    <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" class="status-update-form" data-status-deposit-form data-deposited-value="{{ \App\Models\Order::STATUS_DEPOSITED }}">
                        @csrf
                        <label for="status" class="form-label">Trạng thái</label>
                        <select id="status" name="status" class="form-control" data-status-select>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>

                        @if($needsDepositForStatus)
                            <div class="deposit-status-fields{{ $showStatusDepositFields ? '' : ' is-hidden' }}" data-deposit-status-fields>
                                <div class="form-field">
                                    <label for="status_deposit_amount" class="form-label">Tiền cọc</label>
                                    <input id="status_deposit_amount" type="number" name="deposit_amount" class="form-control" min="0" step="1000" value="{{ old('deposit_amount', $depositAmount > 0 ? (int) $depositAmount : '') }}" data-required-when-deposited>
                                </div>

                                <div class="form-field">
                                    <label for="status_deposit_date" class="form-label">Ngày cọc</label>
                                    <input id="status_deposit_date" type="datetime-local" name="deposit_date" class="form-control" value="{{ old('deposit_date', $order->deposit_date ? $order->deposit_date->format('Y-m-d\TH:i') : '') }}" data-required-when-deposited>
                                </div>

                                <div class="form-field">
                                    <label for="status_deposit_method" class="form-label">Phương thức</label>
                                    <select id="status_deposit_method" name="deposit_method" class="form-control" data-required-when-deposited>
                                        <option value="">Chọn phương thức</option>
                                        @foreach($depositMethodOptions as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old('deposit_method', $order->deposit_method) === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-field">
                                    <label for="status_deposit_reference" class="form-label">Mã giao dịch</label>
                                    <input id="status_deposit_reference" type="text" name="deposit_reference" class="form-control" value="{{ old('deposit_reference', $order->deposit_reference) }}" maxlength="255">
                                </div>

                                <div class="form-field form-field-wide">
                                    <label for="status_deposit_note" class="form-label">Ghi chú đặt cọc</label>
                                    <textarea id="status_deposit_note" name="deposit_note" class="form-control" rows="3" data-required-when-deposited>{{ old('deposit_note', $order->deposit_note) }}</textarea>
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="btn-submit">Lưu trạng thái</button>
                    </form>
                </section>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-status-deposit-form]').forEach((form) => {
                const select = form.querySelector('[data-status-select]');
                const fields = form.querySelector('[data-deposit-status-fields]');

                if (!select || !fields) {
                    return;
                }

                const requiredControls = fields.querySelectorAll('[data-required-when-deposited]');

                const syncDepositFields = () => {
                    const shouldShow = select.value === form.dataset.depositedValue;
                    fields.classList.toggle('is-hidden', !shouldShow);
                    requiredControls.forEach((control) => {
                        control.required = shouldShow;
                    });
                };

                select.addEventListener('change', syncDepositFields);
                syncDepositFields();
            });
        })();
    </script>
@endpush
