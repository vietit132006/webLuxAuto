@extends('layouts.site')

@section('title', 'Livestream ban xe - Lux Auto')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-livestream.css')
    @endif
@endpush

@section('content')
@php
    $stateClass = $isLiveActive ? 'is-live' : ($session?->isScheduled() ? 'is-scheduled' : ($session?->isEnded() ? 'is-ended' : 'is-offline'));
    $stateLabel = $session?->frontendStateLabel() ?: 'Chua phat song';
    $leadSessionId = $session?->id;
@endphp

<div class="live-page">
    <section class="live-hero">
        <div class="live-wrap">
            <div class="live-hero__grid">
                <div>
                    <p class="live-kicker">Phong phat song Lux Auto</p>
                    <h1 class="live-title">{{ $session?->title ?: 'Lux Auto truc tiep' }}</h1>
                    <p class="live-copy">
                        {{ $session?->description ?: 'Theo doi cac phien live gioi thieu xe, uu dai rieng trong live va gui yeu cau bao gia hoac lai thu ngay khi co mau xe phu hop.' }}
                    </p>
                </div>

                <div class="live-status {{ $stateClass }}" aria-label="Trang thai livestream">
                    <span class="live-status__dot" aria-hidden="true"></span>
                    {{ $stateLabel }}
                </div>
            </div>
        </div>
    </section>

    <main class="live-wrap live-stage">
        @if(session('success'))
            <div class="live-alert is-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="live-alert is-error">{{ $errors->first() }}</div>
        @endif

        <section class="live-player-card">
            <div class="live-player">
                @if($liveVideoId)
                    <iframe
                        src="https://www.youtube.com/embed/{{ $liveVideoId }}?autoplay={{ $isLiveActive ? '1' : '0' }}&mute={{ $isLiveActive ? '1' : '0' }}&rel=0"
                        title="{{ $session?->title ?: 'Livestream Lux Auto' }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                @else
                    <div class="live-player-empty">
                        @if($session?->isScheduled())
                            <strong>Sap phat song</strong>
                            <span data-countdown="{{ $session->starts_at?->toIso8601String() }}">
                                Bat dau luc {{ $session->starts_at?->format('H:i d/m/Y') }}
                            </span>
                        @elseif($session?->isEnded())
                            <strong>Phien live da ket thuc</strong>
                            <span>{{ $session->replay_enabled ? 'Replay chua san sang.' : 'Replay khong duoc bat cho phien nay.' }}</span>
                        @else
                            <strong>Chua phat song</strong>
                            <span>Phien live dang tam tat hoac chua co lich phat song.</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="live-player-caption">
                <div>
                    <strong>{{ $stateLabel }}</strong>
                    @if($session?->starts_at)
                        <span>{{ $session->starts_at->format('H:i d/m/Y') }}</span>
                    @endif
                </div>
                <div>{{ $liveCars->count() }} xe trong live / {{ number_format((int) ($session->views_count ?? 0)) }} luot xem</div>
            </div>
        </section>

        @if($focusCars->isNotEmpty())
            <section class="live-focus">
                <div class="live-section-head">
                    <div>
                        <p class="live-section-eyebrow">Dang focus</p>
                        <h2 class="live-section-title">Xe dang len song</h2>
                    </div>
                </div>
                <div class="live-focus-grid">
                    @foreach($focusCars as $sessionCar)
                        @include('client.partials.live-car-card', ['sessionCar' => $sessionCar, 'leadSessionId' => $leadSessionId, 'isFocus' => true])
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="live-section-head">
                <div>
                    <p class="live-section-eyebrow">Showroom trong live</p>
                    <h2 class="live-section-title">{{ $session?->isScheduled() ? 'Xe du kien len song' : 'Xe dang duoc ghim' }}</h2>
                </div>
                <div class="live-products-count">{{ $liveCars->count() }} mau xe</div>
            </div>

            <div class="live-grid">
                @forelse($liveCars as $sessionCar)
                    @include('client.partials.live-car-card', ['sessionCar' => $sessionCar, 'leadSessionId' => $leadSessionId, 'isFocus' => false])
                @empty
                    <div class="live-empty">Chua co xe nao duoc ghim trong phien live nay.</div>
                @endforelse
            </div>
        </section>

        <section class="live-lead-panel" id="live-lead-form">
            <div>
                <p class="live-section-eyebrow">Dang ky tu livestream</p>
                <h2 class="live-section-title">De lai nhu cau</h2>
            </div>

            @if($leadSessionId && $session?->canShowFrontend())
                <form class="live-lead-form" method="post" action="{{ route('livestream.leads.store') }}" data-live-lead-form>
                    @csrf
                    <input type="hidden" name="live_session_id" value="{{ $leadSessionId }}">
                    <input type="hidden" name="car_id" value="{{ old('car_id') }}" data-live-lead-car>

                    <div class="live-lead-field">
                        <label for="lead_type">Nhu cau</label>
                        <select id="lead_type" name="lead_type" data-live-lead-type required>
                            @foreach($leadTypeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('lead_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @guest
                        <div class="live-lead-field">
                            <label for="customer_name">Ho ten</label>
                            <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="live-lead-field">
                            <label for="phone">So dien thoai</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required>
                        </div>
                        <div class="live-lead-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}">
                        </div>
                    @endguest

                    <div class="live-lead-field is-wide">
                        <label for="message">Ghi chu</label>
                        <textarea id="message" name="message" rows="3" data-live-lead-message>{{ old('message') }}</textarea>
                    </div>

                    <div class="live-lead-actions">
                        <button class="live-btn live-btn-primary" type="submit">Gui yeu cau</button>
                        @if($session?->cta_label && $session?->cta_url)
                            <a class="live-btn live-btn-secondary" href="{{ $session->cta_url }}">{{ $session->cta_label }}</a>
                        @endif
                    </div>
                </form>
            @else
                <div class="live-empty is-compact">Hien chua nhan lead vi phien live khong cong khai hoac da tam tat.</div>
            @endif
        </section>
    </main>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('[data-live-lead-form]');

            document.querySelectorAll('[data-live-action]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (!form) {
                        return;
                    }

                    const carInput = form.querySelector('[data-live-lead-car]');
                    const typeInput = form.querySelector('[data-live-lead-type]');
                    const messageInput = form.querySelector('[data-live-lead-message]');

                    if (carInput) {
                        carInput.value = button.dataset.carId || '';
                    }

                    if (typeInput && button.dataset.leadType) {
                        typeInput.value = button.dataset.leadType;
                    }

                    if (messageInput && button.dataset.carName) {
                        messageInput.value = `Toi quan tam ${button.dataset.carName}`;
                    }

                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    form.querySelector('input:not([type="hidden"]), select, textarea')?.focus({ preventScroll: true });
                });
            });

            document.querySelectorAll('[data-countdown]').forEach((target) => {
                const start = new Date(target.dataset.countdown);

                if (Number.isNaN(start.getTime())) {
                    return;
                }

                const render = () => {
                    const diff = start.getTime() - Date.now();

                    if (diff <= 0) {
                        target.textContent = 'Sap bat dau';
                        return;
                    }

                    const totalMinutes = Math.floor(diff / 60000);
                    const days = Math.floor(totalMinutes / 1440);
                    const hours = Math.floor((totalMinutes % 1440) / 60);
                    const minutes = totalMinutes % 60;
                    target.textContent = `${days} ngay ${hours} gio ${minutes} phut nua`;
                };

                render();
                window.setInterval(render, 60000);
            });
        })();
    </script>
@endpush
