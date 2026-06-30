@extends('layouts.admin')

@section('title', $session->exists ? 'Sua phien livestream' : 'Tao phien livestream')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $session->exists;
    $action = $isEdit ? route('admin.live.update', $session) : route('admin.live.store');
    $videoValue = old('video_input', $session->video_url ?: $session->video_id);
@endphp

<div class="live-admin-page">
    <div class="live-admin-head">
        <div>
            <h1>{{ $isEdit ? 'Sua phien livestream' : 'Tao phien livestream' }}</h1>
            <p>Thiet lap video, lich phat, hien thi va xe ghim trong live.</p>
        </div>
        <a class="live-btn live-btn-secondary" href="{{ $isEdit ? route('admin.live.show', $session) : route('admin.live.index') }}">Quay lai</a>
    </div>

    @if($errors->any())
        <div class="live-alert is-error">{{ $errors->first() }}</div>
    @endif

    <form class="live-form" method="post" action="{{ $action }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="live-form-layout">
            <section class="live-panel">
                <div class="live-panel-head">
                    <h2>Thong tin live</h2>
                    <span>{{ $session->live_code ?: 'Ma live tu sinh' }}</span>
                </div>

                <div class="live-form-grid">
                    <div class="live-field is-wide">
                        <label for="title">Tieu de</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $session->title) }}" required>
                    </div>
                    <div class="live-field">
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $session->slug) }}" placeholder="Tu sinh neu bo trong">
                    </div>
                    <div class="live-field">
                        <label for="platform">Nen tang</label>
                        <select id="platform" name="platform">
                            @foreach($platformOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('platform', $session->platform ?: 'youtube') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="live-field is-wide">
                        <label for="video_input">Link YouTube hoac Video ID</label>
                        <input id="video_input" name="video_input" type="text" value="{{ $videoValue }}" placeholder="https://youtube.com/watch?v=xxxxxxxxxxx">
                    </div>
                    <div class="live-field is-wide">
                        <label for="thumbnail">Thumbnail</label>
                        <input id="thumbnail" name="thumbnail" type="text" value="{{ old('thumbnail', $session->thumbnail) }}" placeholder="URL hoac duong dan anh">
                    </div>
                    <div class="live-field">
                        <label for="host_user_id">Nguoi dan live</label>
                        <select id="host_user_id" name="host_user_id">
                            <option value="">Chua gan</option>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}" @selected((string) old('host_user_id', $session->host_user_id) === (string) $user->user_id)>
                                    {{ $user->name }}{{ $user->email ? ' - ' . $user->email : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="live-field">
                        <label for="status">Trang thai</label>
                        <select id="status" name="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $session->status ?: \App\Models\LiveSession::STATUS_DRAFT) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="live-field is-wide">
                        <label for="description">Mo ta</label>
                        <textarea id="description" name="description" rows="4">{{ old('description', $session->description) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="live-panel">
                <div class="live-panel-head">
                    <h2>Thoi gian va hien thi</h2>
                </div>

                <div class="live-form-grid">
                    <div class="live-field">
                        <label for="starts_at">Bat dau</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $session->starts_at?->format('Y-m-d\\TH:i')) }}">
                    </div>
                    <div class="live-field">
                        <label for="ends_at">Ket thuc</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $session->ends_at?->format('Y-m-d\\TH:i')) }}">
                    </div>
                    <label class="live-toggle">
                        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $session->is_public ?? true))>
                        <span>Cong khai tren frontend</span>
                    </label>
                    <label class="live-toggle">
                        <input type="checkbox" name="replay_enabled" value="1" @checked(old('replay_enabled', $session->replay_enabled))>
                        <span>Cho xem lai replay</span>
                    </label>
                    <label class="live-toggle is-danger">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $session->is_active))>
                        <span>Bat live ngay</span>
                    </label>
                    <div class="live-field">
                        <label for="cta_label">CTA label</label>
                        <input id="cta_label" name="cta_label" type="text" value="{{ old('cta_label', $session->cta_label) }}" placeholder="Nhan bao gia">
                    </div>
                    <div class="live-field is-wide">
                        <label for="cta_url">CTA URL</label>
                        <input id="cta_url" name="cta_url" type="url" value="{{ old('cta_url', $session->cta_url) }}" placeholder="https://...">
                    </div>
                </div>
            </section>
        </div>

        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Xe trong live</h2>
                <span>{{ $cars->count() }} xe co the chon</span>
            </div>

            <div class="live-car-picker">
                <table class="live-table">
                    <thead>
                        <tr>
                            <th>Chon</th>
                            <th>Xe</th>
                            <th>Thu tu</th>
                            <th>Gia live</th>
                            <th>Khuyen mai</th>
                            <th>Focus</th>
                            <th>Hien thi</th>
                            <th>Ghi chu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cars as $car)
                            @php
                                $row = $selectedCars->get($car->car_id);
                                $prefix = 'cars.' . $car->car_id . '.';
                                $enabled = old($prefix . 'enabled', $row ? '1' : null);
                                $displayOrder = old($prefix . 'display_order', $row?->display_order ?? 0);
                                $livePrice = old($prefix . 'live_price', $row?->live_price);
                                $promotionId = old($prefix . 'promotion_id', $row?->promotion_id);
                                $isFocus = old($prefix . 'is_focus', $row?->is_focus);
                                $isActive = old($prefix . 'is_active', $row ? $row->is_active : true);
                                $liveNote = old($prefix . 'live_note', $row?->live_note);
                            @endphp
                            <tr>
                                <td>
                                    <input type="hidden" name="cars[{{ $car->car_id }}][car_id]" value="{{ $car->car_id }}">
                                    <input class="live-check" type="checkbox" name="cars[{{ $car->car_id }}][enabled]" value="1" @checked($enabled)>
                                </td>
                                <td>
                                    <div class="live-car-cell">
                                        @if($car->image)
                                            <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}">
                                        @else
                                            <div class="live-car-empty">No image</div>
                                        @endif
                                        <div>
                                            <div class="live-main-text">{{ $car->title }}</div>
                                            <div class="live-sub-text">Ton kha dung: {{ $car->saleableStock() }} / Gia: {{ number_format((float) ($car->sale_price ?: $car->price), 0, ',', '.') }} d</div>
                                        </div>
                                    </div>
                                </td>
                                <td><input class="live-small-input" type="number" min="0" name="cars[{{ $car->car_id }}][display_order]" value="{{ $displayOrder }}"></td>
                                <td><input class="live-money-input" type="number" min="0" step="1" name="cars[{{ $car->car_id }}][live_price]" value="{{ $livePrice }}"></td>
                                <td>
                                    <select name="cars[{{ $car->car_id }}][promotion_id]">
                                        <option value="">Khong ap dung</option>
                                        @foreach($promotions as $promotion)
                                            <option value="{{ $promotion->id }}" @selected((string) $promotionId === (string) $promotion->id)>
                                                {{ $promotion->promotion_code }} - {{ $promotion->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input class="live-check" type="checkbox" name="cars[{{ $car->car_id }}][is_focus]" value="1" @checked($isFocus)></td>
                                <td><input class="live-check" type="checkbox" name="cars[{{ $car->car_id }}][is_active]" value="1" @checked($isActive)></td>
                                <td><input type="text" name="cars[{{ $car->car_id }}][live_note]" value="{{ $liveNote }}" placeholder="Uu dai rieng trong live"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="live-form-actions">
            <button class="live-btn live-btn-primary" type="submit">Luu phien live</button>
            <a class="live-btn live-btn-secondary" href="{{ $isEdit ? route('admin.live.show', $session) : route('admin.live.index') }}">Huy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const input = document.getElementById('video_input');

            if (!input) {
                return;
            }

            input.addEventListener('input', () => {
                const value = input.value.trim();
                const match = value.match(/^.*(youtu\.be\/|embed\/|watch\?v=|&v=|studio\.youtube\.com\/video\/|shorts\/)([^#&?\/]{11}).*$/);

                if (match && match[2]) {
                    input.value = match[2];
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                }
            });
        })();
    </script>
@endpush
