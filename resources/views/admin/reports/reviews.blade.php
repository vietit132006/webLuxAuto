@extends('layouts.admin')

@section('title', 'Báo cáo đánh giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-reviews.css')
    @endif
@endpush


@section('content')

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
                        <td class="admin-reports-reviews-inline-4">{{ $rev->created_at?->format('d/m/Y') }}</td>
                        <td>{{ $rev->user->name ?? '—' }}</td>
                        <td>{{ $rev->car->name ?? '—' }}</td>
                        <td><span class="stars">{{ str_repeat('★', (int) $rev->rating) }}{{ str_repeat('☆', 5 - (int) $rev->rating) }}</span></td>
                        <td class="admin-reports-reviews-inline-3">{{ $rev->comment ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td class="admin-reports-reviews-inline-2" colspan="5">Chưa có đánh giá nào trong hệ thống.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
        <div class="admin-reports-reviews-inline-1">{{ $reviews->links('pagination.lux') }}</div>
    @endif
</div>
@endsection