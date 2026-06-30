@extends('layouts.admin')

@section('title', 'Bao cao livestream')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush

@section('content')
<div class="live-admin-page">
    <div class="live-admin-head">
        <div>
            <h1>Bao cao livestream</h1>
            <p>Hieu qua phien live, live lead, xe duoc quan tam va chuyen doi.</p>
        </div>
        <a class="live-btn live-btn-secondary" href="{{ route('admin.live.index') }}">Quan ly live</a>
    </div>

    <form class="live-filter" method="get" action="{{ route('admin.reports.live') }}">
        <div class="live-field">
            <label for="date_from">Tu ngay</label>
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
        </div>
        <div class="live-field">
            <label for="date_to">Den ngay</label>
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
        </div>
        <div class="live-field">
            <label for="live_session_id">Phien live</label>
            <select id="live_session_id" name="live_session_id">
                <option value="">Tat ca</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" @selected((string) $filters['live_session_id'] === (string) $session->id)>{{ $session->live_code }} - {{ $session->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="live-field">
            <label for="car_id">Xe</label>
            <select id="car_id" name="car_id">
                <option value="">Tat ca</option>
                @foreach($cars as $car)
                    <option value="{{ $car->car_id }}" @selected((string) $filters['car_id'] === (string) $car->car_id)>{{ $car->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="live-field">
            <label for="assigned_to">Nhan vien</label>
            <select id="assigned_to" name="assigned_to">
                <option value="">Tat ca</option>
                @foreach($staff as $user)
                    <option value="{{ $user->user_id }}" @selected((string) $filters['assigned_to'] === (string) $user->user_id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="live-filter-actions">
            <button class="live-btn live-btn-primary" type="submit">Loc</button>
            <a class="live-btn live-btn-secondary" href="{{ route('admin.reports.live') }}">Xoa loc</a>
        </div>
    </form>

    <section class="live-stats-grid is-report">
        <div><span>Tong phien live</span><strong>{{ number_format($stats['sessions']) }}</strong></div>
        <div><span>Da live</span><strong>{{ number_format($stats['completed_sessions']) }}</strong></div>
        <div><span>Lead tu live</span><strong>{{ number_format($stats['leads']) }}</strong></div>
        <div><span>Yeu cau bao gia</span><strong>{{ number_format($stats['quote_requests']) }}</strong></div>
        <div><span>Yeu cau lai thu</span><strong>{{ number_format($stats['test_drive_requests']) }}</strong></div>
        <div><span>Quan tam dat coc</span><strong>{{ number_format($stats['deposit_interests']) }}</strong></div>
        <div><span>Bao gia tu live</span><strong>{{ number_format($stats['quotes_from_live']) }}</strong></div>
        <div><span>Don hang tu live</span><strong>{{ number_format($stats['orders_from_live']) }}</strong></div>
        <div class="is-alert"><span>Ty le chuyen doi</span><strong>{{ number_format($stats['conversion_rate'], 1) }}%</strong></div>
    </section>

    <div class="live-detail-grid">
        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Top xe duoc quan tam</h2>
            </div>
            <div class="live-list">
                @forelse($topCars as $car)
                    <div class="live-list-row">
                        <div>
                            <strong>{{ $car->brand_name }} {{ $car->model_name }} {{ $car->name }}</strong>
                            <span>{{ number_format((int) $car->leads_count) }} lead</span>
                        </div>
                    </div>
                @empty
                    <p class="live-empty">Chua co du lieu.</p>
                @endforelse
            </div>
        </section>

        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Phien live nhieu lead</h2>
            </div>
            <div class="live-list">
                @forelse($topSessions as $session)
                    <div class="live-list-row">
                        <div>
                            <strong>{{ $session->live_code }} - {{ $session->title }}</strong>
                            <span>{{ number_format((int) $session->leads_count) }} lead</span>
                        </div>
                    </div>
                @empty
                    <p class="live-empty">Chua co du lieu.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="live-panel">
        <div class="live-panel-head">
            <h2>Live lead trong bo loc</h2>
            <span>{{ number_format($leads->total()) }} lead</span>
        </div>
        <div class="live-table-wrap is-flat">
            <table class="live-table">
                <thead>
                    <tr>
                        <th>Thoi gian</th>
                        <th>Phien live</th>
                        <th>Khach</th>
                        <th>Nhu cau</th>
                        <th>Xe</th>
                        <th>Phu trach</th>
                        <th>Trang thai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>{{ $lead->created_at?->format('d/m/Y H:i') }}</td>
                            <td><a class="live-code" href="{{ route('admin.live.show', $lead->live_session_id) }}">{{ $lead->liveSession?->live_code }}</a></td>
                            <td>
                                <div class="live-main-text">{{ $lead->customerDisplayName() }}</div>
                                <div class="live-sub-text">{{ $lead->phone ?: $lead->email ?: 'Chua co lien he' }}</div>
                            </td>
                            <td>{{ $lead->leadTypeLabel() }}</td>
                            <td>{{ $lead->car?->title ?: 'Tu van chung' }}</td>
                            <td>{{ $lead->assignedTo?->name ?: 'Chua gan' }}</td>
                            <td><span class="live-status-badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="live-empty">Chua co live lead phu hop.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="live-pagination">{{ $leads->links('pagination.lux') }}</div>
    </section>
</div>
@endsection
