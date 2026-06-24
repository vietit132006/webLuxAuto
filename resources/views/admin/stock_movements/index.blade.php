@extends('layouts.admin')

@section('title', 'Lịch sử tồn kho')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-stock-movements.css')
    @endif
@endpush

@section('content')
    <div class="stock-history-page">
        <div class="stock-history-head">
            <div>
                <h1 class="stock-history-title">Lịch sử tồn kho</h1>
                <p class="stock-history-subtitle">Theo dõi mọi biến động số lượng xe trong kho.</p>
            </div>

            <a class="stock-history-export" href="{{ route('admin.stock-movements.export', request()->query()) }}">
                Export Excel
            </a>
        </div>

        <form class="stock-history-filter" method="get" action="{{ route('admin.stock-movements.index') }}">
            <div class="filter-field filter-field-wide">
                <label for="q">Tìm kiếm</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Xe, VIN, lý do, người thực hiện">
            </div>

            <div class="filter-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id">
                    <option value="">Tất cả xe</option>
                    @foreach ($cars as $car)
                        <option value="{{ $car->car_id }}" @selected((int) $filters['car_id'] === (int) $car->car_id)>
                            {{ $car->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="user_id">Người thực hiện</label>
                <select id="user_id" name="user_id">
                    <option value="">Tất cả người dùng</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->user_id }}" @selected((int) $filters['user_id'] === (int) $user->user_id)>
                            {{ $user->name }}{{ $user->role ? ' - ' . $user->role : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="action_type">Loại thao tác</label>
                <select id="action_type" name="action_type">
                    <option value="">Tất cả thao tác</option>
                    @foreach ($actionTypes as $actionType)
                        <option value="{{ $actionType }}" @selected($filters['action_type'] === $actionType)>
                            {{ $actionLabels[$actionType] ?? $actionType }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="date_from">Từ ngày</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
            </div>

            <div class="filter-field">
                <label for="date_to">Đến ngày</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
            </div>

            <div class="filter-actions">
                <button type="submit">Lọc</button>
                <a href="{{ route('admin.stock-movements.index') }}">Xóa lọc</a>
            </div>
        </form>

        <div class="stock-history-table-wrap">
            <table class="stock-history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Xe</th>
                        <th>Loại thao tác</th>
                        <th>Trước</th>
                        <th>Thay đổi</th>
                        <th>Sau</th>
                        <th>Người thực hiện</th>
                        <th>Lý do</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        @php
                            $changeClass = $movement->quantity_change >= 0 ? 'is-positive' : 'is-negative';
                            $badgeClass = \App\Models\StockMovement::badgeClassFor($movement->action_type);
                        @endphp
                        <tr>
                            <td class="mono">#{{ $movement->id }}</td>
                            <td>
                                <div class="stock-car-name">{{ $movement->car->name ?? 'Xe đã xóa' }}</div>
                                @if ($movement->car?->vin || $movement->car?->license_plate)
                                    <div class="stock-car-meta">
                                        {{ $movement->car->vin ?: $movement->car->license_plate }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="stock-badge {{ $badgeClass }}">
                                    {{ $actionLabels[$movement->action_type] ?? $movement->action_type }}
                                </span>
                            </td>
                            <td class="stock-number">{{ $movement->quantity_before }}</td>
                            <td class="stock-number {{ $changeClass }}">
                                {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                            </td>
                            <td class="stock-number">{{ $movement->quantity_after }}</td>
                            <td>
                                <div class="stock-user-name">{{ $movement->user->name ?? 'Hệ thống' }}</div>
                                @if ($movement->user?->email)
                                    <div class="stock-user-meta">{{ $movement->user->email }}</div>
                                @endif
                            </td>
                            <td class="stock-reason">
                                {{ $movement->reason }}
                                @if ($movement->note)
                                    <div class="stock-note">{{ $movement->note }}</div>
                                @endif
                            </td>
                            <td class="stock-time">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="stock-empty">Chưa có lịch sử tồn kho.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="stock-history-pagination">
                {{ $movements->links('pagination.lux') }}
            </div>
        @endif
    </div>
@endsection
