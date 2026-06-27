@extends('layouts.admin')
@section('title', 'Quản lý Hãng Xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-brands-index.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="header-actions admin-brands-index-inline-13">
        <h1 class="page-title admin-brands-index-inline-12">Quản lý Hãng Xe</h1>
        <a class="admin-brands-index-inline-11" href="{{ route('admin.brands.create') }}">+ Thêm hãng mới</a>
    </div>


    {{-- Thông báo Thành Công --}}
    @if(session('success'))
        <div id="flash-message" class="flash-alert flash-success">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    {{-- Thông báo Lỗi (Đã được tách ra ngoài độc lập) --}}
    @if(session('error'))
        <div id="flash-message" class="flash-alert flash-error">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    {{-- Script đóng thông báo --}}
    <script>
        function closeAlert() {
            const alertBox = document.getElementById('flash-message');
            if (alertBox) {
                alertBox.classList.add('hide');
                setTimeout(() => { alertBox.remove(); }, 500);
            }
        }

        // Tự động tắt sau 3 giây (3000ms) để người dùng kịp đọc lỗi dài
        setTimeout(() => { closeAlert(); }, 3000);
    </script>
    <div class="table-responsive admin-brands-index-inline-10">
        <table class="admin-table admin-brands-index-inline-9">
            <thead>
                <tr class="admin-brands-index-inline-8">
                    <th class="admin-brands-index-inline-5">ID</th>
                    <th class="admin-brands-index-inline-5">Tên Hãng</th>
                    <th class="admin-brands-index-inline-5">Quốc gia</th>
                    <th class="admin-brands-index-inline-4">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                <tr class="admin-brands-index-inline-7">
                    <td class="admin-brands-index-inline-5">#{{ $brand->brand_id }}</td>
                    <td class="admin-brands-index-inline-6">{{ $brand->name }}</td>
                    <td class="admin-brands-index-inline-5">{{ $brand->country ?? 'Chưa cập nhật' }}</td>
                    <td class="admin-brands-index-inline-4">
                        <a class="admin-brands-index-inline-3" href="{{ route('admin.brands.edit', $brand->brand_id) }}">Sửa</a>
                        <form class="admin-brands-index-inline-2" action="{{ route('admin.brands.destroy', $brand->brand_id) }}" method="POST" onsubmit="return confirm('Xóa hãng xe này?');">
                            @csrf @method('DELETE')
                            <button class="admin-brands-index-inline-1" type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection