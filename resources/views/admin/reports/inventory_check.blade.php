@extends('layouts.admin')

@section('title', 'Kiểm tra tồn kho')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-inventory-check.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <h1 class="rep-title">Kiểm tra &amp; điều chỉnh tồn</h1>
    <p class="admin-reports-inventory-check-inline-5">Ghi nhận chênh lệch kiểm kê (ví dụ +1 hoặc -1). Tồn kho sẽ được cập nhật theo số bạn nhập.</p>

    @if(session('success'))
        <div class="flash-alert">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif

    <div class="panel">
        <h2 class="admin-reports-inventory-check-inline-4">Ghi nhận điều chỉnh</h2>
        <form method="post" action="{{ route('admin.reports.inventory_log') }}" class="form-grid">
            @csrf
            <div>
                <label for="car_id">Chọn xe</label>
                <select name="car_id" id="car_id" required>
                    <option value="">— Chọn —</option>
                    @foreach($cars as $c)
                        <option value="{{ $c->car_id }}" @selected(old('car_id') == $c->car_id)>
                            {{ $c->brand->name ?? '' }} {{ $c->name }} (tồn: {{ $c->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="change_qty">Thay đổi tồn (+/-)</label>
                <input type="number" name="change_qty" id="change_qty" value="{{ old('change_qty') }}" required placeholder="VD: -1 hoặc 2">
                <p class="hint">Số dương là nhập thêm, số âm là giảm tồn.</p>
            </div>
            <div>
                <label for="note">Ghi chú (tuỳ chọn)</label>
                <textarea name="note" id="note" rows="2" placeholder="VD: Kiểm kê định kỳ tháng 4">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="btn-save">Lưu điều chỉnh</button>
        </form>
    </div>

    <h2 class="admin-reports-inventory-check-inline-3">50 lần ghi nhận gần nhất</h2>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Xe</th>
                    <th>Thay đổi</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->car->name ?? '—' }}</td>
                        <td style="font-weight: 700; color: {{ $log->change_qty >= 0 ? '#4ade80' : '#f87171' }};">
                            {{ $log->change_qty >= 0 ? '+' : '' }}{{ $log->change_qty }}
                        </td>
                        <td class="admin-reports-inventory-check-inline-2">{{ $log->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="admin-reports-inventory-check-inline-1" colspan="4">Chưa có lịch sử điều chỉnh.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection