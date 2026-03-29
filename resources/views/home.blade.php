@extends('layouts.site')

@section('title', 'Trang chủ')

@section('content')
<style>
    .hero {
        padding: 2.5rem 0 3rem;
        border-bottom: 1px solid var(--border);
        margin-bottom: 2.5rem;
        background: radial-gradient(ellipse 80% 60% at 50% -20%, rgba(201, 169, 98, 0.12), transparent);
    }
    .hero h1 {
        margin: 0 0 0.75rem;
        font-size: clamp(1.75rem, 4vw, 2.35rem);
        font-weight: 700;
        letter-spacing: -0.02em;
    }
    .hero p {
        margin: 0 0 1.5rem;
        color: var(--muted);
        max-width: 36rem;
        font-size: 1.05rem;
    }
    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.65rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9375rem;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--accent), var(--accent-dim));
        color: #0c0f14;
    }
    .btn-primary:hover { filter: brightness(1.08); color: #0c0f14; }
    .btn-ghost {
        border: 1px solid var(--border);
        color: var(--text);
    }
    .btn-ghost:hover { border-color: var(--accent-dim); color: var(--accent); }
    .section-head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .section-head h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 600;
    }
    .section-head a { font-size: 0.9375rem; }
    .empty-state {
        padding: 2rem;
        text-align: center;
        color: var(--muted);
        border: 1px dashed var(--border);
        border-radius: 12px;
    }
    .banners {
        margin: 0 0 2.5rem;
    }
    .banner-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1fr;
    }
    @media (min-width: 640px) {
        .banner-grid { grid-template-columns: repeat(3, 1fr); }
    }
    .banner-card {
        display: block;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 1.25rem 1.35rem;
        min-height: 132px;
        text-decoration: none;
        color: inherit;
        background: var(--surface);
        transition: border-color 0.2s, transform 0.2s;
    }
    .banner-card::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: 0.55;
        pointer-events: none;
        background: var(--banner-bg, linear-gradient(135deg, rgba(201, 169, 98, 0.12) 0%, transparent 55%));
    }
    .banner-card:hover {
        border-color: var(--accent-dim);
        transform: translateY(-2px);
    }
    .banner-card:hover .banner-card__cta { color: #e4d08a; }
    .banner-card--gold { --banner-bg: linear-gradient(145deg, rgba(201, 169, 98, 0.18) 0%, rgba(12, 15, 20, 0) 60%); }
    .banner-card--slate { --banner-bg: linear-gradient(145deg, rgba(100, 116, 139, 0.2) 0%, rgba(12, 15, 20, 0) 55%); }
    .banner-card--deep { --banner-bg: linear-gradient(145deg, rgba(30, 58, 95, 0.35) 0%, rgba(12, 15, 20, 0) 50%); }
    .banner-card__inner { position: relative; z-index: 1; }
    .banner-card__tag {
        display: inline-block;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 0.5rem;
    }
    .banner-card__title {
        margin: 0 0 0.35rem;
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text);
    }
    .banner-card__desc {
        margin: 0;
        font-size: 0.875rem;
        color: var(--muted);
        line-height: 1.45;
    }
    .banner-card__cta {
        margin-top: 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--accent);
    }
    .banner-hero {
        margin-bottom: 1.25rem;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid var(--border);
        min-height: 200px;
        display: flex;
        align-items: flex-end;
        background:
            linear-gradient(90deg, rgba(12, 15, 20, 0.92) 0%, rgba(12, 15, 20, 0.45) 45%, rgba(12, 15, 20, 0.2) 100%),
            url("https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1400&q=80") center / cover no-repeat;
    }
    .banner-hero__box {
        padding: 1.5rem 1.5rem 1.35rem;
        max-width: 26rem;
    }
    .banner-hero__title {
        margin: 0 0 0.4rem;
        font-size: clamp(1.2rem, 3vw, 1.5rem);
        font-weight: 700;
        color: var(--text);
    }
    .banner-hero__desc {
        margin: 0 0 1rem;
        font-size: 0.9375rem;
        color: var(--muted);
        line-height: 1.5;
    }
    .banner-hero .btn-primary { width: fit-content; }
</style>

<div class="hero">
    <div class="wrap">
        <h1>Xe sang đã qua sử dụng — tuyển chọn & minh bạch</h1>
        <p>Lux Auto giúp bạn so sánh nhanh các mẫu xe cao cấp: đời xe, số km, nhiên liệu và mức giá tham khảo.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="{{ route('vehicles.index') }}">Xem danh sách xe</a>
            @guest
                <a class="btn btn-ghost" href="{{ route('login') }}">Đăng nhập nhân viên</a>
            @endguest
        </div>
    </div>
</div>

<div class="wrap banners">
    <a href="{{ route('vehicles.index') }}" class="banner-hero">
        <div class="banner-hero__box">
            <h2 class="banner-hero__title">Bộ sưu tập xe sang đang cập nhật</h2>
            <p class="banner-hero__desc">Khám phá các mẫu sedan, SUV và coupé — thông tin giá và tiến trình xe được cập nhật thường xuyên.</p>
            <span class="btn btn-primary">Khám phá danh mục</span>
        </div>
    </a>

    <div class="banner-grid">
        <a href="{{ route('vehicles.index') }}" class="banner-card banner-card--gold">
            <div class="banner-card__inner">
                <span class="banner-card__tag">Tài chính</span>
                <h3 class="banner-card__title">Hỗ trợ gói vay &amp; sang tên</h3>
                <p class="banner-card__desc">Tư vấn thủ tục và phương án thanh toán linh hoạt cho khách mua xe đã qua sử dụng.</p>
                <span class="banner-card__cta">Xem xe phù hợp →</span>
            </div>
        </a>
        <a href="{{ route('vehicles.index') }}" class="banner-card banner-card--slate">
            <div class="banner-card__inner">
                <span class="banner-card__tag">Minh bạch</span>
                <h3 class="banner-card__title">Lịch bảo dưỡng &amp; nguồn gốc</h3>
                <p class="banner-card__desc">Ưu tiên xe có hồ sơ rõ ràng, km thực tế và lịch sử kiểm định theo tiêu chí nội bộ.</p>
                <span class="banner-card__cta">Danh sách xe →</span>
            </div>
        </a>
        <a href="{{ route('vehicles.index') }}" class="banner-card banner-card--deep">
            <div class="banner-card__inner">
                <span class="banner-card__tag">Ưu đãi</span>
                <h3 class="banner-card__title">Cập nhật giá &amp; ưu đãi theo đợt</h3>
                <p class="banner-card__desc">Theo dõi bảng giá tham khảo và các gói hỗ trợ khi đặt cọc hoặc đổi xe trong thời gian ngắn.</p>
                <span class="banner-card__cta">Xem ngay →</span>
            </div>
        </a>
    </div>
</div>

<div class="wrap">
    <div class="section-head">
        <h2>Xe nổi bật</h2>
        @if ($featuredVehicles->isNotEmpty())
            <a href="{{ route('vehicles.index') }}">Xem tất cả →</a>
        @endif
    </div>

    @if ($featuredVehicles->isEmpty())
        <div class="empty-state">
            Chưa có xe nổi bật. Chạy migration và seeder: <code style="color:var(--accent);">php artisan migrate --seed</code>
        </div>
    @else
        <div class="grid-cards">
            @foreach ($featuredVehicles as $vehicle)
                @include('partials.vehicle-card', ['vehicle' => $vehicle])
            @endforeach
        </div>
    @endif
</div>
@endsection
