<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\LiveLead;
use App\Models\LiveSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function index(): View
    {
        $session = $this->currentSession();

        if ($session) {
            $session->increment('views_count');
            $session->views_count++;
        }

        $liveCars = $session
            ? $session->activeSessionCars
            : collect();

        return view('client.livestream', [
            'session' => $session,
            'liveCars' => $liveCars,
            'focusCars' => $liveCars->where('is_focus', true),
            'isLiveActive' => (bool) $session?->isLive(),
            'liveVideoId' => $session?->canShowPlayer() ? $session->video_id : '',
            'leadTypeOptions' => LiveLead::TYPES,
        ]);
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'live_session_id' => ['required', 'integer', Rule::exists('live_sessions', 'id')],
            'car_id' => ['nullable', 'integer', Rule::exists('cars', 'car_id')],
            'lead_type' => ['required', Rule::in(array_keys(LiveLead::TYPES))],
            'customer_name' => [Rule::requiredIf(fn (): bool => !$request->user()), 'nullable', 'string', 'max:255'],
            'phone' => [Rule::requiredIf(fn (): bool => !$request->user()), 'nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'customer_name' => 'ho ten',
            'phone' => 'so dien thoai',
            'lead_type' => 'nhu cau',
        ]);

        $session = LiveSession::query()
            ->publiclyVisible()
            ->with('activeSessionCars')
            ->findOrFail($data['live_session_id']);

        if (!$session->canShowFrontend() && !$session->isLive()) {
            throw ValidationException::withMessages([
                'live_session_id' => 'Phien live hien khong nhan lead tu frontend.',
            ]);
        }

        $car = null;

        if (!empty($data['car_id'])) {
            $sessionCarIds = $session->activeSessionCars->pluck('car_id')->map(fn ($id): int => (int) $id);

            if (!$sessionCarIds->contains((int) $data['car_id'])) {
                throw ValidationException::withMessages([
                    'car_id' => 'Xe nay khong thuoc danh sach dang ghim trong phien live.',
                ]);
            }

            $car = Car::find($data['car_id']);
        }

        if ($data['lead_type'] === LiveLead::TYPE_DEPOSIT_INTEREST && $car && !$car->isAvailableForSale()) {
            throw ValidationException::withMessages([
                'car_id' => 'Xe hien khong con ton kha dung de dat coc.',
            ]);
        }

        LiveLead::create([
            'live_session_id' => $session->id,
            'car_id' => $data['car_id'] ?? null,
            'user_id' => $request->user()?->getKey(),
            'customer_name' => $request->user()?->name ?: ($data['customer_name'] ?? null),
            'phone' => $data['phone'] ?? $request->user()?->phone,
            'email' => $data['email'] ?? $request->user()?->email,
            'lead_type' => $data['lead_type'],
            'message' => $data['message'] ?? null,
            'status' => LiveLead::STATUS_NEW,
        ]);

        return back()->with('success', 'Lux Auto da nhan yeu cau tu livestream. Nhan vien se lien he ban som.');
    }

    private function currentSession(): ?LiveSession
    {
        $relations = [
            'activeSessionCars.car.modelInfo.brand',
            'activeSessionCars.promotion',
        ];

        $live = LiveSession::query()
            ->publiclyVisible()
            ->liveNow()
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->with($relations)
            ->orderByDesc('starts_at')
            ->orderByDesc('updated_at')
            ->first();

        if ($live) {
            return $live;
        }

        $scheduled = LiveSession::query()
            ->publiclyVisible()
            ->upcoming()
            ->with($relations)
            ->orderByRaw('starts_at IS NULL')
            ->orderBy('starts_at')
            ->orderByDesc('updated_at')
            ->first();

        if ($scheduled) {
            return $scheduled;
        }

        $replay = LiveSession::query()
            ->publiclyVisible()
            ->where('status', LiveSession::STATUS_ENDED)
            ->where('replay_enabled', true)
            ->whereNotNull('video_id')
            ->where('video_id', '!=', '')
            ->with($relations)
            ->orderByDesc('ends_at')
            ->orderByDesc('updated_at')
            ->first();

        if ($replay) {
            return $replay;
        }

        return LiveSession::query()
            ->publiclyVisible()
            ->with($relations)
            ->orderByDesc('updated_at')
            ->first();
    }
}
