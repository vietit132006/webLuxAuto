@extends('layouts.admin')

@section('title', 'Thêm xe')

@section('content')
<style>
.form-wrap { max-width: 700px; margin: 0 auto; background: var(--surface); padding: 2rem; border-radius: 12px; border: 1px solid var(--border); }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); }
.form-group input:not([type="file"]), .form-group textarea, .form-group select { width: 100%; padding: 0.6rem 0.9rem; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--accent-dim); box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15); }
.btn-submit { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--accent); color: #0c0f14; font-weight: 600; cursor: pointer; }
.btn-back { padding: 0.6rem 1.2rem; border-radius: 8px; border: 1px solid var(--border); text-decoration: none; color: var(--text); margin-left: 10px; }
.error { color: red; font-size: 0.85rem; margin-top: 0.3rem;}
input[type="file"] { padding: 0.4rem; background: #fff; width: 100%; border-radius: 8px;}
</style>

<div class="wrap form-wrap">
    <h1 class="page-title" style="margin-bottom: 1.5rem;">Thêm xe mới</h1>

    <form method="POST" action="{{ route('admin.cars.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="background: var(--surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <h3 style="margin-top: 0; color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Thông tin cơ bản</h3>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Hãng xe <span style="color: red;">*</span></label>
                    <select name="brand_id" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                        <option value="">-- Chọn hãng xe --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_id }}" {{ old('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Tên xe / Dòng xe <span style="color: red;">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1rem;">
                <div class="form-group">
                    <label>Giá bán (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" required min="0" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>

                <div class="form-group">
                    <label>Tình trạng <span style="color: red;">*</span></label>
                    <select name="status" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Mới 100%</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Xe lướt (Cũ)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tồn kho (Stock) <span style="color: red;">*</span></label>
                    <input type="number" name="stock" value="{{ old('stock', 1) }}" required min="0" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>
            </div>
        </div>

        <div style="background: var(--surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <h3 style="margin-top: 0; color: var(--accent); margin-bottom: 1rem; font-size: 1.1rem;">Thông số kỹ thuật</h3>

            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem;">
                <div class="form-group">
                    <label>Năm SX <span style="color: red;">*</span></label>
                    <input type="number" name="year" value="{{ old('year') }}" required min="1990" max="{{ date('Y') + 1 }}" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>

                <div class="form-group">
                    <label>Màu sắc</label>
                    <input type="text" name="color" value="{{ old('color') }}" placeholder="VD: Đen nhám" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>

                <div class="form-group">
                    <label>Loại nhiên liệu</label>
                    <input type="text" name="fuel" value="{{ old('fuel') }}" placeholder="VD: Xăng, Điện" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>

                <div class="form-group">
                    <label>Hộp số</label>
                    <input type="text" name="transmission" value="{{ old('transmission') }}" placeholder="VD: Số tự động" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>

                <div class="form-group">
                    <label>Số Km đã đi</label>
                    <input type="number" name="mileage_km" value="{{ old('mileage_km') }}" placeholder="VD: 15000" min="0" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                </div>
            </div>
        </div>

        <div style="background: var(--surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 1.5rem;">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold; margin-bottom: 0.5rem; display: block;">Hình ảnh xe</label>
                <input type="file" name="image" accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px dashed var(--accent); border-radius: 6px; background: rgba(201, 169, 98, 0.05); color: var(--text);">
                @error('image') <div style="color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label style="font-weight: bold; margin-bottom: 0.5rem; display: block;">Mô tả chi tiết</label>
                <textarea name="description" rows="5" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">{{ old('description') }}</textarea>
            </div>

            <div class="form-group" style="margin-top: 1rem; background: rgba(201, 169, 98, 0.1); padding: 12px; border-radius: 8px;">
                <label style="margin: 0; cursor: pointer; display: flex; align-items: center; gap: 8px; color: var(--accent); font-weight: bold;">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--accent);">
                    Đánh dấu là "Xe Nổi Bật" (Hiển thị ngay trên Trang Chủ)
                </label>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn-submit" style="background: var(--accent); color: #000; font-weight: bold; padding: 0.8rem 2rem; border-radius: 6px; border: none; cursor: pointer;">Lưu thông tin</button>
            <a href="{{ route('admin.cars.index') }}" class="btn-back" style="padding: 0.8rem 2rem; border-radius: 6px; border: 1px solid var(--border); color: var(--text); text-decoration: none; display: flex; align-items: center;">Hủy & Quay lại</a>
        </div>
    </form>
</div>
@endsection
