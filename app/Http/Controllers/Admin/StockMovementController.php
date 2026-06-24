<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockMovementsExport;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $movements = $this->baseQuery($filters)
            ->paginate(20)
            ->withQueryString();

        $cars = Car::query()
            ->select(['car_id', 'name', 'vin', 'license_plate', 'internal_code'])
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->select(['user_id', 'name', 'email', 'role'])
            ->orderBy('name')
            ->get();

        $actionTypes = StockMovement::ACTION_TYPES;
        $actionLabels = StockMovement::ACTION_LABELS;

        return view('admin.stock_movements.index', compact(
            'movements',
            'cars',
            'users',
            'actionTypes',
            'actionLabels',
            'filters'
        ));
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new StockMovementsExport($this->filters($request)),
            'luxauto-lich-su-ton-kho-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function baseQuery(array $filters): Builder
    {
        return StockMovement::query()
            ->with(['car.carModel.brand', 'user'])
            ->when($filters['car_id'], fn (Builder $query, $carId) => $query->where('car_id', $carId))
            ->when($filters['user_id'], fn (Builder $query, $userId) => $query->where('user_id', $userId))
            ->when($filters['action_type'], fn (Builder $query, $actionType) => $query->where('action_type', $actionType))
            ->when($filters['date_from'], function (Builder $query, $date) {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'], function (Builder $query, $date) {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->when($filters['q'], function (Builder $query, string $search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('reason', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhere('action_type', 'like', "%{$search}%")
                        ->orWhereHas('car', function (Builder $carQuery) use ($search) {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%")
                                ->orWhere('internal_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function filters(Request $request): array
    {
        $actionType = $request->input('action_type');

        if (!in_array($actionType, StockMovement::ACTION_TYPES, true)) {
            $actionType = null;
        }

        return [
            'q' => trim((string) $request->input('q', '')),
            'car_id' => $request->integer('car_id') ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'action_type' => $actionType,
            'date_from' => $this->validDate($request->input('date_from')),
            'date_to' => $this->validDate($request->input('date_to')),
        ];
    }

    private function validDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
