@extends('layouts.admin')
@section('title', 'Quản lý Hãng Xe')

@section('content')
<div class="wrap">
    <div class="header-actions" style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0; font-size: 1.5rem;">Quản lý Hãng Xe</h1>
        <a href="{{ route('admin.brands.create') }}" style="background: var(--accent); color: #000; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold;">+ Thêm hãng mới</a>
    </div>

    @if(session('success'))
        <style>
            .flash-alert {
                background-color: #d1fae5;
                color: #065f46;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                border: 1px solid #34d399;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: opacity 0.5s ease, transform 0.5s ease;
            }
            .flash-alert.hide {
                opacity: 0;
                transform: translateY(-10px);
                pointer-events: none;
            }
            .btn-close-alert {
                background: none;
                border: none;
                color: #065f46;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                padding: 0 0 0 1rem;
                transition: transform 0.2s;
            }
            .btn-close-alert:hover {
                transform: scale(1.2);
                color: #047857;
            }
        </style>

        <div id="success-alert" class="flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>

        <script>
            function closeAlert() {
                const alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.classList.add('hide');
                    setTimeout(() => {
                        alertBox.remove();
                    }, 500);
                }
            }

            // Tự động gọi hàm đóng sau 2 giây (2000 ms)
            setTimeout(() => {
                closeAlert();
            }, 2000);
        </script>
    @endif
    <div class="table-responsive" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: rgba(255,255,255,0.05); color: var(--muted); text-transform: uppercase; font-size: 0.8rem;">
                    <th style="padding: 1rem;">ID</th>
                    <th style="padding: 1rem;">Tên Hãng</th>
                    <th style="padding: 1rem;">Quốc gia</th>
                    <th style="padding: 1rem; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                <tr style="border-top: 1px solid var(--border);">
                    <td style="padding: 1rem;">#{{ $brand->brand_id }}</td>
                    <td style="padding: 1rem; font-weight: bold; color: var(--text);">{{ $brand->name }}</td>
                    <td style="padding: 1rem;">{{ $brand->country ?? 'Chưa cập nhật' }}</td>
                    <td style="padding: 1rem; text-align: right;">
                        <a href="{{ route('admin.brands.edit', $brand->brand_id) }}" style="color: #facc15; margin-right: 10px;">Sửa</a>
                        <form action="{{ route('admin.brands.destroy', $brand->brand_id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Xóa hãng xe này?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; padding: 0;">Xóa</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
