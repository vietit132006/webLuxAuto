@extends('layouts.admin')

@section('title', 'Báo cáo đánh giá')

@section('content')
<style>
    .rep-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1rem; }
    .stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; }
    .stat-box .lbl { color: var(--muted); font-size: 0.8rem; }
    .stat-box .val { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
    .stars { letter-spacing: 2px; color: #fbbf24; }
    .table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th, .admin-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
    .admin-table th { color: var(--muted); font-size: 0.75rem; text-transform: uppercase; }
    .dist { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .dist span { background: rgba(255,255,255,0.04); padding: 0.35rem 0.65rem; border-radius: 6px; font-size: 0.85rem; }
</style>

<div class="wrap">
    <h1 class="rep-title">Báo cáo đánh giá</h1>

    <div class="stat-row">
        <div class="stat-box">
            <div class="lbl">Điểm trung bình</div>
            <div class="val">{{ $totalReviews ? number_format((float) $avgRating, 1) : '—' }} / 5</div>
        </div>
        <div class="stat-box">
            <div class="lbl">Tổng số đánh giá</div>
            <div class="val">{{ number_format($totalReviews) }}</div>
        </div>
    </div>

    @if($distribution->isNotEmpty())
        <div class="dist">
            @foreach([5,4,3,2,1] as $r)
                <span>{{ $r }}★: {{ $distribution[$r] ?? 0 }}</span>
            @endforeach
        </div>
    @endif

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Khách</th>
                    <th>Xe</th>
                    <th>Điểm</th>
                    <th>Nội dung</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $rev)
                    <tr>
                        <td style="white-space: nowrap;">{{ $rev->created_at?->format('d/m/Y') }}</td>
                        <td>{{ $rev->user->name ?? '—' }}</td>
                        <td>{{ $rev->car->name ?? '—' }}</td>
                        <td><span class="stars">{{ str_repeat('★', (int) $rev->rating) }}{{ str_repeat('☆', 5 - (int) $rev->rating) }}</span></td>
                        <td style="color: var(--muted); max-width: 320px;">{{ $rev->comment ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">Chưa có đánh giá nào trong hệ thống.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">{{ $reviews->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
