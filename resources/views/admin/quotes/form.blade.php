@extends('layouts.admin')

@section('title', $quote->exists ? 'Sửa báo giá' : 'Tạo báo giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-quotes.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $quote->exists;
    $amount = fn (string $field, $default = 0) => old($field, $quote->{$field} !== null ? $quote->{$field} : $default);
    $selectedCustomerId = (string) old('customer_id', $quote->customer_id);
    $selectedCarId = (string) old('car_id', $quote->car_id);
    $selectedUserId = (string) old('user_id', $quote->user_id);
    $backUrl = $isEdit
        ? route('admin.quotes.show', $quote)
        : ($sourceTestDrive ? route('admin.test_drives.show', $sourceTestDrive->ticket_id) : route('admin.quotes.index'));
@endphp

<div class="admin-quotes-page is-form">
    <div class="admin-quotes-head">
        <div>
            <h1>{{ $isEdit ? 'Sửa báo giá' : 'Tạo báo giá' }}</h1>
            <p>Bán hàng / Báo giá</p>
        </div>

        <a class="admin-quotes-secondary" href="{{ $backUrl }}">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="admin-quotes-alert is-error">{{ $errors->first() }}</div>
    @endif

    @if($prefillWarning)
        <div class="admin-quotes-alert is-warning">{{ $prefillWarning }}</div>
    @endif

    @if($sourceTestDrive)
        <section class="quote-source-panel">
            <div>
                <span>Nguồn tạo</span>
                <strong>Từ lịch lái thử {{ $sourceTestDrive->display_code }}</strong>
                <p>
                    {{ $sourceTestDrive->user->name ?? 'Khách vãng lai' }}
                    · {{ $sourceTestDrive->car?->title ?? 'Xe chưa xác định' }}
                </p>
            </div>
            @can('test_drives.view')
                <a class="admin-quotes-secondary" href="{{ route('admin.test_drives.show', $sourceTestDrive->ticket_id) }}">Xem lịch lái thử</a>
            @endcan
        </section>
    @endif

    <form class="quote-form" method="post" action="{{ $isEdit ? route('admin.quotes.update', $quote) : route('admin.quotes.store') }}" data-quote-form data-mode="{{ $isEdit ? 'edit' : 'create' }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="test_drive_id" value="{{ old('test_drive_id', $quote->test_drive_id) }}">

        <div class="quote-form-grid">
            <div class="quote-form-field">
                <label for="customer_id">Khách hàng</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Chọn khách hàng</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->customer_id }}" @selected($selectedCustomerId === (string) $customer->customer_id)>
                            {{ $customer->full_name }} - {{ $customer->phone }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="quote-form-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id" required data-quote-car>
                    <option value="">Chọn xe</option>
                    @foreach($cars as $car)
                        @php
                            $carPrice = (int) ($car->sale_price ?: $car->price);
                        @endphp
                        <option value="{{ $car->car_id }}"
                            data-price="{{ $carPrice }}"
                            data-registration-fee="{{ (int) ($car->registration_fee ?? 0) }}"
                            data-plate-fee="{{ (int) ($car->license_plate_fee ?? 0) }}"
                            data-insurance-fee="{{ (int) ($car->insurance_fee ?? 0) }}"
                            data-other-fee="{{ (int) ($car->other_fees ?? 0) }}"
                            @selected($selectedCarId === (string) $car->car_id)>
                            {{ $car->title }}{{ $car->vin ? ' - VIN ' . $car->vin : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($isEdit)
                <div class="quote-form-field">
                    <label>Mã báo giá</label>
                    <input type="text" value="{{ $quote->quote_code }}" disabled>
                </div>
            @endif

            <div class="quote-form-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $quote->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="quote-form-field">
                <label for="user_id">Nhân viên phụ trách</label>
                <select id="user_id" name="user_id">
                    <option value="">Hệ thống / chưa phân công</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" @selected($selectedUserId === (string) $user->user_id)>
                            {{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="quote-form-field">
                <label for="vehicle_price">Giá xe</label>
                <input id="vehicle_price" name="vehicle_price" type="number" min="0" step="1" value="{{ $amount('vehicle_price') }}" required data-quote-money="vehicle_price">
            </div>

            <div class="quote-form-field">
                <label for="discount_amount">Giảm giá</label>
                <input id="discount_amount" name="discount_amount" type="number" min="0" step="1" value="{{ $amount('discount_amount') }}" data-quote-money="discount_amount">
            </div>

            <div class="quote-form-field">
                <label for="registration_fee">Phí đăng ký</label>
                <input id="registration_fee" name="registration_fee" type="number" min="0" step="1" value="{{ $amount('registration_fee') }}" data-quote-money="registration_fee">
            </div>

            <div class="quote-form-field">
                <label for="plate_fee">Phí biển số</label>
                <input id="plate_fee" name="plate_fee" type="number" min="0" step="1" value="{{ $amount('plate_fee') }}" data-quote-money="plate_fee">
            </div>

            <div class="quote-form-field">
                <label for="insurance_fee">Phí bảo hiểm</label>
                <input id="insurance_fee" name="insurance_fee" type="number" min="0" step="1" value="{{ $amount('insurance_fee') }}" data-quote-money="insurance_fee">
            </div>

            <div class="quote-form-field">
                <label for="other_fee">Phí khác</label>
                <input id="other_fee" name="other_fee" type="number" min="0" step="1" value="{{ $amount('other_fee') }}" data-quote-money="other_fee">
            </div>

            <div class="quote-form-field">
                <label for="expired_at">Ngày hết hạn</label>
                <input id="expired_at" name="expired_at" type="date" value="{{ old('expired_at', $quote->expired_at?->format('Y-m-d')) }}">
            </div>

            <div class="quote-total-preview">
                <span>Tổng báo giá</span>
                <strong data-quote-total>0 đ</strong>
            </div>

            <div class="quote-form-field is-wide">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" rows="4">{{ old('note', $quote->note) }}</textarea>
            </div>
        </div>

        <div class="quote-form-actions">
            <button class="admin-quotes-primary" type="submit">Lưu báo giá</button>
            <a class="admin-quotes-secondary" href="{{ $backUrl }}">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-quote-form]');

            if (!form) {
                return;
            }

            const moneyFields = {};
            form.querySelectorAll('[data-quote-money]').forEach((input) => {
                moneyFields[input.dataset.quoteMoney] = input;
            });

            const totalTarget = form.querySelector('[data-quote-total]');
            const formatter = new Intl.NumberFormat('vi-VN');

            const amount = (field) => {
                const input = moneyFields[field];

                return input ? Number(input.value || 0) : 0;
            };

            const updateTotal = () => {
                const total = Math.max(0,
                    amount('vehicle_price')
                    - amount('discount_amount')
                    + amount('registration_fee')
                    + amount('plate_fee')
                    + amount('insurance_fee')
                    + amount('other_fee')
                );

                if (totalTarget) {
                    totalTarget.textContent = `${formatter.format(total)} đ`;
                }
            };

            Object.values(moneyFields).forEach((input) => {
                input.addEventListener('input', updateTotal);
            });

            const carSelect = form.querySelector('[data-quote-car]');

            const fillFromSelectedCar = (force = false) => {
                if (!carSelect || !carSelect.selectedOptions.length) {
                    return;
                }

                const option = carSelect.selectedOptions[0];

                if (!option.value) {
                    updateTotal();
                    return;
                }

                const mapping = {
                    vehicle_price: 'price',
                    registration_fee: 'registrationFee',
                    plate_fee: 'plateFee',
                    insurance_fee: 'insuranceFee',
                    other_fee: 'otherFee',
                };

                Object.entries(mapping).forEach(([field, dataKey]) => {
                    const input = moneyFields[field];

                    if (!input) {
                        return;
                    }

                    if (force || !input.value || Number(input.value) === 0) {
                        input.value = option.dataset[dataKey] || 0;
                    }
                });

                updateTotal();
            };

            if (carSelect) {
                carSelect.addEventListener('change', () => fillFromSelectedCar(form.dataset.mode === 'create'));

                if (form.dataset.mode === 'create' && carSelect.value) {
                    fillFromSelectedCar(false);
                }
            }

            updateTotal();
        })();
    </script>
@endpush
