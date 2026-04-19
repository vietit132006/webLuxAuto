@extends('layouts.admin')

@section('title', 'Chi tiết lịch lái thử')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.test_drives.index') }}" class="text-sm text-amber-300 hover:underline">← Quay lại danh sách</a>
            <h1 class="text-2xl font-bold text-white mt-2">Lịch lái thử #{{ $booking->ticket_id }}</h1>
        </div>
        @php
            $badge = match($booking->status) {
                'pending' => 'bg-yellow-500/15 text-yellow-200 border-yellow-500/30',
                'approved' => 'bg-emerald-500/15 text-emerald-200 border-emerald-500/30',
                'rejected' => 'bg-red-500/15 text-red-200 border-red-500/30',
                'completed' => 'bg-sky-500/15 text-sky-200 border-sky-500/30',
                default => 'bg-slate-500/15 text-slate-200 border-slate-500/30',
            };
        @endphp
        <span class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold {{ $badge }}">
            {{ $booking->status }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-emerald-200 text-sm">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-400/30 bg-red-500/10 px-4 py-3 text-red-200 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-950/30 p-5">
            <h2 class="text-lg font-bold text-white mb-4">Thông tin yêu cầu</h2>

            <div class="space-y-4">
                <div>
                    <div class="text-xs text-slate-400">Khách hàng</div>
                    <div class="text-slate-100 font-semibold">{{ $booking->user->name ?? '—' }}</div>
                    <div class="text-sm text-slate-400">{{ $booking->user->email ?? '' }}</div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">Xe</div>
                    <div class="text-slate-100 font-semibold">
                        {{ $booking->car ? (($booking->car->brand->name ?? '') . ' ' . $booking->car->name) : '—' }}
                    </div>
                    @if($booking->car)
                        <div class="text-sm text-slate-400">Đời {{ $booking->car->year ?? '—' }} • Tồn {{ $booking->car->stock ?? 0 }}</div>
                    @endif
                </div>

                <div>
                    <div class="text-xs text-slate-400">Tiêu đề</div>
                    <div class="text-slate-100 font-semibold">{{ $booking->subject }}</div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">Nội dung</div>
                    <div class="text-slate-100 whitespace-pre-wrap leading-relaxed">{{ $booking->message }}</div>
                </div>

                <div class="text-sm text-slate-400">
                    Tạo lúc: <span class="text-slate-200">{{ $booking->created_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-950/30 p-5 h-fit">
            <h2 class="text-lg font-bold text-white mb-4">Cập nhật trạng thái</h2>

            @if($booking->status === 'completed')
                <div class="rounded-xl border border-slate-700 bg-slate-900/40 p-4 text-slate-300 text-sm">
                    Lịch lái thử đã hoàn tất. Không thể cập nhật thêm.
                </div>
            @else
                <form method="post" action="{{ route('admin.test_drives.updateStatus', $booking->ticket_id) }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm text-slate-300 font-semibold">Trạng thái</label>
                    <select name="status" class="w-full bg-slate-950/40 border border-slate-700 text-slate-100 rounded-lg px-3 py-2 text-sm">
                        @foreach(['pending','approved','rejected','completed'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $booking->status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <button class="w-full px-4 py-2 rounded-lg bg-amber-400 text-black font-semibold text-sm">
                        Lưu trạng thái
                    </button>
                    <p class="text-xs text-slate-400">
                        Quy tắc chuyển trạng thái: pending → approved/rejected; approved → completed/rejected.
                    </p>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

