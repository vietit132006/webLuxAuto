@extends('layouts.admin')
@section('title', 'Quản lý hãng xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-brands-index.css')
    @endif
@endpush

@section('content')
<div class="brands-index-page">
    <div class="brands-index-head">
        <div>
            <p class="brands-eyebrow">Danh mục xe</p>
            <h1>Quản lý hãng xe</h1>
        </div>
        <a class="brands-add-btn" href="{{ route('admin.brands.create') }}">
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Thêm hãng
        </a>
    </div>

    @if(session('success'))
        <div id="flash-message" class="brands-alert brands-alert-success" role="status">
            <span>{{ session('success') }}</span>
            <button type="button" class="brands-alert-close" onclick="closeAlert()" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div id="flash-message" class="brands-alert brands-alert-error" role="alert">
            <span>{{ session('error') }}</span>
            <button type="button" class="brands-alert-close" onclick="closeAlert()" aria-label="Đóng thông báo">&times;</button>
        </div>
    @endif

    <form class="brands-filter" method="GET" action="{{ route('admin.brands.index') }}">
        <div class="brands-filter-field brands-filter-search">
            <label for="q">Tên hãng</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" autocomplete="off">
        </div>

        <div class="brands-filter-field">
            <label for="country">Quốc gia</label>
            <select id="country" name="country">
                <option value="">Tất cả</option>
                @foreach($countries as $country)
                    <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                @endforeach
            </select>
        </div>

        <div class="brands-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hiển thị</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Đang ẩn</option>
            </select>
        </div>

        <div class="brands-filter-actions">
            <button type="submit" class="brands-filter-submit">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044c0 .318-.126.623-.352.848l-6.298 6.299a1.2 1.2 0 0 0-.351.848v3.686a1.2 1.2 0 0 1-.658 1.071l-2.4 1.2A1.2 1.2 0 0 1 9.2 18.696v-4.883a1.2 1.2 0 0 0-.351-.848L2.55 6.666A1.2 1.2 0 0 1 2.2 5.818V4.774c0-.54.384-1.006.917-1.096A53.17 53.17 0 0 1 12 3Z" />
                </svg>
                Lọc
            </button>
            <a class="brands-filter-reset" href="{{ route('admin.brands.index') }}">Xóa lọc</a>
        </div>
    </form>

    <div class="brands-table-shell">
        <table class="brands-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Tên hãng</th>
                    <th>Quốc gia</th>
                    <th>Model</th>
                    <th>Xe trong kho</th>
                    <th>Có thể bán</th>
                    <th>Trạng thái</th>
                    <th>Thứ tự</th>
                    <th class="brands-actions-col">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        <td>
                            <div class="brands-logo-thumb">
                                @if($brand->logo_url)
                                    <img src="{{ $brand->logo_url }}" alt="Logo {{ $brand->name }}">
                                @else
                                    <span>{{ mb_substr($brand->name, 0, 1) }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="brands-name-stack">
                                <strong>{{ $brand->name }}</strong>
                                @if($brand->slug)
                                    <span>{{ $brand->slug }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $brand->country ?: 'Chưa cập nhật' }}</td>
                        <td>{{ number_format((int) $brand->car_models_count, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $brand->cars_count, 0, ',', '.') }}</td>
                        <td>{{ number_format((int) $brand->available_cars_count, 0, ',', '.') }}</td>
                        <td>
                            <span class="brands-status {{ $brand->is_active ? 'is-active' : 'is-inactive' }}">
                                {{ $brand->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
                            </span>
                        </td>
                        <td>{{ number_format((int) $brand->sort_order, 0, ',', '.') }}</td>
                        <td class="brands-actions-col">
                            <div class="brands-actions">
                                <a class="brands-action-btn brands-action-edit" href="{{ route('admin.brands.edit', $brand->brand_id) }}">Sửa</a>

                                <form action="{{ route('admin.brands.toggle-status', $brand->brand_id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="brands-action-btn brands-action-toggle" type="submit">
                                        {{ $brand->is_active ? 'Ẩn' : 'Hiện' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.brands.destroy', $brand->brand_id) }}" method="POST" onsubmit="return confirm('Xóa hãng xe này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="brands-action-btn brands-action-delete" type="submit">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="brands-empty" colspan="9">Chưa có hãng xe phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($brands->hasPages())
        <div class="brands-pagination">
            {{ $brands->links() }}
        </div>
    @endif
</div>

<script>
    function closeAlert() {
        const alertBox = document.getElementById('flash-message');
        if (!alertBox) {
            return;
        }

        alertBox.classList.add('is-hiding');
        setTimeout(() => alertBox.remove(), 250);
    }
</script>
@endsection
