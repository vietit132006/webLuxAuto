<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Review;
use App\Models\ServiceRecord;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ReviewEligibilityService
{
    public function resolve(Car $car, User $user): array
    {
        if ($purchaseOrder = $this->purchaseOrder($car, $user)) {
            return $this->result(true, Review::VERIFIED_PURCHASE, orderId: (int) $purchaseOrder->order_id);
        }

        if ($depositOrder = $this->depositOrder($car, $user)) {
            return $this->result(true, Review::VERIFIED_DEPOSIT, orderId: (int) $depositOrder->order_id);
        }

        if ($testDrive = $this->completedTestDrive($car, $user)) {
            return $this->result(true, Review::VERIFIED_TEST_DRIVE, ticketId: (int) $testDrive->ticket_id);
        }

        if ($serviceRecord = $this->completedServiceRecord($car, $user)) {
            return $this->result(true, Review::VERIFIED_SERVICE, serviceRecordId: (int) $serviceRecord->id);
        }

        return $this->result(false, Review::VERIFIED_NONE);
    }

    private function purchaseOrder(Car $car, User $user): ?Order
    {
        return Order::query()
            ->where('user_id', $user->user_id)
            ->whereHas('details', fn ($query) => $query->where('car_id', $car->car_id))
            ->where(function ($query): void {
                $query->where('status', Order::STATUS_COMPLETED)
                    ->orWhereHas('delivery', fn ($deliveryQuery) => $deliveryQuery->where('status', Delivery::STATUS_DELIVERED));
            })
            ->orderByDesc('created_at')
            ->orderByDesc('order_id')
            ->first();
    }

    private function depositOrder(Car $car, User $user): ?Order
    {
        return Order::query()
            ->where('user_id', $user->user_id)
            ->where('status', Order::STATUS_DEPOSITED)
            ->whereHas('details', fn ($query) => $query->where('car_id', $car->car_id))
            ->orderByDesc('deposit_date')
            ->orderByDesc('created_at')
            ->orderByDesc('order_id')
            ->first();
    }

    private function completedTestDrive(Car $car, User $user): ?Ticket
    {
        if (
            !Schema::hasTable('support_tickets')
            || !Schema::hasColumn('support_tickets', 'ticket_type')
            || !Schema::hasColumn('support_tickets', 'car_id')
        ) {
            return null;
        }

        return Ticket::query()
            ->where('user_id', $user->user_id)
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->where('status', Ticket::STATUS_COMPLETED)
            ->where('car_id', $car->car_id)
            ->orderByDesc('created_at')
            ->orderByDesc('ticket_id')
            ->first();
    }

    private function completedServiceRecord(Car $car, User $user): ?ServiceRecord
    {
        if (!Schema::hasTable('service_records')) {
            return null;
        }

        return ServiceRecord::query()
            ->where('user_id', $user->user_id)
            ->where('car_id', $car->car_id)
            ->where('status', ServiceRecord::STATUS_COMPLETED)
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->first();
    }

    private function result(
        bool $canReview,
        string $verifiedType,
        ?int $orderId = null,
        ?int $ticketId = null,
        ?int $serviceRecordId = null
    ): array {
        return [
            'can_review' => $canReview,
            'verified_type' => $verifiedType,
            'order_id' => $orderId,
            'ticket_id' => $ticketId,
            'service_record_id' => $serviceRecordId,
        ];
    }
}
