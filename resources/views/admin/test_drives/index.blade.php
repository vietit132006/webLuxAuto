@extends('layouts.admin')
@section('title', 'Quản lý Đặt Lịch Lái Thử')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-test-drives-index.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="admin-test-drives-index-inline-30">
        <div>
            <h1 class="admin-test-drives-index-inline-29">
                <svg class="admin-test-drives-index-inline-28" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Quản lý lái thử
            </h1>
            <p class="admin-test-drives-index-inline-27">Theo dõi và xử lý các yêu cầu trải nghiệm xe từ khách hàng.</p>
        </div>

        <form class="admin-test-drives-index-inline-26" method="get" action="{{ route('admin.test_drives.index') }}">
            <select class="admin-test-drives-index-inline-25" name="status">
                <option class="admin-test-drives-index-inline-24" value="">Tất cả trạng thái</option>
                @foreach(['pending' => 'Chờ xử lý', 'approved' => 'Đã duyệt', 'rejected' => 'Đã huỷ', 'completed' => 'Hoàn thành'] as $k => $v)
                    <option class="admin-test-drives-index-inline-24" value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="admin-test-drives-index-inline-23" type="submit">
                Lọc
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="admin-test-drives-index-inline-22">
            <svg class="admin-test-drives-index-inline-21" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="admin-test-drives-index-inline-20">
        <div class="admin-test-drives-index-inline-19">
            <table class="admin-test-drives-index-inline-18">
                <thead class="admin-test-drives-index-inline-17">
                    <tr>
                        <th class="admin-test-drives-index-inline-16">Mã vé</th>
                        <th class="admin-test-drives-index-inline-16">Khách hàng</th>
                        <th class="admin-test-drives-index-inline-16">Xe yêu cầu</th>
                        <th class="admin-test-drives-index-inline-16">Ngày tạo</th>
                        <th class="admin-test-drives-index-inline-16">Trạng thái</th>
                        <th class="admin-test-drives-index-inline-15">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr class="admin-test-drives-index-inline-14" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                            <td class="admin-test-drives-index-inline-9">
                                <span class="admin-test-drives-index-inline-13">
                                    #{{ $b->ticket_id }}
                                </span>
                            </td>
                            <td class="admin-test-drives-index-inline-9">
                                <div class="admin-test-drives-index-inline-12">{{ $b->user->name ?? '—' }}</div>
                                <div class="admin-test-drives-index-inline-11">{{ $b->user->email ?? '' }}</div>
                            </td>
                            <td class="admin-test-drives-index-inline-9">
                                <div class="admin-test-drives-index-inline-12">{{ $b->car ? (($b->car->brand->name ?? '') . ' ' . $b->car->name) : '—' }}</div>
                                <div class="admin-test-drives-index-inline-11">{{ Str::limit($b->subject, 30) }}</div>
                            </td>
                            <td class="admin-test-drives-index-inline-10">
                                {{ $b->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="admin-test-drives-index-inline-9">
                                @php
                                    $badgeStyle = match($b->status) {
                                        'pending' => 'background: rgba(234, 179, 8, 0.1); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3);',
                                        'approved' => 'background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);',
                                        'rejected' => 'background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                                        'completed' => 'background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);',
                                        default => 'background: rgba(100, 116, 139, 0.1); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.3);',
                                    };
                                    $statusText = match($b->status) {
                                        'pending' => 'Chờ xử lý',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Đã huỷ',
                                        'completed' => 'Hoàn thành',
                                        default => ucfirst($b->status),
                                    };
                                @endphp
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; {{ $badgeStyle }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="admin-test-drives-index-inline-8">
                                <a class="admin-test-drives-index-inline-7" href="{{ route('admin.test_drives.show', $b->ticket_id) }}" onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)';" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text)';">
                                    Chi tiết
                                    <svg class="admin-test-drives-index-inline-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="admin-test-drives-index-inline-5" colspan="6">
                                <svg class="admin-test-drives-index-inline-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="admin-test-drives-index-inline-3">Chưa có yêu cầu lái thử nào.</p>
                                <p class="admin-test-drives-index-inline-2">Khi khách hàng đặt lịch, danh sách sẽ hiển thị tại đây.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="admin-test-drives-index-inline-1">
            {{ $bookings->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection