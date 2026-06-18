@extends('layouts.site') @section('title', ($type ?? 'support') === 'test_drive' ? 'Đặt lịch lái thử' : 'Tạo Yêu Cầu Hỗ Trợ')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-tickets-create.css')
    @endif
@endpush


@section('content')
<div class="wrap client-tickets-create-inline-19">
    <div class="client-tickets-create-inline-18">
        <h1 class="client-tickets-create-inline-17">
            @if(($type ?? 'support') === 'test_drive')
                Đặt <span class="client-tickets-create-inline-16">lịch lái thử</span>
            @else
                Tạo <span class="client-tickets-create-inline-16">Ticket Hỗ Trợ</span>
            @endif
        </h1>
        <a class="client-tickets-create-inline-15" href="{{ route('ticket.history') }}">
            📋 Xem Lịch Sử
        </a>
    </div>

    <div class="client-tickets-create-inline-14">

        @if(session('error'))
            <div class="client-tickets-create-inline-13">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('ticket.store') }}" method="POST">
            @csrf
            <input type="hidden" name="ticket_type" value="{{ old('ticket_type', $type ?? 'support') }}">
            <input type="hidden" name="car_id" value="{{ old('car_id', $car->car_id ?? '') }}">

            @if(($type ?? 'support') === 'test_drive' && ($car ?? null))
                <div class="client-tickets-create-inline-12">
                    <div class="client-tickets-create-inline-11">Xe đăng ký lái thử</div>
                    <div class="client-tickets-create-inline-10">
                        {{ $car->brand->name ?? '' }} {{ $car->name }} — Đời {{ $car->year ?? '—' }}
                    </div>
                </div>
            @endif
            @error('car_id') <span class="client-tickets-create-inline-9">{{ $message }}</span> @enderror

            <div class="client-tickets-create-inline-8">
                <label class="client-tickets-create-inline-5">Tiêu đề hỗ trợ <span class="client-tickets-create-inline-4">*</span></label>
                <input class="client-tickets-create-inline-7" type="text" name="subject"
                    value="{{ old('subject', (($type ?? 'support') === 'test_drive' && ($car ?? null)) ? ('Đặt lịch lái thử: ' . (($car->brand->name ?? '') . ' ' . $car->name)) : '') }}"
                    placeholder="{{ (($type ?? 'support') === 'test_drive') ? 'Ví dụ: Lái thử vào cuối tuần, khung giờ 9-11h...' : 'Ví dụ: Cần tư vấn thủ tục trả góp xe C300...' }}"
                    required>
                @error('subject') <span class="client-tickets-create-inline-2">{{ $message }}</span> @enderror
            </div>

            <div class="client-tickets-create-inline-6">
                <label class="client-tickets-create-inline-5">Nội dung chi tiết <span class="client-tickets-create-inline-4">*</span></label>
                <textarea class="client-tickets-create-inline-3" name="message" rows="6"
                    placeholder="{{ (($type ?? 'support') === 'test_drive') ? 'Ghi rõ: ngày/giờ mong muốn, địa điểm, số điện thoại liên hệ...' : 'Mô tả chi tiết vấn đề bạn đang gặp phải...' }}"
                    required></textarea>
                @error('message') <span class="client-tickets-create-inline-2">{{ $message }}</span> @enderror
            </div>

            <button class="client-tickets-create-inline-1" type="submit">
                {{ (($type ?? 'support') === 'test_drive') ? '📅 GỬI YÊU CẦU ĐẶT LỊCH LÁI THỬ' : '✉️ GỬI YÊU CẦU HỖ TRỢ' }}
            </button>
        </form>
    </div>
</div>

@endsection