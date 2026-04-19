@extends('layouts.site') @section('title', ($type ?? 'support') === 'test_drive' ? 'Đặt lịch lái thử' : 'Tạo Yêu Cầu Hỗ Trợ')

@section('content')
<div class="wrap" style="max-width: 1200px; padding: 3rem 1.25rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <h1 style="margin: 0; font-size: 2rem; color: #f8fafc;">
            @if(($type ?? 'support') === 'test_drive')
                Đặt <span style="color: var(--accent);">lịch lái thử</span>
            @else
                Tạo <span style="color: var(--accent);">Ticket Hỗ Trợ</span>
            @endif
        </h1>
        <a href="{{ route('ticket.history') }}" style="padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-weight: 500; transition: 0.2s;">
            📋 Xem Lịch Sử
        </a>
    </div>

    <div style="background: var(--surface); padding: 2rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">

        @if(session('error'))
            <div style="padding: 1rem; margin-bottom: 1.5rem; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; border-radius: 8px;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('ticket.store') }}" method="POST">
            @csrf
            <input type="hidden" name="ticket_type" value="{{ old('ticket_type', $type ?? 'support') }}">
            <input type="hidden" name="car_id" value="{{ old('car_id', $car->car_id ?? '') }}">

            @if(($type ?? 'support') === 'test_drive' && ($car ?? null))
                <div style="margin-bottom: 1.25rem; padding: 0.9rem 1rem; border-radius: 10px; border: 1px solid var(--border); background: rgba(255,255,255,0.02);">
                    <div style="font-weight: 700; color: var(--text);">Xe đăng ký lái thử</div>
                    <div style="color: var(--muted); margin-top: 0.35rem;">
                        {{ $car->brand->name ?? '' }} {{ $car->name }} — Đời {{ $car->year ?? '—' }}
                    </div>
                </div>
            @endif
            @error('car_id') <span style="color: #ef4444; font-size: 0.85rem; margin: -0.75rem 0 1rem; display: block;">{{ $message }}</span> @enderror

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--muted);">Tiêu đề hỗ trợ <span style="color: #ef4444;">*</span></label>
                <input type="text" name="subject"
                    value="{{ old('subject', (($type ?? 'support') === 'test_drive' && ($car ?? null)) ? ('Đặt lịch lái thử: ' . (($car->brand->name ?? '') . ' ' . $car->name)) : '') }}"
                    placeholder="{{ (($type ?? 'support') === 'test_drive') ? 'Ví dụ: Lái thử vào cuối tuần, khung giờ 9-11h...' : 'Ví dụ: Cần tư vấn thủ tục trả góp xe C300...' }}"
                    required
                    style="width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid var(--border); background: #0a0d12; color: var(--text); font-size: 1rem; transition: border-color 0.2s;">
                @error('subject') <span style="color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--muted);">Nội dung chi tiết <span style="color: #ef4444;">*</span></label>
                <textarea name="message" rows="6"
                    placeholder="{{ (($type ?? 'support') === 'test_drive') ? 'Ghi rõ: ngày/giờ mong muốn, địa điểm, số điện thoại liên hệ...' : 'Mô tả chi tiết vấn đề bạn đang gặp phải...' }}"
                    required
                    style="width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid var(--border); background: #0a0d12; color: var(--text); font-size: 1rem; transition: border-color 0.2s; resize: vertical;"></textarea>
                @error('message') <span style="color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block;">{{ $message }}</span> @enderror
            </div>

            <button type="submit" style="width: 100%; padding: 1rem; background: var(--accent); color: #000; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 15px rgba(201, 169, 98, 0.2);">
                {{ (($type ?? 'support') === 'test_drive') ? '📅 GỬI YÊU CẦU ĐẶT LỊCH LÁI THỬ' : '✉️ GỬI YÊU CẦU HỖ TRỢ' }}
            </button>
        </form>
    </div>
</div>

<style>
    input:focus, textarea:focus { border-color: var(--accent) !important; outline: none; }
    a:hover { border-color: var(--muted) !important; }
</style>
@endsection
