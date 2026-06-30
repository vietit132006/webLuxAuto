@extends('layouts.admin')

@section('title', 'Quan ly livestream')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush

@section('content')
<div class="live-admin-page">
    <div class="live-admin-head">
        <div>
            <h1>Quan ly livestream</h1>
            <p>Phien live ban xe, xe ghim, lead va hieu qua chuyen doi.</p>
        </div>
        <div class="live-admin-actions">
            @can('live.reports.view')
                <a class="live-btn live-btn-secondary" href="{{ route('admin.reports.live') }}">Bao cao</a>
            @endcan
            @can('live.create')
                <a class="live-btn live-btn-primary" href="{{ route('admin.live.create') }}">Tao phien live</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="live-alert is-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="live-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="live-stats-grid">
        <div><span>Tong phien</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div><span>Dang live</span><strong>{{ number_format($stats['live']) }}</strong></div>
        <div><span>Da len lich</span><strong>{{ number_format($stats['scheduled']) }}</strong></div>
        <div><span>Da ket thuc</span><strong>{{ number_format($stats['ended']) }}</strong></div>
        <div class="is-alert"><span>Lead moi</span><strong>{{ number_format($stats['new_leads']) }}</strong></div>
    </section>

    <form class="live-filter" method="get" action="{{ route('admin.live.index') }}">
        <div class="live-field">
            <label for="q">Tu khoa</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Ma live, tieu de, video ID">
        </div>
        <div class="live-field">
            <label for="status">Trang thai</label>
            <select id="status" name="status">
                <option value="">Tat ca</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="live-field">
            <label for="platform">Nen tang</label>
            <select id="platform" name="platform">
                <option value="">Tat ca</option>
                @foreach($platformOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['platform'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="live-field">
            <label for="date_from">Tu ngay</label>
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
        </div>
        <div class="live-field">
            <label for="date_to">Den ngay</label>
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
        </div>
        <div class="live-filter-actions">
            <button class="live-btn live-btn-primary" type="submit">Loc</button>
            <a class="live-btn live-btn-secondary" href="{{ route('admin.live.index') }}">Xoa loc</a>
        </div>
    </form>

    <section class="live-table-wrap">
        <table class="live-table">
            <thead>
                <tr>
                    <th>Ma live</th>
                    <th>Tieu de</th>
                    <th>Nen tang</th>
                    <th>Bat dau</th>
                    <th>Ket thuc</th>
                    <th>Trang thai</th>
                    <th>Xe ghim</th>
                    <th>Luot xem</th>
                    <th>Lead</th>
                    <th>Hanh dong</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>
                            <a class="live-code" href="{{ route('admin.live.show', $session) }}">{{ $session->live_code ?: 'LIVE' . str_pad($session->id, 6, '0', STR_PAD_LEFT) }}</a>
                        </td>
                        <td>
                            <div class="live-main-text">{{ $session->title }}</div>
                            <div class="live-sub-text">{{ $session->host?->name ?: 'Chua gan host' }}</div>
                        </td>
                        <td>{{ $session->platformLabel() }}</td>
                        <td>{{ $session->starts_at?->format('d/m/Y H:i') ?: 'Chua len lich' }}</td>
                        <td>{{ $session->ends_at?->format('d/m/Y H:i') ?: 'Chua ket thuc' }}</td>
                        <td><span class="live-status-badge {{ $session->statusBadgeClass() }}">{{ $session->statusLabel() }}</span></td>
                        <td>{{ number_format((int) $session->pinned_cars_count) }}</td>
                        <td>{{ number_format((int) $session->views_count) }}</td>
                        <td>
                            <div class="live-main-text">{{ number_format((int) $session->leads_count) }}</div>
                            <div class="live-sub-text">BG {{ number_format((int) $session->quote_requests_count) }} / LT {{ number_format((int) $session->test_drive_requests_count) }}</div>
                        </td>
                        <td>
                            <div class="live-row-actions">
                                @can('live.view')
                                    <a href="{{ route('admin.live.show', $session) }}">Chi tiet</a>
                                @endcan
                                @can('live.edit')
                                    <a href="{{ route('admin.live.edit', $session) }}">Sua</a>
                                @endcan
                                @can('live.manage')
                                    @if($session->is_active)
                                        <form method="post" action="{{ route('admin.live.stop', $session) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Tat</button>
                                        </form>
                                    @else
                                        <form method="post" action="{{ route('admin.live.start', $session) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Bat</button>
                                        </form>
                                    @endif
                                    @if($session->status !== \App\Models\LiveSession::STATUS_ENDED)
                                        <form method="post" action="{{ route('admin.live.end', $session) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Ket thuc</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('live.delete')
                                    @if((int) $session->leads_count === 0)
                                        <form method="post" action="{{ route('admin.live.destroy', $session) }}" onsubmit="return confirm('Xoa phien livestream nay?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="is-danger" type="submit">Xoa</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="live-empty">Chua co phien livestream nao.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="live-pagination">{{ $sessions->links('pagination.lux') }}</div>
</div>
@endsection
