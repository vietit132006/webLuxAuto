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
        @if ($errors->any())
            <div style="background:red;color:white;padding:10px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data" class="container mt-4">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Thêm Xe Cũ Vào Kho</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- PHẦN 1: CHỌN MẪU XE (CAR MODEL) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Hãng & Mẫu Xe <span
                                    class="text-danger">*</span></label>
                            <select name="car_model_id" id="car_model_id"
                                class="form-select @error('car_model_id') is-invalid @enderror" required>
                                <option value="">-- Chọn Mẫu Xe Có Sẵn --</option>
                                @foreach ($carModels as $model)
                                    <option value="{{ $model->id }}">
                                        {{ $model->brand->name }} - {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('car_model_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Chưa có mẫu xe? <a href="#" data-bs-toggle="modal"
                                    data-bs-target="#addModelModal">Thêm mẫu mới ngay</a></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Tên Phiên Bản Cụ Thể</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="Ví dụ: Carrera S, Premium, G..." value="{{ old('name') }}">
                            <small class="text-muted">Để phân biệt các biến thể cùng một dòng xe.</small>
                        </div>

                        <!-- PHẦN 2: THÔNG TIN ĐỊNH DANH (VIN & BIỂN SỐ) -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Số VIN (Số khung) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="vin" class="form-control @error('vin') is-invalid @enderror"
                                required value="{{ old('vin') }}" placeholder="Nhập 17 ký tự số khung">
                            @error('vin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Biển Số Xe</label>
                            <input type="text" name="license_plate" class="form-control"
                                value="{{ old('license_plate') }}" placeholder="Ví dụ: 30A-123.45">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Năm Sản Xuất <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" required
                                value="{{ old('year', date('Y')) }}">
                        </div>

                        <!-- PHẦN 3: TÌNH TRẠNG THỰC TẾ -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Số Odo (km) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="mileage_km" class="form-control" required
                                value="{{ old('mileage_km') }}" placeholder="Ví dụ: 15000">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Giá Bán (VNĐ) <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required value="{{ old('price') }}"
                                placeholder="Ví dụ: 1200000000">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Số Đời Chủ</label>
                            <input type="number" name="owner_count" class="form-control"
                                value="{{ old('owner_count', 1) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Màu Ngoại Thất</label>
                            <input type="text" name="color" class="form-control" placeholder="Trắng, Đen, Đỏ...">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Màu Nội Thất</label>
                            <input type="text" name="interior_color" class="form-control"
                                placeholder="Kem, Nâu, Đen...">
                        </div>

                        <!-- PHẦN 4: HÌNH ẢNH & VIDEO -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Ảnh Đại Diện (Bìa) <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control" required accept="image/*">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Album Ảnh Chi Tiết (Gallery)</label>
                            <input type="file" name="gallery[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Có thể chọn nhiều ảnh cùng lúc.</small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label font-weight-bold">Mô Tả Tình Trạng Xe</label>
                            <textarea name="description" class="form-control" rows="4"
                                placeholder="Mô tả kỹ về tình trạng xe, lịch sử bảo dưỡng..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="reset" class="btn btn-secondary">Nhập Lại</button>
                    <button type="submit" class="btn btn-success px-5">Đăng Bán Xe</button>
                </div>
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
