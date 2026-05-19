@extends('layouts.admin')

@section('title', 'Thêm xe mới')

@section('content')
    <style>
        /* --- FORM CONTAINER & CARDS --- */
        .lux-form-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .lux-page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2rem;
        }

        .lux-page-title {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
        }

        .lux-card {
            background: linear-gradient(145deg, var(--surface), #0f141a);
            border: 1px solid var(--border);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.8rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }

        .lux-card-title {
            margin-top: 0;
            color: var(--accent);
            margin-bottom: 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
            padding-bottom: 0.8rem;
        }

        .lux-card-title svg {
            width: 20px;
            height: 20px;
        }

        /* --- FORM GROUPS & INPUTS --- */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text);
            font-size: 0.9rem;
        }

        .form-group label span.required {
            color: #ef4444;
        }

        .lux-input,
        .lux-select,
        .lux-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.2);
            color: var(--text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
            font-family: inherit;
        }

        .lux-input:focus,
        .lux-select:focus,
        .lux-textarea:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Highlight cho ô giá tiền */
        .price-input-wrapper {
            position: relative;
        }

        .price-input-wrapper::after {
            content: 'VNĐ';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-weight: 600;
            pointer-events: none;
        }

        .lux-input.price-input {
            padding-right: 3.5rem;
            font-weight: bold;
            color: var(--accent);
            font-size: 1.1rem;
        }

        .price-preview {
            margin-top: 6px;
            font-size: 0.85rem;
            color: #10b981;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* --- IMAGE UPLOAD PREVIEW --- */
        .img-upload-zone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            background: rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .img-upload-zone:hover {
            border-color: var(--accent);
            background: rgba(201, 169, 98, 0.05);
        }

        .img-preview-box {
            width: 100%;
            max-width: 300px;
            height: 180px;
            border-radius: 8px;
            object-fit: cover;
            margin: 0 auto 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            display: none;
        }

        .img-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 180px;
            color: var(--muted);
            gap: 10px;
        }

        .img-placeholder svg {
            width: 48px;
            height: 48px;
            opacity: 0.5;
        }

        /* CSS Mới cho Multi-image (Gallery) */
        .gallery-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .gallery-item {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* --- FEATURED CHECKBOX --- */
        .featured-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 1.5rem;
            background: rgba(201, 169, 98, 0.08);
            border: 1px solid rgba(201, 169, 98, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .featured-toggle:hover {
            background: rgba(201, 169, 98, 0.15);
        }

        .featured-toggle input {
            width: 20px;
            height: 20px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .featured-toggle span {
            font-weight: 700;
            color: var(--accent);
        }

        /* --- BUTTONS --- */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--accent), #e4d08a);
            color: #000;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            border: none;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(201, 169, 98, 0.5);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        .btn-back {
            padding: 0.8rem 2rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .field-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .field-hint {
            color: var(--muted);
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .alert-errors {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fecaca;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-errors ul {
            margin: 0;
            padding-left: 1.2rem;
        }
    </style>

    <div class="wrap lux-form-wrap">
        <div class="lux-page-header">
            <a href="{{ route('admin.cars.index') }}" style="color: var(--muted); transition: 0.2s;"
                onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                <svg style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
            </a>
            <h1 class="lux-page-title">Thêm xe mới</h1>
        </div>
        @if (session('success'))
            <div
                style="background:#10b981;color:#062d21;padding:10px;border-radius:10px;margin-bottom:12px;font-weight:700;">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div style="background:#ef4444;color:#fff;padding:10px;border-radius:10px;margin-bottom:12px;font-weight:700;">
                {{ session('error') }}
            </div>
        @endif
        @if (session()->has('ui_log'))
            <div class="lux-card" style="padding: 1rem;">
                <h3 class="lux-card-title" style="margin-bottom: 0.75rem;">UI Debug Log</h3>
                <details>
                    <summary style="cursor:pointer;font-weight:700;color:var(--accent);">Mở log lần submit gần nhất
                    </summary>
                    <pre
                        style="margin-top:10px;white-space:pre-wrap;word-break:break-word;background:rgba(0,0,0,0.25);padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);">{{ json_encode(session('ui_log'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
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
                </div>

                <div class="form-row">
                    <div class="form-group price-input-wrapper">
                        <label>Giá (VNĐ) <span class="required">*</span></label>
                        <input type="number" name="price" class="lux-input price-input" value="{{ old('price') }}"
                            min="0" step="1000" required>
                        <p class="field-hint">Giá không được âm.</p>
                        @error('price')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

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
            price: 'Vui lòng nhập giá bán.',
            year: 'Vui lòng nhập năm sản xuất.',
            mileage_km: 'Vui lòng nhập số km đã đi.',
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

                form.addEventListener('submit', function(e) {
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
