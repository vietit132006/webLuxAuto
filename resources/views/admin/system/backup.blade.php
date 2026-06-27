@extends('layouts.admin')
@section('title', 'Sao lưu dữ liệu')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-system-backup.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Sao lưu dữ liệu hệ thống</h2>
        </div>
        <div class="panel-body admin-system-backup-inline-8">
            <div class="admin-system-backup-inline-7">💾</div>
            <h3 class="admin-system-backup-inline-6">Sao lưu toàn bộ cơ sở dữ liệu</h3>
            <p class="admin-system-backup-inline-5">
                Dữ liệu của bạn rất quan trọng. Hãy thường xuyên thực hiện sao lưu để đảm bảo an toàn cho hệ thống. Bản sao lưu sẽ được xuất dưới định dạng JSON bao gồm tất cả các bảng chính.
            </p>

            <div class="admin-system-backup-inline-4">
                <div class="admin-system-backup-inline-3">Lưu ý:</div>
                <ul class="admin-system-backup-inline-2">
                    <li>Bao gồm: Người dùng, Xe, Hãng, Đơn hàng, Tin tức, Cài đặt.</li>
                    <li>Định dạng: JSON (Dễ dàng đọc và phục hồi).</li>
                    <li>Thời gian xử lý: Khoảng vài giây tùy thuộc vào khối lượng dữ liệu.</li>
                </ul>

                <a class="admin-system-backup-inline-1" href="{{ route('admin.system.backup.download') }}">
                    TẢI BẢN SAO LƯU NGAY
                </a>
            </div>
        </div>
    </div>
</div>
@endsection