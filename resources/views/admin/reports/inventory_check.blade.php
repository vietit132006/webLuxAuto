@extends('layouts.admin')

@section('title', 'Kiểm tra tồn kho')

@section('content')
<style>
    .rep-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.5rem; }
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
    .form-grid { display: grid; gap: 1rem; max-width: 520px; }
    .form-grid label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
    .form-grid select, .form-grid input, .form-grid textarea {
        width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);
    }
    .btn-save { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--accent); color: #0c0f14; font-weight: 700; cursor: pointer; }
    .hint { font-size: 0.85rem; color: var(--muted); margin-top: 0.25rem; }
    .table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .admin-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .admin-table th, .admin-table td { padding: 0.65rem 0.85rem; border-bottom: 1px solid var(--border); text-align: left; }
    .flash-alert { background: #d1fae5; color: #065f46; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 600; }
    .err { background: #fee2e2; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
</style>

<div class="wrap">
    <h1 class="rep-title">Kiểm tra &amp; điều chỉnh tồn</h1>
    <p style="color: var(--muted); margin-bottom: 1.5rem;">Ghi nhận chênh lệch kiểm kê (ví dụ +1 hoặc -1). Tồn kho sẽ được cập nhật theo số bạn nhập.</p>

    @if(session('success'))
        <div class="flash-alert">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif

    <div class="panel">
        <h2 style="margin-top: 0; font-size: 1.1rem;">Ghi nhận điều chỉnh</h2>
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

    <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">50 lần ghi nhận gần nhất</h2>
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
                        <td style="color: var(--muted);">{{ $log->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: var(--muted); padding: 1.5rem;">Chưa có lịch sử điều chỉnh.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
