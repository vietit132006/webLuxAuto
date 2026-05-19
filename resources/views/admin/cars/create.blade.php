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
            <div style="background:red;color:white;padding:10px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data">
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
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tên hiển thị <span class="required">*</span></label>
                        <input type="text" name="name" class="lux-input" placeholder="Ví dụ: VinFast Lux A2.0 Plus"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số VIN (Số khung) <span class="required">*</span></label>
                        <input type="text" name="vin" class="lux-input" required>
                    </div>
                    <div class="form-group">
                        <label>Biển số xe</label>
                        <input type="text" name="license_plate" class="lux-input" placeholder="Nếu có">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group price-input-wrapper">
                        <label>Giá (VNĐ) <span class="required">*</span></label>
                        <input type="number" name="price" class="lux-input price-input" required>
                    </div>

                    <div class="form-group">
                        <label>Năm sản xuất <span class="required">*</span></label>
                        <input type="number" name="year" class="lux-input" required>
                    </div>
                </div>
            </div>

            {{-- THÔNG SỐ & NGOẠI THẤT --}}
            <div class="lux-card">
                <h3 class="lux-card-title"> Màu sắc & Đặc điểm</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Màu ngoại thất</label>
                        <input type="text" name="color" class="lux-input" placeholder="Ví dụ: Đen">
                    </div>
                    <div class="form-group">
                        <label>Màu nội thất</label>
                        <input type="text" name="interior_color" class="lux-input" placeholder="Ví dụ: Kem">
                    </div>
                    <div class="form-group">
                        <label>Số đời chủ</label>
                        <input type="number" name="owner_count" class="lux-input" value="1">
                    </div>
                </div>
            </div>

            {{-- TÌNH TRẠNG --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Tình trạng xe</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Số km đã đi <span class="required">*</span></label>
                        <input type="number" name="mileage_km" class="lux-input" required>
                    </div>

                    <div class="form-group">
                        <label>Trạng thái bán hàng</label>
                        <select name="status" class="lux-select">
                            <option value="1">1: Sẵn sàng</option>
                            <option value="2">2: Cọc</option>
                            <option value="3">3: Đã bán</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Độ nổi bật</label>
                        <select name="is_featured" class="lux-select">
                            <option value="0">Bình thường</option>
                            <option value="1">Nổi bật (Hiện trang chủ)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- HÌNH ẢNH --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Hình ảnh & Video</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ảnh đại diện (Chính)</label>
                        <input type="file" name="image" class="lux-input"
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewNewImage(event)">
                    </div>

                    <div class="form-group">
                        <label>Album ảnh (Gallery)</label>
                        <input type="file" name="gallery[]" multiple class="lux-input"
                            accept="image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif"
                            onchange="previewGallery(event)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Video upload</label>
                        {{-- Thêm accept="video/*" để chỉ hiển thị các định dạng video --}}
                        <input type="file" name="video_file" class="lux-input"
                            accept="video/mp4,video/x-m4v,video/*">
                    </div>

                    <div class="form-group">
                        <label>Youtube URL</label>
                        <input type="text" name="video_url" class="lux-input" placeholder="https://youtube.com/...">
                    </div>
                </div>

                <div id="gallery-preview-container" class="gallery-preview-container"></div>
            </div>

            {{-- MÔ TẢ --}}
            <div class="lux-card">
                <h3 class="lux-card-title">Mô tả chi tiết</h3>
                <div class="form-group">
                    <textarea name="description" class="lux-textarea" rows="4"
                        placeholder="Nhập mô tả về tình trạng xe, option thêm..."></textarea>
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
        // 1. Format Giá tiền trực tiếp khi gõ
        function formatPriceRealtime() {
            const input = document.getElementById('price');
            const display = document.getElementById('price-text');

            if (input.value) {
                const formatted = parseInt(input.value).toLocaleString('vi-VN');
                display.innerText = formatted + ' VNĐ';
            } else {
                display.innerText = '0 VNĐ';
            }
        }

        // 2. Load ảnh đại diện
        function previewNewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('instant-preview');
            const placeholder = document.getElementById('img-placeholder');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
            }
        }

        // 3. Load nhiều ảnh (Gallery)
        function previewGallery(event) {
            const container = document.getElementById('gallery-preview-container');
            container.innerHTML = ''; // Xóa ảnh cũ nếu chọn lại

            const files = event.target.files;

            if (files) {
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'gallery-item';
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            formatPriceRealtime();
        });
    </script>
@endsection
