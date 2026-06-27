@extends('layouts.admin')

@section('title', 'Quản lý model xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-car-models-index.css')
    @endif
@endpush


@section('content')
    @include('admin.car_models._style')

    <div class="wrap model-wrap">
        <div class="model-header">
            <div>
                <h1 class="model-title">Quản lý model xe</h1>
                <p class="model-subtitle">Quản lý dòng xe, thông số kỹ thuật và số lượng xe đang sử dụng model.</p>
            </div>

            <a href="{{ route('admin.car-models.create') }}" class="lux-btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Thêm model
            </a>
        </div>
        @if (session('success'))
            <div id="success-alert" class="lux-flash-alert lux-flash-success">
                <div class="lux-flash-content">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>

                <button type="button" class="btn-close-alert" onclick="closeAlert('success-alert')" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="lux-flash-alert lux-flash-error">
                <div class="lux-flash-content">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>

                <button type="button" class="btn-close-alert" onclick="closeAlert('error-alert')" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
        <form class="model-toolbar" method="GET" action="{{ route('admin.car-models.index') }}">
            <div class="search-box">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="search" name="q" value="{{ $search ?? '' }}" class="lux-input"
                    placeholder="Tìm theo model, hãng, động cơ, nhiên liệu, hộp số..." autocomplete="off">
            </div>

            <button class="lux-btn-secondary" type="submit">Tìm kiếm</button>

            @if (!empty($search))
                <a href="{{ route('admin.car-models.index') }}" class="lux-btn-muted">Xóa lọc</a>
            @endif
        </form>

        <div class="model-card">
            @if ($carModels->isEmpty())
                <div class="empty-state">
                    <svg class="admin-car-models-index-inline-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h7.5m0 0a1.5 1.5 0 003 0m-3 0a1.5 1.5 0 013 0m-3 0H6.75m13.5-7.5H3.75m16.5 0-2.25-4.5a2.25 2.25 0 00-2.012-1.244H8.012A2.25 2.25 0 006 6.75l-2.25 4.5" />
                    </svg>
                    <div class="admin-car-models-index-inline-7">Chưa có model xe</div>
                    <div>Hãy thêm model đầu tiên để sử dụng khi tạo xe trong kho.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="model-table">
                        <thead>
                            <tr>
                                <th>Model / Hãng</th>
                                <th>Thông số chính</th>
                                <th>Số chỗ</th>
                                <th>Xuất xứ</th>
                                <th class="admin-car-models-index-inline-2">Xe đang dùng</th>
                                <th class="admin-car-models-index-inline-6">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($carModels as $model)
                                <tr>
                                    <td>
                                        <div class="model-main">
                                            <span class="model-name">{{ $model->name }}</span>
                                            <span class="model-brand">{{ $model->brand?->name ?? 'Chưa có hãng' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="spec-grid">
                                            @if ($model->engine)
                                                <span class="spec-pill">{{ $model->engine }}</span>
                                            @endif
                                            @if ($model->fuel_type)
                                                <span class="spec-pill">{{ $model->fuel_type }}</span>
                                            @endif
                                            @if ($model->transmission)
                                                <span class="spec-pill">{{ $model->transmission }}</span>
                                            @endif
                                            @if ($model->body_type)
                                                <span class="spec-pill">{{ $model->body_type }}</span>
                                            @endif
                                            @if ($model->drive_type)
                                                <span class="spec-pill">{{ $model->drive_type }}</span>
                                            @endif

                                            @if (!$model->engine && !$model->fuel_type && !$model->transmission && !$model->body_type && !$model->drive_type)
                                                <span class="admin-car-models-index-inline-3">Chưa cập nhật</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="admin-car-models-index-inline-5">
                                        {{ $model->seats ? $model->seats . ' chỗ' : '-' }}
                                        @if ($model->doors)
                                            <span class="admin-car-models-index-inline-4">/ {{ $model->doors }}
                                                cửa</span>
                                        @endif
                                    </td>
                                    <td class="admin-car-models-index-inline-3">{{ $model->origin ?? '-' }}</td>
                                    <td class="admin-car-models-index-inline-2">
                                        <span class="count-badge">{{ $model->cars_count }}</span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('admin.car-models.show', $model->id) }}"
                                                class="lux-btn-secondary icon-btn" title="Xem chi tiết">
                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="1.7">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('admin.car-models.edit', $model->id) }}"
                                                class="lux-btn-secondary icon-btn" title="Sửa model">
                                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="1.7">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931Z" />
                                                </svg>
                                            </a>

                                            @if ($model->cars_count > 0)
                                                <button type="button" class="lux-btn-muted icon-btn safe-delete"
                                                    title="Không thể xóa vì model đang được xe sử dụng" disabled>
                                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="1.7">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </button>
                                            @else
                                                <form class="admin-car-models-index-inline-1" action="{{ route('admin.car-models.destroy', $model->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa model này không?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="lux-btn-danger icon-btn"
                                                        title="Xóa model">
                                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="1.7">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($carModels->hasPages())
            <div class="pagination-wrap">
                {{ $carModels->links() }}
            </div>
        @endif
    </div>
    <script>
        function closeAlert(id) {
            const alertBox = document.getElementById(id);

            if (alertBox) {
                alertBox.classList.add('hide');

                setTimeout(() => {
                    alertBox.remove();
                }, 500);
            }
        }

        setTimeout(() => {
            closeAlert('success-alert');
            closeAlert('error-alert');
        }, 2000);
    </script>
@endsection