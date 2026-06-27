<?php

namespace App\Services;

use App\Mail\TestDriveStatusUpdated;
use App\Models\Ticket;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestDriveNotificationService
{
    public function __construct(private readonly SmsService $smsService)
    {
    }

    public function notifyStatusChanged(Ticket $ticket): bool
    {
        $ticket->loadMissing(['user', 'car.brand', 'car.carModel.brand']);

        $this->smsService->sendTestDriveStatusNotification($ticket);

        if (!$ticket->user?->email) {
            return false;
        }

        try {
            Mail::to($ticket->user->email)->send(new TestDriveStatusUpdated($ticket));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
