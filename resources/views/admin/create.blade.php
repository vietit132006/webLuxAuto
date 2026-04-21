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
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
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
        border-bottom: 1px dashed rgba(255,255,255,0.05);
        padding-bottom: 0.8rem;
    }
    .lux-card-title svg { width: 20px; height: 20px; }

    /* --- FORM GROUPS & INPUTS --- */
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-group { position: relative; }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text);
        font-size: 0.9rem;
    }
    .form-group label span.required { color: #ef4444; }

    .lux-input, .lux-select, .lux-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: rgba(0, 0, 0, 0.2);
        color: var(--text);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        font-family: inherit;
    }
    .lux-input:focus, .lux-select:focus, .lux-textarea:focus {
        outline: none;
        border-color: var(--accent);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15), inset 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Highlight cho ô giá tiền */
    .price-input-wrapper { position: relative; }
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
    .lux-input.price-input { padding-right: 3.5rem; font-weight: bold; color: var(--accent); font-size: 1.1rem; }
    .price-preview { margin-top: 6px; font-size: 0.85rem; color: #10b981; font-weight: 600; display: flex; align-items: center; gap: 4px; }

    /* --- IMAGE UPLOAD PREVIEW --- */
    .img-upload-zone {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        background: rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    .img-upload-zone:hover { border-color: var(--accent); background: rgba(201, 169, 98, 0.05); }
    .img-preview-box {
        width: 100%;
        max-width: 300px;
        height: 180px;
        border-radius: 8px;
        object-fit: cover;
        margin: 0 auto 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        border: 1px solid var(--border);
        display: none; /* Ẩn đi khi chưa có ảnh */
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
    .img-placeholder svg { width: 48px; height: 48px; opacity: 0.5; }

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
    .featured-toggle:hover { background: rgba(201, 169, 98, 0.15); }
    .featured-toggle input { width: 20px; height: 20px; accent-color: var(--accent); cursor: pointer; }
    .featured-toggle span { font-weight: 700; color: var(--accent); }

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
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(201, 169, 98, 0.5); }
    .btn-submit:active { transform: translateY(1px); }

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
    .btn-back:hover { background: rgba(255,255,255,0.05); color: #fff; }
</style>

<div class="wrap lux-form-wrap">
    <div class="lux-page-header">
        <a href="{{ route('admin.cars.index') }}" style="color: var(--muted); transition: 0.2s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
            <svg style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" /></svg>
        </a>
        <h1 class="lux-page-title">Thêm xe mới</h1>
    </div>

    <form method="POST" action="{{ route('admin.cars.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="lux-card">
            <h3 class="lux-card-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Thông tin nhận diện
            </h3>

            <div class="form-row" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label for="brand_id">Hãng sản xuất <span class="required">*</span></label>
                    <select id="brand_id" name="brand_id" class="lux-select" required>
                        <option value="">-- Chọn hãng --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_id }}" {{ old('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="name">Tên dòng xe / Phiên bản <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="lux-input" value="{{ old('name') }}" required placeholder="VD: Porsche 911 GT3 RS" maxlength="150">
                    @error('name') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="lux-card">
            <h3 class="lux-card-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Định giá & Phân phối
            </h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá bán đề xuất <span class="required">*</span></label>
                    <div class="price-input-wrapper">
                        <input type="number" id="price" name="price" class="lux-input price-input"
                               value="{{ old('price') }}" required min="0" max="999000000000" step="1000000"
                               oninput="formatPriceRealtime()">
                    </div>
                    <div class="price-preview" id="price-display">
                        <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="price-text">0 VNĐ</span>
                    </div>
                    @error('price') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="status">Tình trạng xe <span class="required">*</span></label>
                    <select id="status" name="status" class="lux-select" required>
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Xe Mới 100%</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Xe Lướt (Đã qua sử dụng)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock">Số lượng nhập kho <span class="required">*</span></label>
                    <input type="number" id="stock" name="stock" class="lux-input" value="{{ old('stock', 1) }}" required min="0" max="9999">
                    @error('stock') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="lux-card">
            <h3 class="lux-card-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Thông số kỹ thuật
            </h3>

            <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="form-group">
                    <label for="year">Năm sản xuất <span class="required">*</span></label>
                    <input type="number" id="year" name="year" class="lux-input" value="{{ old('year') }}" required min="1900" max="{{ date('Y') + 2 }}">
                </div>

                <div class="form-group">
                    <label for="color">Màu ngoại thất</label>
                    <input type="text" id="color" name="color" class="lux-input" value="{{ old('color') }}" placeholder="VD: Đen nhám" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="fuel">Nhiên liệu</label>
                    <input type="text" id="fuel" name="fuel" class="lux-input" value="{{ old('fuel') }}" placeholder="VD: Xăng V8" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="transmission">Hộp số</label>
                    <input type="text" id="transmission" name="transmission" class="lux-input" value="{{ old('transmission') }}" placeholder="VD: 8 cấp Tự động" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="mileage_km">Odo (Số Km đã đi)</label>
                    <input type="number" id="mileage_km" name="mileage_km" class="lux-input" value="{{ old('mileage_km') }}" placeholder="VD: 0" min="0" max="5000000">
                </div>
            </div>
        </div>

        <div class="lux-card">
            <h3 class="lux-card-title">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Hình ảnh & Mô tả
            </h3>

            <div class="form-row" style="grid-template-columns: 1fr 2fr;">
                <div class="form-group">
                    <label style="margin-bottom: 1rem;">Ảnh đại diện xe</label>
                    <div class="img-upload-zone">
                        <div id="img-placeholder" class="img-placeholder">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span>Chưa chọn ảnh</span>
                        </div>

                        <img id="instant-preview" src="#" alt="Preview" class="img-preview-box">

                        <input type="file" id="image" name="image" accept="image/*" class="lux-input" style="padding: 0.5rem; cursor: pointer;" onchange="previewNewImage(event)">
                        <p style="font-size: 0.8rem; color: var(--muted); margin-top: 8px;">Định dạng hỗ trợ: JPG, PNG, WEBP</p>
                        @error('image') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group" style="display: flex; flex-direction: column;">
                    <label for="description">Bài viết mô tả chi tiết</label>
                    <textarea id="description" name="description" class="lux-textarea" rows="9" placeholder="Viết giới thiệu về những điểm nổi bật của chiếc xe này...">{{ old('description') }}</textarea>

                    <label class="featured-toggle" style="margin-top: auto;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <span>★ Đánh dấu là "Xe Nổi Bật" (Ưu tiên hiển thị trên Trang chủ)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <svg style="width:20px; height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Thêm Xe Vào Kho
            </button>
            <a href="{{ route('admin.cars.index') }}" class="btn-back">
                Hủy bỏ
            </a>
        </div>
    </form>
</div>

<script>
    // 1. Format Giá tiền trực tiếp khi gõ
    function formatPriceRealtime() {
        const input = document.getElementById('price');
        const display = document.getElementById('price-text');

        if(input.value) {
            const formatted = parseInt(input.value).toLocaleString('vi-VN');
            display.innerText = formatted + ' VNĐ';
        } else {
            display.innerText = '0 VNĐ';
        }
    }

    // 2. Load ảnh xem trước ngay khi vừa chọn file (Ẩn placeholder, hiện thẻ img)
    function previewNewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('instant-preview');
        const placeholder = document.getElementById('img-placeholder');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block'; // Hiện ảnh
                placeholder.style.display = 'none'; // Ẩn chữ "Chưa chọn ảnh"
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
        }
    }

    // Chạy format 1 lần khi trang vừa load để phòng trường hợp lỗi validation và old('price') đang có giá trị
    document.addEventListener("DOMContentLoaded", function() {
        formatPriceRealtime();
    });
</script>
@endsection
