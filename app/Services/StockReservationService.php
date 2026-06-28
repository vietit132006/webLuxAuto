<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockReservationService
{
    public function __construct(private readonly StockMovementService $stockMovementService)
    {
    }

    public function reserveForOrder(Order $order, ?User $user = null): void
    {
        DB::transaction(function () use ($order, $user): void {
            $lockedOrder = Order::query()
                ->with(['details.car', 'user'])
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            foreach ($lockedOrder->details as $detail) {
                if (!$detail->car_id) {
                    continue;
                }

                $existingReservation = StockReservation::query()
                    ->where('order_id', $lockedOrder->order_id)
                    ->where('car_id', $detail->car_id)
                    ->where('status', StockReservation::STATUS_ACTIVE)
                    ->lockForUpdate()
                    ->first();

                if ($existingReservation) {
                    continue;
                }

                $quantity = max(1, (int) $detail->quantity);
                $car = $this->lockCar((int) $detail->car_id);
                $physicalBefore = $car->physicalStock();
                $reservedBefore = $car->reservedStock();
                $reservedAfter = $reservedBefore + $quantity;

                if (!$car->isAvailableForSale()) {
                    throw new InvalidArgumentException('Xe không còn khả dụng để giữ chỗ.');
                }

                if ($physicalBefore - $reservedBefore < $quantity) {
                    throw new InvalidArgumentException('Xe không còn tồn khả dụng để giữ chỗ.');
                }

                StockReservation::create([
                    'car_id' => $car->car_id,
                    'order_id' => $lockedOrder->order_id,
                    'quote_id' => $lockedOrder->quote_id,
                    'user_id' => $lockedOrder->user_id,
                    'quantity' => $quantity,
                    'status' => StockReservation::STATUS_ACTIVE,
                    'reserved_by' => $user?->getKey(),
                    'reserved_at' => now(),
                ]);

                $car->forceFill([
                    'reserved_quantity' => $reservedAfter,
                ])->save();

                $this->stockMovementService->recordMovement(
                    $car,
                    $physicalBefore,
                    0,
                    $physicalBefore,
                    StockMovement::ACTION_RESERVED,
                    'Giữ xe cho đơn hàng ' . $lockedOrder->display_code . '.',
                    null,
                    $user,
                    null,
                    $reservedBefore,
                    $quantity,
                    $reservedAfter
                );
            }
        });
    }

    public function releaseForOrder(Order $order, string $reason, ?User $user = null): void
    {
        DB::transaction(function () use ($order, $reason, $user): void {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            $reservations = StockReservation::query()
                ->where('order_id', $lockedOrder->order_id)
                ->where('status', StockReservation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                return;
            }

            $reservationStatus = Order::normalizeStatus($lockedOrder->status) === Order::STATUS_CANCELLED
                ? StockReservation::STATUS_CANCELLED
                : StockReservation::STATUS_RELEASED;

            foreach ($reservations as $reservation) {
                $car = $this->lockCar((int) $reservation->car_id);
                $physicalBefore = $car->physicalStock();
                $reservedBefore = $car->reservedStock();
                $quantity = max(1, (int) $reservation->quantity);
                $releasedQuantity = min($quantity, $reservedBefore);
                $reservedAfter = max(0, $reservedBefore - $releasedQuantity);

                $car->forceFill([
                    'reserved_quantity' => $reservedAfter,
                ])->save();

                $reservation->forceFill([
                    'status' => $reservationStatus,
                    'released_at' => now(),
                    'release_reason' => $reason,
                ])->save();

                $this->stockMovementService->recordMovement(
                    $car,
                    $physicalBefore,
                    0,
                    $physicalBefore,
                    StockMovement::ACTION_RELEASE_RESERVATION,
                    $reason,
                    'Nhả giữ chỗ cho đơn hàng ' . $lockedOrder->display_code . '.',
                    $user,
                    null,
                    $reservedBefore,
                    -$releasedQuantity,
                    $reservedAfter
                );
            }
        });
    }

    public function completeForOrder(Order $order, ?User $user = null): void
    {
        DB::transaction(function () use ($order, $user): void {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            $reservations = StockReservation::query()
                ->where('order_id', $lockedOrder->order_id)
                ->where('status', StockReservation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                throw new InvalidArgumentException('Đơn hàng chưa giữ xe, không thể giao xe.');
            }

            foreach ($reservations as $reservation) {
                $car = $this->lockCar((int) $reservation->car_id);
                $quantity = max(1, (int) $reservation->quantity);
                $physicalBefore = $car->physicalStock();
                $reservedBefore = $car->reservedStock();
                $completedReservedQuantity = min($quantity, $reservedBefore);

                if ($physicalBefore < $quantity) {
                    throw new InvalidArgumentException('Tồn kho sau thay đổi không được âm.');
                }

                $physicalAfter = $physicalBefore - $quantity;
                $reservedAfter = max(0, $reservedBefore - $completedReservedQuantity);

                $updates = [
                    'stock' => $physicalAfter,
                    'stock_quantity' => $physicalAfter,
                    'reserved_quantity' => $reservedAfter,
                ];

                if ($physicalAfter === 0) {
                    $updates['status'] = 3;
                }

                $car->forceFill($updates)->save();

                $reservation->forceFill([
                    'status' => StockReservation::STATUS_COMPLETED,
                    'completed_at' => now(),
                ])->save();

                $this->stockMovementService->recordMovement(
                    $car,
                    $physicalBefore,
                    -$quantity,
                    $physicalAfter,
                    StockMovement::ACTION_DELIVERY,
                    'Giao xe cho đơn hàng ' . $lockedOrder->display_code,
                    null,
                    $user,
                    null,
                    $reservedBefore,
                    -$completedReservedQuantity,
                    $reservedAfter
                );
            }
        });
    }

    public function expireOldReservations(): int
    {
        $expiredCount = 0;

        StockReservation::query()
            ->where('status', StockReservation::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($reservations) use (&$expiredCount): void {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation, &$expiredCount): void {
                        $lockedReservation = StockReservation::query()
                            ->whereKey($reservation->getKey())
                            ->where('status', StockReservation::STATUS_ACTIVE)
                            ->lockForUpdate()
                            ->first();

                        if (!$lockedReservation) {
                            return;
                        }

                        $car = $this->lockCar((int) $lockedReservation->car_id);
                        $physicalBefore = $car->physicalStock();
                        $reservedBefore = $car->reservedStock();
                        $quantity = max(1, (int) $lockedReservation->quantity);
                        $releasedQuantity = min($quantity, $reservedBefore);
                        $reservedAfter = max(0, $reservedBefore - $releasedQuantity);

                        $car->forceFill([
                            'reserved_quantity' => $reservedAfter,
                        ])->save();

                        $lockedReservation->forceFill([
                            'status' => StockReservation::STATUS_EXPIRED,
                            'released_at' => now(),
                            'release_reason' => 'Reservation expired.',
                        ])->save();

                        $this->stockMovementService->recordMovement(
                            $car,
                            $physicalBefore,
                            0,
                            $physicalBefore,
                            StockMovement::ACTION_RELEASE_RESERVATION,
                            'Reservation expired.',
                            null,
                            null,
                            null,
                            $reservedBefore,
                            -$releasedQuantity,
                            $reservedAfter
                        );

                        $expiredCount++;
                    });
                }
            });

        return $expiredCount;
    }

    public function recalculateReservedQuantity(Car $car): Car
    {
        return DB::transaction(function () use ($car): Car {
            $lockedCar = $this->lockCar((int) $car->getKey());
            $activeReserved = (int) StockReservation::query()
                ->where('car_id', $lockedCar->car_id)
                ->where('status', StockReservation::STATUS_ACTIVE)
                ->sum('quantity');

            $lockedCar->forceFill([
                'reserved_quantity' => $activeReserved,
            ])->save();

            return $lockedCar;
        });
    }

    private function lockCar(int $carId): Car
    {
        return Car::query()
            ->lockForUpdate()
            ->findOrFail($carId);
    }
}
