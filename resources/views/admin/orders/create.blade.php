@extends('layouts.admin')
@section('title', 'Tạo đơn hàng mới')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-create.css')
    @endif
@endpush

@section('content')
<div class="wrap">
    <div class="form-wrap">
        <div class="form-header">
            <a href="{{ route('admin.orders.index') }}" class="back-link">Quay lại danh sách</a>
            <h1 class="form-title">Tạo đơn hàng mới</h1>
        </div>

        @if($errors->any())
            <div class="error-box">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.orders.store') }}" method="POST">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="label" for="user_id">Khách hàng</label>
                    <select id="user_id" name="user_id" class="select" required>
                        <option value="">Chọn khách hàng</option>
                        @foreach($users as $user)
                            <option value="{{ $user->user_id }}" @selected((string) old('user_id') === (string) $user->user_id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="car_id">Xe bán</label>
                    <select id="car_id" name="car_id" class="select" required>
                        <option value="">Chọn xe</option>
                        @foreach($cars as $car)
                            @php
                                $stock = $car->stock_quantity ?? $car->stock ?? 0;
                            @endphp
                            <option value="{{ $car->car_id }}" @selected((string) old('car_id') === (string) $car->car_id)>
                                {{ $car->name }} - {{ number_format((float) $car->price, 0, ',', '.') }} đ - Kho: {{ $stock }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="deposit_amount">Tiền cọc</label>
                    <input id="deposit_amount" type="number" name="deposit_amount" class="input" min="0" step="1000" value="{{ old('deposit_amount', (int) $defaultDepositAmount) }}">
                </div>

                <div class="form-group">
                    <label class="label" for="deposit_date">Ngày cọc</label>
                    <input id="deposit_date" type="datetime-local" name="deposit_date" class="input" value="{{ old('deposit_date') }}">
                </div>

                <div class="form-group">
                    <label class="label" for="deposit_method">Phương thức cọc</label>
                    <select id="deposit_method" name="deposit_method" class="select">
                        <option value="">Chọn phương thức</option>
                        @foreach($depositMethodOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('deposit_method') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="deposit_reference">Mã giao dịch</label>
                    <input id="deposit_reference" type="text" name="deposit_reference" class="input" value="{{ old('deposit_reference') }}" maxlength="255">
                </div>

                <div class="form-group">
                    <label class="label" for="status">Trạng thái ban đầu</label>
                    <select id="status" name="status" class="select" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('status', 0) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="label" for="note">Ghi chú lịch sử</label>
                    <textarea id="note" name="note" class="input" rows="3" placeholder="Ghi chú khi tạo đơn">{{ old('note') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="label" for="deposit_note">Ghi chú đặt cọc</label>
                    <textarea id="deposit_note" name="deposit_note" class="input" rows="3">{{ old('deposit_note') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn-submit">Tạo đơn hàng</button>
        </form>
    </div>
</div>
@endsection
