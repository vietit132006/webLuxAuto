@extends('layouts.admin')
@section('title', 'Quản lý Đặt Lịch Lái Thử')

@section('content')
<div class="wrap">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.8rem; color: var(--text); display: flex; align-items: center; gap: 10px;">
                <svg style="width: 28px; height: 28px; color: var(--accent);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Quản lý lái thử
            </h1>
            <p style="color: var(--muted); margin-top: 0.5rem; margin-bottom: 0;">Theo dõi và xử lý các yêu cầu trải nghiệm xe từ khách hàng.</p>
        </div>

        <form method="get" action="{{ route('admin.test_drives.index') }}" style="display: flex; align-items: center; gap: 10px; background: #0a0d12; padding: 6px; border-radius: 8px; border: 1px solid var(--border);">
            <select name="status" style="background: transparent; border: none; color: var(--text); padding: 5px 10px; outline: none; cursor: pointer;">
                <option value="" style="background: var(--bg);">Tất cả trạng thái</option>
                @foreach(['pending' => 'Chờ xử lý', 'approved' => 'Đã duyệt', 'rejected' => 'Đã huỷ', 'completed' => 'Hoàn thành'] as $k => $v)
                    <option value="{{ $k }}" style="background: var(--bg);" @selected(($status ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" style="padding: 6px 16px; border-radius: 6px; background: var(--accent); color: #000; border: none; font-weight: bold; cursor: pointer; transition: 0.2s;">
                Lọc
            </button>
        </form>
    </div>

    @if(session('success'))
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead style="background: rgba(0,0,0,0.2); color: var(--muted); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">
                    <tr>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">Mã vé</th>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">Khách hàng</th>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">Xe yêu cầu</th>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">Ngày tạo</th>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">Trạng thái</th>
                        <th style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr style="border-bottom: 1px solid var(--border); transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem;">
                                <span style="background: #0a0d12; color: var(--accent); padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border); font-family: monospace; font-weight: bold;">
                                    #{{ $b->ticket_id }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight: bold; color: var(--text);">{{ $b->user->name ?? '—' }}</div>
                                <div style="font-size: 0.8rem; color: var(--muted); margin-top: 2px;">{{ $b->user->email ?? '' }}</div>
                            </td>
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight: bold; color: var(--text);">{{ $b->car ? (($b->car->brand->name ?? '') . ' ' . $b->car->name) : '—' }}</div>
                                <div style="font-size: 0.8rem; color: var(--muted); margin-top: 2px;">{{ Str::limit($b->subject, 30) }}</div>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: var(--muted);">
                                {{ $b->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding: 1rem 1.5rem;">
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
                            <td style="padding: 1rem 1.5rem; text-align: right;">
                                <a href="{{ route('admin.test_drives.show', $b->ticket_id) }}" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border); color: var(--text); font-size: 0.8rem; transition: 0.2s; text-decoration: none;" onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)';" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text)';">
                                    Chi tiết
                                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 4rem 1rem; text-align: center; color: var(--muted);">
                                <svg style="width: 48px; height: 48px; opacity: 0.2; margin: 0 auto 10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p style="font-size: 1rem; color: var(--text); margin: 0 0 5px;">Chưa có yêu cầu lái thử nào.</p>
                                <p style="font-size: 0.85rem; margin: 0;">Khi khách hàng đặt lịch, danh sách sẽ hiển thị tại đây.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $bookings->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
