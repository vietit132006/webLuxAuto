@extends('layouts.admin')

@section('title', 'Thêm xe mới')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-create.css')
    @endif
@endpush


@section('content')

    <div class="wrap lux-form-wrap">
        <div class="lux-page-header">
            <a class="admin-cars-create-inline-8" href="{{ route('admin.cars.index') }}"
                onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                <svg class="admin-cars-create-inline-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
            </a>
            <h1 class="lux-page-title">Thêm xe mới</h1>
        </div>
        @if (session('success'))
            <div class="admin-cars-create-inline-6">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="admin-cars-create-inline-5">
                {{ session('error') }}
            </div>
        @endif
        @if (session()->has('ui_log'))
            <div class="lux-card admin-cars-create-inline-4">
                <h3 class="lux-card-title admin-cars-create-inline-3">UI Debug Log</h3>
                <details>
                    <summary class="admin-cars-create-inline-2">Mở log lần submit gần nhất
                    </summary>
                    <pre class="admin-cars-create-inline-1">{{ json_encode(session('ui_log'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert-errors">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form id="car-create-form" action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- THÔNG TIN CƠ BẢN --}}
            <div class="lux-card">
                <h3 class="lux-card-title"> Thông tin cơ bản</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Dòng xe (Model) <span class="required">*</span></label>
                        <select name="car_model_id" class="lux-select" required>
                            <option value="">-- Chọn dòng xe --</option>
                            @foreach ($carModels as $model)
                                {{-- Giả sử bạn truyền $carModels từ Controller --}}
                                <option value="{{ $model->id }}"
                                    {{ (string) old('car_model_id') === (string) $model->id ? 'selected' : '' }}>
                                    {{ $model->brand?->name ? $model->brand->name . ' - ' : '' }}{{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('car_model_id')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Tên hiển thị <span class="required">*</span></label>
                        <input type="text" name="name" class="lux-input" placeholder="Ví dụ: VinFast Lux A2.0 Plus"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số VIN (Số khung) <span class="required">*</span></label>
                        <input type="text" name="vin" class="lux-input" value="{{ old('vin') }}" maxlength="17"
                            required>
                        @error('vin')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Biển số xe</label>
                        <input type="text" name="license_plate" class="lux-input" placeholder="Nếu có"
                            value="{{ old('license_plate') }}" maxlength="20">
                        @error('license_plate')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Mã xe nội bộ</label>
                        <input type="text" name="internal_code" class="lux-input" placeholder="VD: LUX-2026-001"
                            value="{{ old('internal_code') }}" maxlength="50">
                        @error('internal_code')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="price" value="{{ old('price') }}">

                <div class="form-row">
                    <div class="form-group">
                        <label>Năm sản xuất <span class="required">*</span></label>
                        <input type="number" name="year" class="lux-input" value="{{ old('year') }}" min="1000"
                            max="{{ date('Y') }}" required>
                        <p class="field-hint">Từ năm 1000 đến {{ date('Y') }}.</p>
                        @error('year')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- GIÁ BÁN & CHI PHÍ LĂN BÁNH --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Giá bán & chi phí lăn bánh</h3>

                <div class="pricing-grid">
                    <div class="form-group price-input-wrapper">
                        <label>Giá niêm yết <span class="required">*</span></label>
                        <input type="number" name="list_price" class="lux-input price-input"
                            value="{{ old('list_price', old('price')) }}" min="0" step="1000" required>
                        @error('list_price')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Giá khuyến mãi</label>
                        <input type="number" name="sale_price" class="lux-input price-input"
                            value="{{ old('sale_price') }}" min="0" step="1000">
                        <p class="field-hint pricing-warning" data-sale-warning>Giá khuyến mãi không được lớn hơn giá niêm yết.</p>
                        @error('sale_price')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Phí trước bạ</label>
                        <input type="number" name="registration_fee" class="lux-input price-input"
                            value="{{ old('registration_fee') }}" min="0" step="1000">
                        @error('registration_fee')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Phí biển số</label>
                        <input type="number" name="license_plate_fee" class="lux-input price-input"
                            value="{{ old('license_plate_fee') }}" min="0" step="1000">
                        @error('license_plate_fee')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Phí đăng kiểm</label>
                        <input type="number" name="inspection_fee" class="lux-input price-input"
                            value="{{ old('inspection_fee') }}" min="0" step="1000">
                        @error('inspection_fee')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Phí bảo hiểm</label>
                        <input type="number" name="insurance_fee" class="lux-input price-input"
                            value="{{ old('insurance_fee') }}" min="0" step="1000">
                        @error('insurance_fee')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Phí dịch vụ khác</label>
                        <input type="number" name="other_fees" class="lux-input price-input"
                            value="{{ old('other_fees') }}" min="0" step="1000">
                        @error('other_fees')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Khu vực đăng ký</label>
                        <input type="text" name="registration_area" class="lux-input"
                            placeholder="VD: TP.HCM, Hà Nội, Đà Nẵng" value="{{ old('registration_area') }}"
                            maxlength="100">
                        @error('registration_area')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group price-input-wrapper">
                        <label>Giá lăn bánh dự kiến</label>
                        <input type="number" name="estimated_rolling_price"
                            class="lux-input price-input rolling-total-input"
                            value="{{ old('estimated_rolling_price') }}" min="0" step="1000" readonly>
                        @error('estimated_rolling_price')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- THÔNG SỐ & NGOẠI THẤT --}}
            <div class="lux-card">
                <h3 class="lux-card-title"> Màu sắc & Đặc điểm</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Màu ngoại thất</label>
                        <input type="text" name="color" class="lux-input" placeholder="Ví dụ: Đen"
                            value="{{ old('color') }}">
                    </div>
                    <div class="form-group">
                        <label>Màu nội thất</label>
                        <input type="text" name="interior_color" class="lux-input" placeholder="Ví dụ: Kem"
                            value="{{ old('interior_color') }}">
                    </div>
                    <div class="form-group">
                        <label>Số đời chủ</label>
                        <input type="number" name="owner_count" id="owner_count" class="lux-input"
                            value="{{ old('owner_count', '1') }}" min="0" max="10">
                        <p class="field-hint" id="owner-count-hint">Xe 0 km: 0–10 đời chủ. Xe đã đi: 1–10 đời chủ.</p>
                        @error('owner_count')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- TÌNH TRẠNG --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Tình trạng xe</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tình trạng xe <span class="required">*</span></label>
                        <select name="vehicle_condition" class="lux-select" required>
                            <option value="new" {{ old('vehicle_condition', 'new') === 'new' ? 'selected' : '' }}>Mới</option>
                            <option value="used" {{ old('vehicle_condition') === 'used' ? 'selected' : '' }}>Cũ</option>
                            <option value="display" {{ old('vehicle_condition') === 'display' ? 'selected' : '' }}>Trưng bày</option>
                            <option value="test_drive" {{ old('vehicle_condition') === 'test_drive' ? 'selected' : '' }}>Lái thử</option>
                        </select>
                        @error('vehicle_condition')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số km đã đi <span class="required">*</span></label>
                        <input type="number" name="mileage_km" id="mileage_km" class="lux-input"
                            value="{{ old('mileage_km') }}" min="0" required>
                        <p class="field-hint">Số km không được âm (xe mới nhập 0).</p>
                        @error('mileage_km')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Trạng thái bán hàng</label>
                        <select name="status" class="lux-select">
                            <option value="1" {{ (string) old('status', '1') === '1' ? 'selected' : '' }}>1: Sẵn sàng
                            </option>
                            <option value="2" {{ (string) old('status') === '2' ? 'selected' : '' }}>2: Cọc</option>
                            <option value="3" {{ (string) old('status') === '3' ? 'selected' : '' }}>3: Đã bán
                            </option>
                        </select>
                        @error('status')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Độ nổi bật</label>
                        <select name="is_featured" class="lux-select">
                            <option value="0" {{ (string) old('is_featured', '0') === '0' ? 'selected' : '' }}>Bình
                                thường</option>
                            <option value="1" {{ (string) old('is_featured', '0') === '1' ? 'selected' : '' }}>Nổi
                                bật (Hiện trang chủ)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày nhập kho</label>
                        <input type="date" name="stock_in_date" class="lux-input" value="{{ old('stock_in_date') }}">
                        @error('stock_in_date')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Ngày lăn bánh</label>
                        <input type="date" name="on_road_date" class="lux-input" value="{{ old('on_road_date') }}">
                        @error('on_road_date')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Vị trí xe</label>
                        <input type="text" name="current_location" class="lux-input"
                            placeholder="VD: Kho Q7 / Showroom Nguyễn Văn Linh"
                            value="{{ old('current_location') }}" maxlength="255">
                        @error('current_location')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số lượng tồn</label>
                        <input type="number" name="stock_quantity" class="lux-input"
                            value="{{ old('stock_quantity', 1) }}" min="0" step="1">
                        <p class="field-hint">Số lượng ban đầu sẽ được ghi vào lịch sử nhập kho.</p>
                        @error('stock_quantity')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- HÌNH ẢNH --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Hình ảnh & Video</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ảnh đại diện (Chính) <span class="required">*</span></label>
                        <input type="file" name="image" class="lux-input" required
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewNewImage(event)">
                        @error('image')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Album ảnh (Gallery)</label>
                        <input type="file" name="gallery[]" id="gallery-input" multiple class="lux-input"
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewGallery(event)">
                        <p class="field-hint">Tối đa 10 ảnh, mỗi ảnh không quá 5MB.</p>
                        @error('gallery')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                        @error('gallery.*')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Video upload</label>
                        {{-- Thêm accept="video/*" để chỉ hiển thị các định dạng video --}}
                        <input type="file" name="video_file" id="video_file" class="lux-input"
                            accept="video/mp4,video/x-m4v,video/*">
                        <p class="field-hint">Dung lượng tối đa 20MB (mp4, mov, m4v, avi).</p>
                        <div id="video-file-error" class="field-error" style="display:none;"></div>
                        @error('video_file')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Youtube URL</label>
                        <input type="text" name="video_url" class="lux-input" placeholder="https://youtube.com/..."
                            value="{{ old('video_url') }}">
                        @error('video_url')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div id="gallery-preview-container" class="gallery-preview-container"></div>
            </div>

            {{-- MÔ TẢ --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Mô tả chi tiết</h3>
                <div class="form-group">
                    <textarea name="description" class="lux-textarea" rows="4"
                        placeholder="Nhập mô tả về tình trạng xe, option thêm...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- BUTTON --}}
            <div class="form-actions">
                <a href="{{ route('admin.cars.index') }}" class="btn-back">← Quay lại</a>
                <button type="submit" class="btn-submit">💾 Lưu xe vào hệ thống</button>
            </div>
        </form>
    </div>

    <script>
        const MAX_GALLERY = 10;
        const MAX_VIDEO_BYTES = 20 * 1024 * 1024; // 20MB

        const REQUIRED_FIELD_MESSAGES = {
            car_model_id: 'Vui lòng chọn dòng xe (Model).',
            name: 'Vui lòng nhập tên hiển thị.',
            vin: 'Vui lòng nhập số VIN (số khung).',
            list_price: 'Vui lòng nhập giá niêm yết.',
            year: 'Vui lòng nhập năm sản xuất.',
            mileage_km: 'Vui lòng nhập số km đã đi.',
            vehicle_condition: 'Vui lòng chọn tình trạng xe.',
            image: 'Vui lòng chọn ảnh đại diện cho xe.'
        };

        function setupVietnameseRequiredMessages(form) {
            if (!form) return;

            Object.entries(REQUIRED_FIELD_MESSAGES).forEach(([name, message]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (!field) return;

                field.addEventListener('invalid', function() {
                    if (field.validity.valueMissing) {
                        field.setCustomValidity(message);
                    } else {
                        field.setCustomValidity('');
                    }
                });

                field.addEventListener('input', function() {
                    field.setCustomValidity('');
                });

                field.addEventListener('change', function() {
                    field.setCustomValidity('');
                });
            });
        }

        function updateOwnerCountHint() {
            const mileage = parseInt(document.getElementById('mileage_km')?.value || '0', 10);
            const hint = document.getElementById('owner-count-hint');
            const ownerInput = document.getElementById('owner_count');
            if (!hint || !ownerInput) return;

            if (mileage === 0) {
                hint.textContent = 'Xe mới (0 km): số đời chủ từ 0 đến 10.';
                ownerInput.min = 0;
            } else {
                hint.textContent = 'Xe đã qua sử dụng: số đời chủ từ 1 đến 10.';
                ownerInput.min = 1;
                if (ownerInput.value !== '' && parseInt(ownerInput.value, 10) < 1) {
                    ownerInput.value = 1;
                }
            }
        }

        function previewNewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            // Preview optional — không bắt buộc phần tử DOM
        }

        function previewGallery(event) {
            const container = document.getElementById('gallery-preview-container');
            const input = event.target;
            container.innerHTML = '';

            const files = input.files;
            if (!files || !files.length) return;

            if (files.length > MAX_GALLERY) {
                alert('Album ảnh chỉ được tối đa ' + MAX_GALLERY + ' ảnh.');
                input.value = '';
                return;
            }

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'gallery-item';
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        function validateVideoFile(input) {
            const errEl = document.getElementById('video-file-error');
            if (!errEl) return true;

            errEl.style.display = 'none';
            errEl.textContent = '';

            const file = input.files[0];
            if (!file) return true;

            if (file.size > MAX_VIDEO_BYTES) {
                errEl.textContent = 'Video vượt quá dung lượng cho phép (tối đa 20MB).';
                errEl.style.display = 'block';
                input.value = '';
                return false;
            }
            return true;
        }

        function moneyNumber(form, name) {
            const field = form?.querySelector(`[name="${name}"]`);
            if (!field || field.value === '') return 0;

            const value = Number(field.value);
            return Number.isFinite(value) && value >= 0 ? value : 0;
        }

        function optionalMoneyNumber(form, name) {
            const field = form?.querySelector(`[name="${name}"]`);
            if (!field || field.value === '') return null;

            const value = Number(field.value);
            return Number.isFinite(value) && value >= 0 ? value : 0;
        }

        function calculateRollingPrice(form) {
            if (!form) return;

            const listPrice = moneyNumber(form, 'list_price');
            const salePrice = optionalMoneyNumber(form, 'sale_price');
            const actualPrice = salePrice !== null ? salePrice : listPrice;
            const total = actualPrice +
                moneyNumber(form, 'registration_fee') +
                moneyNumber(form, 'license_plate_fee') +
                moneyNumber(form, 'inspection_fee') +
                moneyNumber(form, 'insurance_fee') +
                moneyNumber(form, 'other_fees');

            const priceInput = form.querySelector('[name="price"]');
            const rollingInput = form.querySelector('[name="estimated_rolling_price"]');
            const saleInput = form.querySelector('[name="sale_price"]');
            const saleWarning = form.querySelector('[data-sale-warning]');

            if (priceInput) priceInput.value = Math.round(actualPrice);
            if (rollingInput) rollingInput.value = Math.round(total);

            const saleTooHigh = salePrice !== null && salePrice > listPrice;
            if (saleWarning) saleWarning.style.display = saleTooHigh ? 'block' : 'none';
            if (saleInput) {
                saleInput.setCustomValidity(saleTooHigh ? 'Giá khuyến mãi không được lớn hơn giá niêm yết.' : '');
            }
        }

        function setupPricingCalculator(form) {
            if (!form) return;

            [
                'list_price',
                'sale_price',
                'registration_fee',
                'license_plate_fee',
                'inspection_fee',
                'insurance_fee',
                'other_fees'
            ].forEach(name => {
                form.querySelector(`[name="${name}"]`)?.addEventListener('input', () => calculateRollingPrice(form));
            });

            calculateRollingPrice(form);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const mileageInput = document.getElementById('mileage_km');
            const videoInput = document.getElementById('video_file');
            const form = document.getElementById('car-create-form');

            if (mileageInput) {
                mileageInput.addEventListener('input', updateOwnerCountHint);
                updateOwnerCountHint();
            }

            if (videoInput) {
                videoInput.addEventListener('change', function() {
                    validateVideoFile(videoInput);
                });
            }

            if (form) {
                setupVietnameseRequiredMessages(form);
                setupPricingCalculator(form);

                form.addEventListener('submit', function(e) {
                    calculateRollingPrice(form);

                    const saleInput = form.querySelector('[name="sale_price"]');
                    if (saleInput && !saleInput.checkValidity()) {
                        e.preventDefault();
                        saleInput.reportValidity();
                        return;
                    }

                    const galleryInput = document.getElementById('gallery-input');
                    if (galleryInput?.files?.length > MAX_GALLERY) {
                        e.preventDefault();
                        alert('Album ảnh chỉ được tối đa ' + MAX_GALLERY + ' ảnh.');
                        return;
                    }
                    if (videoInput && !validateVideoFile(videoInput)) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
@endsection
