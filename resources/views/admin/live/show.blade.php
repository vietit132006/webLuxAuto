@extends('layouts.admin')

@section('title', 'Chi tiet livestream')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush

@section('content')
<div class="live-admin-page">
    <div class="live-admin-head">
        <div>
            <h1>{{ $session->title }}</h1>
            <p>{{ $session->live_code }} / {{ $session->platformLabel() }} / {{ $session->host?->name ?: 'Chua gan host' }}</p>
        </div>
        <div class="live-admin-actions">
            <a class="live-btn live-btn-secondary" href="{{ route('admin.live.index') }}">Danh sach</a>
            @can('live.edit')
                <a class="live-btn live-btn-secondary" href="{{ route('admin.live.edit', $session) }}">Sua</a>
            @endcan
            @can('live.manage')
                @if($session->is_active)
                    <form method="post" action="{{ route('admin.live.stop', $session) }}">
                        @csrf
                        @method('PATCH')
                        <button class="live-btn live-btn-secondary" type="submit">Tat live</button>
                    </form>
                @else
                    <form method="post" action="{{ route('admin.live.start', $session) }}">
                        @csrf
                        @method('PATCH')
                        <button class="live-btn live-btn-primary" type="submit">Bat live</button>
                    </form>
                @endif
                <form method="post" action="{{ route('admin.live.end', $session) }}">
                    @csrf
                    @method('PATCH')
                    <button class="live-btn live-btn-danger" type="submit">Ket thuc</button>
                </form>
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
        <div><span>Trang thai</span><strong class="is-text"><span class="live-status-badge {{ $session->statusBadgeClass() }}">{{ $session->statusLabel() }}</span></strong></div>
        <div><span>Luot xem</span><strong>{{ number_format((int) $session->views_count) }}</strong></div>
        <div><span>Dinh xem</span><strong>{{ number_format((int) $session->peak_viewers) }}</strong></div>
        <div><span>Tong lead</span><strong>{{ number_format($session->leads->count()) }}</strong></div>
        <div><span>Ty le chuyen doi</span><strong>{{ number_format($session->conversionRate(), 1) }}%</strong></div>
    </section>

    <div class="live-detail-grid">
        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Video preview</h2>
                <span>{{ $session->video_id ?: 'Chua co video ID' }}</span>
            </div>
            <div class="live-preview">
                @if($session->video_id)
                    <iframe src="https://www.youtube.com/embed/{{ $session->video_id }}?rel=0" title="{{ $session->title }}" allowfullscreen></iframe>
                @else
                    <div class="live-preview-empty">Chua cau hinh video.</div>
                @endif
            </div>
        </section>

        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Thong tin phien</h2>
            </div>
            <dl class="live-definition">
                <div><dt>Bat dau</dt><dd>{{ $session->starts_at?->format('d/m/Y H:i') ?: 'Chua len lich' }}</dd></div>
                <div><dt>Ket thuc</dt><dd>{{ $session->ends_at?->format('d/m/Y H:i') ?: 'Chua ket thuc' }}</dd></div>
                <div><dt>Cong khai</dt><dd>{{ $session->is_public ? 'Co' : 'Khong' }}</dd></div>
                <div><dt>Replay</dt><dd>{{ $session->replay_enabled ? 'Co' : 'Khong' }}</dd></div>
                <div><dt>CTA</dt><dd>{{ $session->cta_label ?: 'Mac dinh theo frontend' }}</dd></div>
            </dl>
            @if($session->description)
                <p class="live-description">{{ $session->description }}</p>
            @endif
        </section>
    </div>

    <section class="live-panel">
        <div class="live-panel-head">
            <h2>Xe ghim trong live</h2>
            <span>{{ $session->sessionCars->count() }} xe</span>
        </div>
        <div class="live-card-grid">
            @forelse($session->sessionCars as $sessionCar)
                @php($car = $sessionCar->car)
                <article class="live-car-card {{ $sessionCar->is_focus ? 'is-focus' : '' }}">
                    @if($car?->image)
                        <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}">
                    @else
                        <div class="live-car-card-empty">No image</div>
                    @endif
                    <div>
                        <span>{{ $car?->carModel?->brand?->name }} {{ $car?->carModel?->name }}</span>
                        <h3>{{ $car?->name ?: 'Xe da bi xoa' }}</h3>
                        <p>Ton kha dung: {{ $car ? $car->saleableStock() : 0 }}</p>
                        <strong>{{ $sessionCar->live_price ? number_format((float) $sessionCar->live_price, 0, ',', '.') . ' d' : ($car ? number_format((float) ($car->sale_price ?: $car->price), 0, ',', '.') . ' d' : 'N/A') }}</strong>
                        @if($sessionCar->promotion)
                            <em>{{ $sessionCar->promotion->promotion_code }} - {{ $sessionCar->promotion->title }}</em>
                        @endif
                    </div>
                </article>
            @empty
                <div class="live-empty">Chua ghim xe nao.</div>
            @endforelse
        </div>
    </section>

    <section class="live-panel">
        <div class="live-panel-head">
            <h2>Lead tu livestream</h2>
            <span>{{ $session->leads->count() }} lead</span>
        </div>
        <div class="live-table-wrap is-flat">
            <table class="live-table">
                <thead>
                    <tr>
                        <th>Khach</th>
                        <th>Nhu cau</th>
                        <th>Xe</th>
                        <th>Phu trach</th>
                        <th>Trang thai</th>
                        <th>Thoi gian</th>
                        <th>Hanh dong</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($session->leads->sortByDesc('created_at') as $lead)
                        <tr>
                            <td>
                                <div class="live-main-text">{{ $lead->customerDisplayName() }}</div>
                                <div class="live-sub-text">{{ $lead->phone ?: $lead->email ?: 'Chua co lien he' }}</div>
                            </td>
                            <td>{{ $lead->leadTypeLabel() }}</td>
                            <td>{{ $lead->car?->title ?: 'Tu van chung' }}</td>
                            <td>{{ $lead->assignedTo?->name ?: 'Chua gan' }}</td>
                            <td><span class="live-status-badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></td>
                            <td>{{ $lead->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="live-row-actions">
                                    @can('live.leads.view')
                                        <a href="{{ route('admin.live.leads.show', $lead) }}">Chi tiet</a>
                                    @endcan
                                    @can('live.leads.edit')
                                        @if(!$lead->quote)
                                            <form method="post" action="{{ route('admin.live.leads.quote', $lead) }}">
                                                @csrf
                                                <button type="submit">Tao BG</button>
                                            </form>
                                        @endif
                                        @if(!$lead->testDrive)
                                            <form method="post" action="{{ route('admin.live.leads.test_drive', $lead) }}">
                                                @csrf
                                                <button type="submit">Tao LT</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="live-empty">Chua phat sinh lead.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
