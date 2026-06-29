@extends('layouts.admin')

@section('title', 'Khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-promotions.css')
    @endif
@endpush

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . ' đ';
@endphp

<div class="admin-promotions-page">
    <div class="admin-promotions-head">
        <div>
            <h1>Khuyến mãi</h1>
            <p>Marketing / Quản lý chiến dịch ưu đãi</p>
        </div>

        <div class="promotion-head-actions">
            @can('reports.view')
                <a class="promotion-secondary" href="{{ route('admin.reports.promotions') }}">Báo cáo</a>
            @endcan
            @can('promotions.create')
                <a class="promotion-primary" href="{{ route('admin.promotions.create') }}">Tạo khuyến mãi</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="promotion-alert is-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="promotion-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="promotion-stats-grid">
        <div class="promotion-stat">
            <span>Tổng chương trình</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Đang diễn ra</span>
            <strong>{{ number_format($stats['active']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Sắp diễn ra</span>
            <strong>{{ number_format($stats['scheduled']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Đã hết hạn</span>
            <strong>{{ number_format($stats['expired']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Nổi bật</span>
            <strong>{{ number_format($stats['featured']) }}</strong>
        </div>
    </section>

    <form class="promotion-filter" method="get" action="{{ route('admin.promotions') }}">
        <div class="promotion-filter-field is-wide">
            <label for="q">Từ khóa</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Mã, tên, mô tả, quà tặng">
        </div>

        <div class="promotion-filter-field">
            <label for="promotion_type">Loại</label>
            <select id="promotion_type" name="promotion_type">
                <option value="">Tất cả loại</option>
                @foreach(\App\Models\Promotion::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected($filters['promotion_type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="promotion-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả trạng thái</option>
                @foreach(\App\Models\Promotion::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="promotion-filter-field">
            <label for="period">Thời gian</label>
            <select id="period" name="period">
                <option value="">Tất cả thời gian</option>
                @foreach($periodOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="promotion-filter-field">
            <label for="featured">Nổi bật</label>
            <select id="featured" name="featured">
                <option value="">Tất cả</option>
                <option value="1" @selected($filters['featured'] === '1')>Có</option>
                <option value="0" @selected($filters['featured'] === '0')>Không</option>
            </select>
        </div>

        <div class="promotion-filter-field">
            <label for="brand_id">Hãng xe</label>
            <select id="brand_id" name="brand_id">
                <option value="">Tất cả hãng</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}" @selected((string) $filters['brand_id'] === (string) $brand->brand_id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="promotion-filter-field">
            <label for="model_id">Model xe</label>
            <select id="model_id" name="model_id">
                <option value="">Tất cả model</option>
                @foreach($carModels as $model)
                    <option value="{{ $model->id }}" @selected((string) $filters['model_id'] === (string) $model->id)>
                        {{ $model->brand?->name }} {{ $model->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="promotion-filter-actions">
            <button type="submit">Lọc</button>
            <a href="{{ route('admin.promotions') }}">Xóa lọc</a>
        </div>
    </form>

    <section class="promotions-table-wrap">
        <table class="promotions-table">
            <thead>
                <tr>
                    <th>Mã KM</th>
                    <th>Banner</th>
                    <th>Chương trình</th>
                    <th>Loại</th>
                    <th>Ưu đãi</th>
                    <th>Thời gian</th>
                    <th>Đối tượng</th>
                    <th>Trạng thái</th>
                    <th>Nổi bật</th>
                    <th>Sử dụng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promotion)
                    @php
                        $usedCount = (int) $promotion->quote_promotions_count + (int) $promotion->order_promotions_count;
                    @endphp
                    <tr>
                        <td>
                            <span class="promotion-code">{{ $promotion->promotion_code }}</span>
                        </td>
                        <td>
                            @if($promotion->bannerUrl())
                                <img class="promotion-thumb" src="{{ $promotion->bannerUrl() }}" alt="{{ $promotion->banner_alt ?: $promotion->title }}">
                            @else
                                <div class="promotion-thumb is-empty">KM</div>
                            @endif
                        </td>
                        <td class="promotion-title-cell">
                            <strong>{{ $promotion->title }}</strong>
                            <span>{{ $promotion->short_description ?: 'Chưa có mô tả ngắn' }}</span>
                        </td>
                        <td>{{ $promotion->typeLabel() }}</td>
                        <td>
                            <strong>{{ $promotion->discountLabel() }}</strong>
                            @if($promotion->max_discount_value)
                                <span class="promotion-muted">Tối đa {{ $money($promotion->max_discount_value) }}</span>
                            @endif
                        </td>
                        <td class="promotion-date-cell">
                            <span>{{ $promotion->start_at?->format('d/m/Y H:i') ?: 'Không giới hạn' }}</span>
                            <span>{{ $promotion->end_at?->format('d/m/Y H:i') ?: 'Không giới hạn' }}</span>
                        </td>
                        <td>{{ $promotion->targetSummary() }}</td>
                        <td>
                            <span class="promotion-status {{ $promotion->statusBadgeClass() }}">{{ $promotion->statusLabel() }}</span>
                            @unless($promotion->is_public)
                                <span class="promotion-mini-badge">Ẩn frontend</span>
                            @endunless
                        </td>
                        <td>
                            <span class="promotion-featured {{ $promotion->is_featured ? 'is-on' : '' }}">
                                {{ $promotion->is_featured ? 'Có' : 'Không' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ number_format($promotion->usage_count) }}</strong>
                            <span class="promotion-muted">{{ $usedCount }} liên kết</span>
                        </td>
                        <td>
                            <div class="promotion-actions">
                                @can('promotions.edit')
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}">Sửa</a>
                                @endcan

                                @can('promotions.publish')
                                    @if($promotion->status !== \App\Models\Promotion::STATUS_ACTIVE || !$promotion->is_public)
                                        <form method="post" action="{{ route('admin.promotions.publish', $promotion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Xuất bản</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('promotions.edit')
                                    @if($promotion->status !== \App\Models\Promotion::STATUS_ARCHIVED)
                                        <form method="post" action="{{ route('admin.promotions.archive', $promotion) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Lưu trữ</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('promotions.delete')
                                    <form method="post" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Xóa hoặc lưu trữ khuyến mãi này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="is-danger" type="submit">{{ $usedCount > 0 ? 'Lưu trữ' : 'Xóa' }}</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="promotion-empty">Chưa có chương trình khuyến mãi phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if($promotions->hasPages())
        <div class="promotion-pagination">{{ $promotions->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
