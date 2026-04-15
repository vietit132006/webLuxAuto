@extends('layouts.site')

@section('title', $car->brand->name . ' ' . $car->name)

@section('content')
<style>
    /* --- BREADCRUMB --- */
    .breadcrumb {
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        color: var(--muted);
    }
    .breadcrumb a {
        color: var(--text);
        text-decoration: none;
        transition: color 0.2s;
    }
    .breadcrumb a:hover {
        color: var(--accent);
    }

    /* --- LAYOUT CHÍNH --- */
    .product-detail {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2.5rem;
        background: var(--surface);
        padding: 0;
        border-radius: 0;
    }
    @media (min-width: 992px) {
        .product-detail { grid-template-columns: 1.4fr 1fr; }
    }

    /* --- BÊN TRÁI: HÌNH ẢNH --- */
    .pd-image-wrapper {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        aspect-ratio: 16 / 10;
        background: #0a0d12;
        position: relative;
    }
    .pd-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .badge-hot {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #e63946;
        color: #fff;
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(230, 57, 70, 0.4);
    }

    /* --- BÊN PHẢI: THÔNG TIN --- */
    .pd-info {
        display: flex;
        flex-direction: column;
    }
    .pd-brand {
        font-size: 1rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    .pd-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 1rem;
        line-height: 1.2;
    }
    .pd-price {
        font-size: 2rem;
        font-weight: 700;
        color: var(--accent); /* Giả sử màu accent của bạn là màu vàng kim/cam */
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* --- LƯỚI THÔNG SỐ (KIỂU CARD NHỎ) --- */
    .pd-specs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .spec-card {
        background: rgba(255, 255, 255, 0.03); /* Chỉnh lại màu này nếu site của bạn nền sáng */
        border: 1px solid var(--border);
        padding: 1rem;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .spec-card .label {
        font-size: 0.85rem;
        color: var(--muted);
    }
    .spec-card .value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
    }

    /* --- NÚT LIÊN HỆ --- */
    .pd-actions {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-top: auto;
    }
    .btn-primary-cta {
        background: var(--accent);
        color: #0c0f14;
        text-align: center;
        padding: 1rem;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(201, 169, 98, 0.3); /* Chỉnh màu shadow theo var(--accent) của bạn */
    }
    .btn-primary-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(201, 169, 98, 0.4);
    }
    .btn-secondary-cta {
        background: transparent;
        color: var(--text);
        border: 2px solid var(--border);
        text-align: center;
        padding: 1rem;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }
    .btn-secondary-cta:hover {
        border-color: var(--text);
    }

    /* --- MÔ TẢ CHI TIẾT DƯỚI CÙNG --- */
    .pd-description-box {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border);
    }
    .pd-description-box h3 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--text);
    }
    .pd-desc-content {
        line-height: 1.8;
        color: var(--text);
        font-size: 1.05rem;
    }
    /* --- CSS Nút Đặt Cọc Lux Auto --- */
    .deposit-box {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
        text-align: center;
    }
    .btn-deposit {
        width: 100%;
        background: linear-gradient(135deg, var(--accent) 0%, #b89453 100%);
        color: #000;
        border: none;
        padding: 1.2rem;
        border-radius: 8px;
        font-size: 1.25rem;
        font-weight: 800;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(201, 169, 98, 0.2);
        display: block;
        text-decoration: none;
    }
    .btn-deposit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(201, 169, 98, 0.4);
        background: linear-gradient(135deg, #fcebb6 0%, var(--accent) 100%);
    }
    .btn-deposit.login-to-book {
        background: #1f2937;
        color: var(--text);
        box-shadow: none;
    }
    .btn-deposit.login-to-book:hover {
        background: #374151;
    }
    .deposit-policy {
        color: var(--muted);
        font-size: 0.85rem;
        margin-top: 1rem;
        line-height: 1.5;
    }
</style>

<div class="wrap">
    <div class="breadcrumb">
        <a href="/">Trang chủ</a> &nbsp; / &nbsp;
        <a href="#">Xe đã qua sử dụng</a> &nbsp; / &nbsp;
        <span style="color: var(--accent)">{{ $car->brand->name ?? 'Hãng khác' }} {{ $car->name }}</span>
    </div>

<div class="product-detail">
        <div class="pd-left">
            <div class="pd-image-wrapper">
                @if($car->is_featured)
                    <div class="badge-hot">Xe nổi bật</div>
                @endif

                @if ($car->image)
                    <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}">
                @else
                    <img src="https://via.placeholder.com/800x500?text=Chua+co+hinh+anh" alt="Chưa có hình">
                @endif
            </div>
        </div>

        <div class="pd-right">
            <div class="pd-info">
                <div class="pd-brand">{{ $car->brand->name ?? 'Hãng xe' }}</div>
                <h1 class="pd-title">{{ $car->name }}</h1>
@if(isset($car->status))
    <div style="margin-top: 0.5rem; margin-bottom: 1.5rem;">
        <span style="display: inline-block; background: {{ $car->status == 1 ? 'var(--accent)' : '#4b5563' }}; color: {{ $car->status == 1 ? '#000' : '#fff' }}; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.9rem; font-weight: bold;">
            <i style="margin-right: 5px;">{{ $car->status == 1 ? '✨' : '🔄' }}</i>
            {{ $car->status == 1 ? 'Mới 100%' : 'Xe lướt (Cũ)' }}
        </span>
    </div>
@endif
                <div class="pd-price">
                    {{ number_format($car->price, 0, ',', '.') }} VNĐ
                </div>
                @if($errors->any())
    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-top: 1rem; margin-bottom: 1rem;">
        <strong style="display: block; margin-bottom: 0.5rem;">⚠️ Chú ý:</strong>
        <ul style="margin: 0; padding-left: 1.5rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="deposit-box">
    @auth
        <form action="{{ route('order.deposit', $car->car_id) }}" method="POST" onsubmit="return confirm('Bạn xác nhận muốn đặt cọc 20.000.000 VNĐ để giữ chiếc {{ $car->name }} này chứ?');">
            @csrf
            <button type="submit" class="btn-deposit">
                ĐẶT CỌC NGAY
                <span style="display:block; font-weight:500; font-size:0.9rem; margin-top:5px; color: rgba(0,0,0,0.7);">
                    Phí giữ xe: 20.000.000 VNĐ
                </span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="btn-deposit login-to-book">
            ĐĂNG NHẬP ĐỂ ĐẶT CỌC
            <span style="display:block; font-weight:500; font-size:0.9rem; margin-top:5px; color: var(--muted);">
                Vui lòng đăng nhập tài khoản của bạn
            </span>
        </a>
    @endauth

    <div class="deposit-policy">
        🛡️ Xe sẽ được giữ chân trong 24h kể từ khi thanh toán tiền cọc thành công. Lux Auto cam kết hoàn tiền 100% nếu xe không đúng mô tả.
    </div>
</div>

              <div class="pd-specs-grid">

    <div class="spec-card">
        <span class="label">Năm sản xuất</span>
        <span class="value">{{ $car->year ?? 'Cập nhật sau' }}</span>
    </div>

    <div class="spec-card">
        <span class="label">Đã đi (Odo)</span>
        <span class="value">
            @if($car->mileage_km)
                {{ number_format($car->mileage_km, 0, ',', '.') }} km
            @else
                {{ (isset($car->status) && $car->status == 1) ? 'Xe mới 100%' : 'Chưa cập nhật' }}
            @endif
        </span>
    </div>

    <div class="spec-card">
        <span class="label">Nhiên liệu</span>
        <span class="value">{{ $car->fuel ?? 'Cập nhật sau' }}</span>
    </div>

    <div class="spec-card">
        <span class="label">Hộp số</span>
        <span class="value">{{ $car->transmission ?? 'Cập nhật sau' }}</span>
    </div>

    @if($car->color)
    <div class="spec-card">
        <span class="label">Màu ngoại thất</span>
        <span class="value">{{ $car->color }}</span>
    </div>
    @endif

</div>

                <div class="pd-actions">
                    <a href="tel:0988888888" class="btn-primary-cta">📞 Gọi Hotline tư vấn</a>

                    <a href="#" class="btn-secondary-cta">✉️ Đăng ký lái thử</a>
                </div>
            </div>
        </div>
    </div>

    @if ($car->description)
        <div class="pd-description-box">
            <h3>Thông tin chi tiết</h3>
            <div class="pd-desc-content">
                {!! nl2br(e($car->description)) !!}
            </div>
        </div>
    @endif
</div>
@endsection
