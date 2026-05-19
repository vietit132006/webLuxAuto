@extends('layouts.admin')

@section('title', 'Cập nhật xe - ' . $car->name)

@section('content')
    <style>
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

        .gallery-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            justify-content: flex-start;
        }

        .gallery-item {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .existing-media-label {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }

        .current-main-preview {
            width: 100%;
            max-width: 280px;
            height: 160px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
        }

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
    </style>

    <div class="wrap lux-form-wrap">
        <div class="lux-page-header">
            <a href="{{ route('admin.cars.show', $car->car_id) }}" style="color: var(--muted); transition: 0.2s;"
                onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                <svg style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
            </a>
            <h1 class="lux-page-title">Chỉnh sửa: <span style="color: var(--accent);">{{ $car->name }}</span></h1>
        </div>

        @if (session('success'))
            <div style="background:#10b981;color:#062d21;padding:10px;border-radius:10px;margin-bottom:12px;font-weight:700;">
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
                    <summary style="cursor:pointer;font-weight:700;color:var(--accent);">Mở log lần submit gần nhất</summary>
                    <pre style="margin-top:10px;white-space:pre-wrap;word-break:break-word;background:rgba(0,0,0,0.25);padding:12px;border-radius:10px;border:1px solid var(--border);color:var(--text);">{{ json_encode(session('ui_log'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        @endif
        @if ($errors->any())
            <div style="background:red;color:white;padding:10px;border-radius:10px;margin-bottom:12px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.cars.update', $car->car_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- THÔNG TIN CƠ BẢN --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Thông tin cơ bản</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Dòng xe (Model) <span class="required">*</span></label>
                        <select name="car_model_id" class="lux-select" required>
                            <option value="">-- Chọn dòng xe --</option>
                            @foreach ($carModels as $model)
                                <option value="{{ $model->id }}"
                                    {{ (string) old('car_model_id', $car->car_model_id) === (string) $model->id ? 'selected' : '' }}>
                                    {{ $model->brand?->name ? $model->brand->name . ' - ' : '' }}{{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tên hiển thị <span class="required">*</span></label>
                        <input type="text" name="name" class="lux-input" placeholder="Ví dụ: VinFast Lux A2.0 Plus"
                            value="{{ old('name', $car->name) }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số VIN (Số khung) <span class="required">*</span></label>
                        <input type="text" name="vin" class="lux-input" value="{{ old('vin', $car->vin) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Biển số xe</label>
                        <input type="text" name="license_plate" class="lux-input" placeholder="Nếu có"
                            value="{{ old('license_plate', $car->license_plate) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group price-input-wrapper">
                        <label>Giá (VNĐ) <span class="required">*</span></label>
                        <input type="number" name="price" class="lux-input price-input"
                            value="{{ old('price', $car->price) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Năm sản xuất <span class="required">*</span></label>
                        <input type="number" name="year" class="lux-input" value="{{ old('year', $car->year) }}"
                            required>
                    </div>
                </div>
            </div>

            {{-- MÀU SẮC & ĐẶC ĐIỂM --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Màu sắc & Đặc điểm</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Màu ngoại thất</label>
                        <input type="text" name="color" class="lux-input" placeholder="Ví dụ: Đen"
                            value="{{ old('color', $car->color) }}">
                    </div>
                    <div class="form-group">
                        <label>Màu nội thất</label>
                        <input type="text" name="interior_color" class="lux-input" placeholder="Ví dụ: Kem"
                            value="{{ old('interior_color', $car->interior_color) }}">
                    </div>
                    <div class="form-group">
                        <label>Số đời chủ</label>
                        <input type="number" name="owner_count" class="lux-input"
                            value="{{ old('owner_count', $car->owner_count ?? 1) }}">
                    </div>
                </div>
            </div>

            {{-- TÌNH TRẠNG --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Tình trạng xe</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số km đã đi <span class="required">*</span></label>
                        <input type="number" name="mileage_km" class="lux-input"
                            value="{{ old('mileage_km', $car->mileage_km) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Trạng thái bán hàng</label>
                        <select name="status" class="lux-select">
                            <option value="1" {{ (string) old('status', $car->status) === '1' ? 'selected' : '' }}>1: Sẵn sàng</option>
                            <option value="2" {{ (string) old('status', $car->status) === '2' ? 'selected' : '' }}>2: Cọc</option>
                            <option value="3" {{ (string) old('status', $car->status) === '3' ? 'selected' : '' }}>3: Đã bán</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Độ nổi bật</label>
                        <select name="is_featured" class="lux-select">
                            <option value="0" {{ !old('is_featured', $car->is_featured) ? 'selected' : '' }}>Bình thường</option>
                            <option value="1" {{ old('is_featured', $car->is_featured) ? 'selected' : '' }}>Nổi bật (Hiện trang chủ)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- HÌNH ẢNH --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Hình ảnh & Video</h3>

                @if ($car->image)
                    <p class="existing-media-label">Ảnh đại diện hiện tại:</p>
                    <img src="{{ asset('storage/' . $car->image) }}" alt="Ảnh hiện tại" class="current-main-preview"
                        id="instant-preview">
                @endif

                <div class="form-row">
                    <div class="form-group">
                        <label>{{ $car->image ? 'Thay ảnh đại diện' : 'Ảnh đại diện (Chính)' }}</label>
                        <input type="file" name="image" class="lux-input"
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewNewImage(event)">
                        @if ($car->image)
                            <p style="font-size:0.8rem;color:var(--muted);margin-top:6px;">Bỏ trống để giữ ảnh cũ.</p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Thêm ảnh vào Album (Gallery)</label>
                        <input type="file" name="gallery[]" multiple class="lux-input"
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewGallery(event)">
                    </div>
                </div>

                @if ($car->images && $car->images->count())
                    <p class="existing-media-label" style="margin-top:1rem;">Album hiện có ({{ $car->images->count() }} ảnh):</p>
                    <div class="gallery-preview-container">
                        @foreach ($car->images as $img)
                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="Gallery" class="gallery-item">
                        @endforeach
                    </div>
                @endif

                <div class="form-row" style="margin-top:1.5rem;">
                    <div class="form-group">
                        <label>Video upload</label>
                        <input type="file" name="video_file" class="lux-input"
                            accept="video/mp4,video/x-m4v,video/*">
                        @if ($car->video_file)
                            <p style="font-size:0.8rem;color:var(--muted);margin-top:6px;">
                                Video hiện tại: <a href="{{ asset('storage/' . $car->video_file) }}" target="_blank"
                                    style="color:var(--accent);">Xem file</a> — bỏ trống để giữ.
                            </p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Youtube URL</label>
                        <input type="text" name="video_url" class="lux-input" placeholder="https://youtube.com/..."
                            value="{{ old('video_url', $car->video_url) }}">
                    </div>
                </div>

                <div id="gallery-preview-container" class="gallery-preview-container"></div>
            </div>

            {{-- MÔ TẢ --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Mô tả chi tiết</h3>
                <div class="form-group">
                    <textarea name="description" class="lux-textarea" rows="4"
                        placeholder="Nhập mô tả về tình trạng xe, option thêm...">{{ old('description', $car->description) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.cars.show', $car->car_id) }}" class="btn-back">← Quay lại</a>
                <button type="submit" class="btn-submit">💾 Lưu thay đổi</button>
            </div>
        </form>
    </div>

    <script>
        function previewNewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            let preview = document.getElementById('instant-preview');
            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'instant-preview';
                preview.className = 'current-main-preview';
                event.target.closest('.lux-card').insertBefore(preview, event.target.closest('.form-row'));
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function previewGallery(event) {
            const container = document.getElementById('gallery-preview-container');
            container.innerHTML = '';

            const files = event.target.files;
            if (files) {
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
        }
    </script>
@endsection
