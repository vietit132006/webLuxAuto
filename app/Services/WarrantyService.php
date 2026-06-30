<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WarrantyService
{
    public function ensureForDeliveredOrder(Order $order, ?Delivery $delivery = null, int $warrantyMonths = 36): ?Warranty
    {
        return DB::transaction(function () use ($order, $delivery, $warrantyMonths): ?Warranty {
            $lockedOrder = Order::query()
                ->with(['details.car', 'user', 'delivery'])
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            $lockedDelivery = $delivery?->exists
                ? Delivery::query()->with('car')->lockForUpdate()->find($delivery->getKey())
                : $lockedOrder->delivery;

            if (!$lockedDelivery || $lockedDelivery->status !== Delivery::STATUS_DELIVERED) {
                return null;
            }

            $existing = Warranty::query()
                ->where('order_id', $lockedOrder->order_id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $lockedDelivery->loadMissing('car');
            $car = $lockedDelivery->car ?: $lockedOrder->details->first()?->car;
            $startDate = $lockedDelivery->actual_delivery_date
                ? $lockedDelivery->actual_delivery_date->copy()
                : Carbon::now();

            return Warranty::create([
                'order_id' => $lockedOrder->order_id,
                'delivery_id' => $lockedDelivery->id,
                'user_id' => $lockedOrder->user_id,
                'car_id' => $car?->car_id,
                'vin' => $car?->vin,
                'license_plate' => $car?->license_plate,
                'start_date' => $startDate->toDateString(),
                'end_date' => $startDate->copy()->addMonthsNoOverflow($warrantyMonths)->toDateString(),
                'warranty_months' => $warrantyMonths,
                'status' => Warranty::STATUS_ACTIVE,
                'note' => 'Tự động tạo sau khi giao xe thành công.',
            ]);
        });
    }
}
