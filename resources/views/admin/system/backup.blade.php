@extends('layouts.admin')
@section('title', 'Sao lưu dữ liệu')

@section('content')
<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Sao lưu dữ liệu hệ thống</h2>
        </div>
        <div class="panel-body" style="padding: 2rem; text-align: center;">
            <div style="font-size: 4rem; margin-bottom: 2rem;">💾</div>
            <h3 style="color: var(--text); margin-bottom: 1rem;">Sao lưu toàn bộ cơ sở dữ liệu</h3>
            <p style="color: var(--muted); max-width: 600px; margin: 0 auto 2rem;">
                Dữ liệu của bạn rất quan trọng. Hãy thường xuyên thực hiện sao lưu để đảm bảo an toàn cho hệ thống. Bản sao lưu sẽ được xuất dưới định dạng JSON bao gồm tất cả các bảng chính.
            </p>

            <div style="background: rgba(201, 169, 98, 0.1); border: 1px solid var(--accent-dim); padding: 1.5rem; border-radius: 12px; max-width: 500px; margin: 0 auto;">
                <div style="font-weight: bold; color: var(--accent); margin-bottom: 1rem;">Lưu ý:</div>
                <ul style="text-align: left; color: var(--muted); font-size: 0.9rem; padding-left: 1.5rem; margin-bottom: 2rem;">
                    <li>Bao gồm: Người dùng, Xe, Hãng, Đơn hàng, Tin tức, Cài đặt.</li>
                    <li>Định dạng: JSON (Dễ dàng đọc và phục hồi).</li>
                    <li>Thời gian xử lý: Khoảng vài giây tùy thuộc vào khối lượng dữ liệu.</li>
                </ul>

                <a href="{{ route('admin.system.backup.download') }}" style="background: var(--accent); color: #000; padding: 1rem 2rem; border-radius: 8px; font-weight: 800; text-decoration: none; display: inline-block;">
                    TẢI BẢN SAO LƯU NGAY
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
