<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class StockMovementService
{
    public function recordMovement(
        Car $car,
        int $before,
        int $change,
        int $after,
        string $actionType,
        string $reason,
        ?string $note = null,
        ?User $user = null,
        ?Request $request = null,
        ?int $reservedBefore = null,
        ?int $reservedChange = null,
        ?int $reservedAfter = null
    ): ?StockMovement {
        if ($change === 0 && ($reservedChange === null || $reservedChange === 0)) {
            return null;
        }

        if (!in_array($actionType, StockMovement::ACTION_TYPES, true)) {
            throw new InvalidArgumentException("Unsupported stock action type [{$actionType}].");
        }

        return StockMovement::create([
            'car_id' => $car->getAttribute('car_id'),
            'user_id' => $user?->getKey() ?? Auth::id(),
            'action_type' => $actionType,
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after' => $after,
            'reserved_before' => $reservedBefore,
            'reserved_change' => $reservedChange,
            'reserved_after' => $reservedAfter,
            'reason' => $reason,
            'note' => $note,
            'ip_address' => $request?->ip() ?? request()?->ip(),
        ]);
    }

    public function changeStock(
        Car $car,
        int $change,
        string $actionType,
        string $reason,
        ?string $note = null,
        ?Request $request = null
    ): Car {
        $lockedCar = Car::query()
            ->lockForUpdate()
            ->findOrFail($car->getAttribute('car_id'));

        $before = $this->currentQuantity($lockedCar);
        $after = $before + $change;

        if ($after < 0) {
            throw new InvalidArgumentException('Tồn kho sau thay đổi không được âm.');
        }

        $lockedCar->update([
            'stock' => $after,
            'stock_quantity' => $after,
        ]);

        $this->recordMovement(
            $lockedCar,
            $before,
            $change,
            $after,
            $actionType,
            $reason,
            $note,
            null,
            $request
        );

        return $lockedCar;
    }

    public function recordOrderStatusChange(
        Order $order,
        mixed $statusBefore,
        mixed $statusAfter,
        ?Request $request = null
    ): void {
        $wasStockHeld = $this->statusHoldsStock($statusBefore);
        $willStockBeHeld = $this->statusHoldsStock($statusAfter);

        if ($wasStockHeld === $willStockBeHeld) {
            return;
        }

        $order->loadMissing(['details.car', 'user']);

        foreach ($order->details as $detail) {
            if (!$detail->car) {
                continue;
            }

            $quantity = max(1, (int) $detail->quantity);
            $change = $willStockBeHeld ? -$quantity : $quantity;
            $actionType = $this->orderActionType($statusAfter, $willStockBeHeld);
            $reason = $this->orderReason($order, $statusAfter);

            $this->changeStock(
                $detail->car,
                $change,
                $actionType,
                $reason,
                null,
                $request
            );
        }
    }

    private function currentQuantity(Car $car): int
    {
        return (int) ($car->stock_quantity ?? $car->stock ?? 0);
    }

    private function statusHoldsStock(mixed $status): bool
    {
        return in_array((string) $status, ['1', '2'], true);
    }

    private function orderActionType(mixed $statusAfter, bool $willStockBeHeld): string
    {
        if (!$willStockBeHeld) {
            return (string) $statusAfter === '3'
                ? StockMovement::ACTION_CANCEL_ORDER
                : StockMovement::ACTION_RETURN;
        }

        return (string) $statusAfter === '1'
            ? StockMovement::ACTION_RESERVED
            : StockMovement::ACTION_SALE;
    }

    private function orderReason(Order $order, mixed $statusAfter): string
    {
        $customerName = $order->user?->name ?? 'khach hang';
        $orderId = $order->getAttribute('order_id');

        return match ((string) $statusAfter) {
            '1' => "Giữ xe cho đơn hàng #{$orderId} của {$customerName}.",
            '2' => "Bán xe theo đơn hàng #{$orderId} của {$customerName}.",
            '3' => "Hủy đơn hàng #{$orderId}, hoàn lại tồn kho.",
            default => "Cập nhật trạng thái đơn hàng #{$orderId}, đồng bộ tồn kho.",
        };
    }
}
