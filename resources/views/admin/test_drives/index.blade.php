@extends('layouts.admin')

@section('title', 'Đặt lịch lái thử')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Quản lý đặt lịch lái thử</h1>
            <p class="text-sm text-slate-400 mt-1">Danh sách yêu cầu lái thử từ khách hàng (ticket_type = test_drive).</p>
        </div>

        <form method="get" action="{{ route('admin.test_drives.index') }}" class="flex items-center gap-2">
            <label class="text-sm text-slate-300">Trạng thái</label>
            <select name="status" class="bg-slate-950/40 border border-slate-700 text-slate-100 rounded-lg px-3 py-2 text-sm">
                <option value="">Tất cả</option>
                @foreach(['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected', 'completed' => 'completed'] as $k => $v)
                    <option value="{{ $k }}" @selected(($status ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-amber-400 text-black font-semibold text-sm">Lọc</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/30">
        <table class="min-w-full text-sm">
            <thead class="text-slate-400">
                <tr class="border-b border-slate-800">
                    <th class="text-left px-4 py-3 font-semibold">Mã</th>
                    <th class="text-left px-4 py-3 font-semibold">Khách hàng</th>
                    <th class="text-left px-4 py-3 font-semibold">Xe</th>
                    <th class="text-left px-4 py-3 font-semibold">Ngày tạo</th>
                    <th class="text-left px-4 py-3 font-semibold">Trạng thái</th>
                    <th class="text-right px-4 py-3 font-semibold">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-slate-100">
                @forelse($bookings as $b)
                    <tr class="border-b border-slate-900/60 hover:bg-white/5">
                        <td class="px-4 py-3 font-semibold text-amber-300">#{{ $b->ticket_id }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $b->user->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $b->user->email ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">
                                {{ $b->car ? (($b->car->brand->name ?? '') . ' ' . $b->car->name) : '—' }}
                            </div>
                            <div class="text-xs text-slate-400">{{ $b->subject }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $b->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($b->status) {
                                    'pending' => 'bg-yellow-500/15 text-yellow-200 border-yellow-500/30',
                                    'approved' => 'bg-emerald-500/15 text-emerald-200 border-emerald-500/30',
                                    'rejected' => 'bg-red-500/15 text-red-200 border-red-500/30',
                                    'completed' => 'bg-sky-500/15 text-sky-200 border-sky-500/30',
                                    default => 'bg-slate-500/15 text-slate-200 border-slate-500/30',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.test_drives.show', $b->ticket_id) }}"
                               class="inline-flex items-center justify-center rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-100 hover:bg-white/5">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">Chưa có yêu cầu lái thử.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div class="mt-5 flex justify-center">
            {{ $bookings->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection

