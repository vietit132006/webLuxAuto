@extends('layouts.admin')

@section('title', 'Chi tiet live lead')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush

@section('content')
<div class="live-admin-page">
    <div class="live-admin-head">
        <div>
            <h1>Live lead #{{ $lead->id }}</h1>
            <p>{{ $lead->liveSession?->live_code }} / {{ $lead->leadTypeLabel() }}</p>
        </div>
        <div class="live-admin-actions">
            <a class="live-btn live-btn-secondary" href="{{ route('admin.live.show', $lead->live_session_id) }}">Ve phien live</a>
            @can('live.leads.edit')
                @if(!$lead->quote)
                    <form method="post" action="{{ route('admin.live.leads.quote', $lead) }}">
                        @csrf
                        <button class="live-btn live-btn-primary" type="submit">Tao bao gia</button>
                    </form>
                @else
                    <a class="live-btn live-btn-secondary" href="{{ route('admin.quotes.show', $lead->quote) }}">Xem bao gia</a>
                @endif
                @if(!$lead->testDrive)
                    <form method="post" action="{{ route('admin.live.leads.test_drive', $lead) }}">
                        @csrf
                        <button class="live-btn live-btn-secondary" type="submit">Tao lai thu</button>
                    </form>
                @else
                    <a class="live-btn live-btn-secondary" href="{{ route('admin.test_drives.show', $lead->testDrive->ticket_id) }}">Xem lai thu</a>
                @endif
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="live-alert is-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="live-alert is-error">{{ $errors->first() }}</div>
    @endif

    <div class="live-detail-grid">
        <section class="live-panel">
            <div class="live-panel-head">
                <h2>Thong tin lead</h2>
                <span class="live-status-badge {{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span>
            </div>
            <dl class="live-definition">
                <div><dt>Khach</dt><dd>{{ $lead->customerDisplayName() }}</dd></div>
                <div><dt>SDT</dt><dd>{{ $lead->phone ?: 'Chua co' }}</dd></div>
                <div><dt>Email</dt><dd>{{ $lead->email ?: 'Chua co' }}</dd></div>
                <div><dt>Xe</dt><dd>{{ $lead->car?->title ?: 'Tu van chung' }}</dd></div>
                <div><dt>Thoi gian</dt><dd>{{ $lead->created_at?->format('d/m/Y H:i') }}</dd></div>
            </dl>
            @if($lead->message)
                <p class="live-description">{{ $lead->message }}</p>
            @endif
        </section>

        @can('live.leads.edit')
            <section class="live-panel">
                <div class="live-panel-head">
                    <h2>Cap nhat xu ly</h2>
                </div>
                <form class="live-form-grid" method="post" action="{{ route('admin.live.leads.update', $lead) }}">
                    @csrf
                    @method('PATCH')
                    <div class="live-field">
                        <label for="customer_name">Ten khach</label>
                        <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $lead->customer_name) }}">
                    </div>
                    <div class="live-field">
                        <label for="phone">SDT</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $lead->phone) }}">
                    </div>
                    <div class="live-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $lead->email) }}">
                    </div>
                    <div class="live-field">
                        <label for="assigned_to">Phu trach</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">Chua gan</option>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}" @selected((string) old('assigned_to', $lead->assigned_to) === (string) $user->user_id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="live-field">
                        <label for="status">Trang thai</label>
                        <select id="status" name="status">
                            @foreach($leadStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $lead->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="live-field is-wide">
                        <label for="message">Ghi chu</label>
                        <textarea id="message" name="message" rows="4">{{ old('message', $lead->message) }}</textarea>
                    </div>
                    <div class="live-form-actions is-inline">
                        <button class="live-btn live-btn-primary" type="submit">Luu lead</button>
                    </div>
                </form>
            </section>
        @endcan
    </div>
</div>
@endsection
