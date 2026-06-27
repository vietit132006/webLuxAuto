@extends('layouts.admin')

@section('title', 'Cập nhật model xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-car-models-edit.css')
    @endif
@endpush


@section('content')
    @include('admin.car_models._style')

    <div class="wrap model-wrap">
        <div class="model-header">
            <div>
                <h1 class="model-title">Cập nhật model xe</h1>
                <p class="model-subtitle">Chỉnh sửa thông số model. Nếu model đã có xe sử dụng, hệ thống vẫn cho sửa nhưng không cho xóa.</p>
            </div>

            <a href="{{ route('admin.car-models.index') }}" class="lux-btn-muted">← Quay lại</a>
        </div>

        @if (session('error'))
            <div class="flash-alert flash-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash-alert flash-error">
                <div class="admin-car-models-edit-inline-4">Vui lòng kiểm tra lại thông tin:</div>
                <ul class="admin-car-models-edit-inline-3">
                    @foreach ($errors->all() as $error)
                        <li class="admin-car-models-edit-inline-2">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.car-models.update', $carModel->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="model-card form-card">
                <h3 class="form-section-title">
                    <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h7.5m0 0a1.5 1.5 0 003 0m-3 0a1.5 1.5 0 013 0m-3 0H6.75m13.5-7.5H3.75m16.5 0-2.25-4.5a2.25 2.25 0 00-2.012-1.244H8.012A2.25 2.25 0 006 6.75l-2.25 4.5" />
                    </svg>
                    Thông tin model
                </h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Hãng xe <span class="required">*</span></label>
                        <select name="brand_id" class="lux-select" required>
                            <option value="">-- Chọn hãng xe --</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->brand_id }}"
                                    {{ (string) old('brand_id', $carModel->brand_id) === (string) $brand->brand_id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Tên model <span class="required">*</span></label>
                        <input type="text" name="name" class="lux-input" value="{{ old('name', $carModel->name) }}"
                            placeholder="Ví dụ: VF 8 Plus, Lux A2.0, Camry 2.5Q" required>
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Động cơ</label>
                        <input type="text" name="engine" class="lux-input" value="{{ old('engine', $carModel->engine) }}"
                            placeholder="Ví dụ: 2.0L Turbo, Electric Dual Motor">
                        @error('engine')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nhiên liệu</label>
                        <select name="fuel_type" class="lux-select">
                            <option value="">-- Chọn nhiên liệu --</option>
                            @foreach (['Xăng', 'Dầu', 'Điện', 'Hybrid', 'Plug-in Hybrid'] as $fuel)
                                <option value="{{ $fuel }}" {{ old('fuel_type', $carModel->fuel_type) === $fuel ? 'selected' : '' }}>
                                    {{ $fuel }}
                                </option>
                            @endforeach
                        </select>
                        @error('fuel_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hộp số</label>
                        <select name="transmission" class="lux-select">
                            <option value="">-- Chọn hộp số --</option>
                            @foreach (['Số tự động', 'Số sàn', 'CVT', 'DCT', 'Không hộp số'] as $transmission)
                                <option value="{{ $transmission }}"
                                    {{ old('transmission', $carModel->transmission) === $transmission ? 'selected' : '' }}>
                                    {{ $transmission }}
                                </option>
                            @endforeach
                        </select>
                        @error('transmission')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kiểu dáng</label>
                        <select name="body_type" class="lux-select">
                            <option value="">-- Chọn kiểu dáng --</option>
                            @foreach (['Sedan', 'SUV', 'Crossover', 'Hatchback', 'MPV', 'Pickup', 'Coupe', 'Convertible'] as $bodyType)
                                <option value="{{ $bodyType }}" {{ old('body_type', $carModel->body_type) === $bodyType ? 'selected' : '' }}>
                                    {{ $bodyType }}
                                </option>
                            @endforeach
                        </select>
                        @error('body_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Dẫn động</label>
                        <select name="drive_type" class="lux-select">
                            <option value="">-- Chọn dẫn động --</option>
                            @foreach (['FWD', 'RWD', 'AWD', '4WD'] as $driveType)
                                <option value="{{ $driveType }}" {{ old('drive_type', $carModel->drive_type) === $driveType ? 'selected' : '' }}>
                                    {{ $driveType }}
                                </option>
                            @endforeach
                        </select>
                        @error('drive_type')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Xuất xứ</label>
                        <input type="text" name="origin" class="lux-input" value="{{ old('origin', $carModel->origin) }}"
                            placeholder="Ví dụ: Việt Nam, Đức, Nhật Bản">
                        @error('origin')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số chỗ</label>
                        <input type="number" name="seats" class="lux-input" value="{{ old('seats', $carModel->seats) }}"
                            min="1" max="100" placeholder="Ví dụ: 5">
                        @error('seats')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số cửa</label>
                        <input type="number" name="doors" class="lux-input" value="{{ old('doors', $carModel->doors) }}"
                            min="1" max="20" placeholder="Ví dụ: 4">
                        @error('doors')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if ($carModel->cars_count > 0)
                    <div class="flash-alert flash-success admin-car-models-edit-inline-1">
                        Model này đang được {{ $carModel->cars_count }} xe sử dụng. Bạn có thể sửa thông tin, nhưng không thể xóa model.
                    </div>
                @endif

                <div class="form-actions">
                    <a href="{{ route('admin.car-models.index') }}" class="lux-btn-muted">Hủy</a>
                    <button type="submit" class="lux-btn-primary">Cập nhật model</button>
                </div>
            </div>
        </form>
    </div>
@endsection