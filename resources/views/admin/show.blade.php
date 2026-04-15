@extends('layouts.admin')

@section('title', $car->title)

@section('content')
<style>
    .vehicle-detail {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        background: var(--surface);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    @media (min-width: 900px) {
        .vehicle-detail { grid-template-columns: 1.2fr 1fr; }
    }
    .vd-img {
        aspect-ratio: 16 / 10;
        background: #f1f5f9;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
    }
    .vd-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .vd-head {
        margin: 0 0 0.5rem;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text);
    }
    .vd-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #e63946; /* Màu đỏ cho giá nổi bật */
        margin: 0 0 1.5rem;
    }
    .vd-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid var(--border);
        font-size: 0.95rem;
        color: var(--text);
        margin-bottom: 1.5rem;
    }
    .vd-specs div {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .vd-specs span.label {
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .vd-desc {
        line-height: 1.6;
        color: var(--text);
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
    }
    .vd-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    .vd-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.7rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-back {
        border: 1px solid var(--border);
        color: var(--text);
        background: #fff;
    }
    .btn-back:hover {
        background: #f1f5f9;
    }
    .btn-edit {
        background: var(--accent); /* Nút sửa nổi bật */
        color: #0c0f14;
        border: none;
    }
    .btn-edit:hover {
        opacity: 0.9;
    }
    .badge-featured {
        display: inline-block;
        background: #fbbf24;
        color: #000;
        font-size: 0.75rem;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-weight: bold;
        vertical-align: middle;
        margin-left: 10px;
    }
</style>

<div class="wrap">
    <div class="vehicle-detail">
        <div class="vd-left">
            <div class="vd-img">
                @if ($car->image)
                    <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->title }}">
                @else
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        Chưa có hình ảnh
                    </div>
                @endif
            </div>
        </div>

        <div class="vd-right">
            <h1 class="vd-head">
                {{ $car->title }}
                @if($car->is_featured)
                    <span class="badge-featured">⭐ Nổi bật</span>
                @endif
            </h1>

            <p class="vd-price">{{ number_format($car->price, 0, ',', '.') }} VNĐ</p>

            <div class="vd-specs">
                <div><span class="label">Mã ID</span> <strong>#{{ $car->car_id }}</strong></div>
                <div><span class="label">Hãng xe</span> <strong>{{ $car->brand->name ?? 'N/A' }}</strong></div>
                <div><span class="label">Dòng xe</span> <strong>{{ $car->name }}</strong></div>
                <div><span class="label">Năm sản xuất</span> <strong>{{ $car->year }}</strong></div>

                <div><span class="label">Số Km đã đi</span> <strong>{{ $car->mileage_km ? number_format($car->mileage_km, 0, ',', '.') . ' km' : 'Chưa cập nhật' }}</strong></div>
                <div><span class="label">Tồn kho</span> <strong>{{ $car->stock }} chiếc</strong></div>

                <div><span class="label">Nhiên liệu</span> <strong>{{ $car->fuel_type ?? 'Chưa cập nhật' }}</strong></div>
                <div><span class="label">Hộp số</span> <strong>{{ $car->transmission ?? 'Chưa cập nhật' }}</strong></div>

                <div><span class="label">Màu sắc</span> <strong>{{ $car->color ?? 'Chưa cập nhật' }}</strong></div>
                <div><span class="label">Ngày đăng</span> <strong>{{ $car->created_at ? $car->created_at->format('d/m/Y H:i') : 'N/A' }}</strong></div>
            </div>

            @if ($car->description)
                <div class="vd-desc">
                    <strong>Mô tả chi tiết:</strong><br>
                    {!! nl2br(e($car->description)) !!} </div>
            @endif

            <div class="vd-actions">
                <a href="{{ route('admin.cars.index') }}" class="btn-back">← Quay lại danh sách</a>

                <a href="{{ route('admin.cars.edit', $car->car_id) }}" class="btn-edit">✏️ Sửa thông tin</a>
            </div>
        </div>
    </div>
</div>
@endsection
