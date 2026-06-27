<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message, array $context = []): bool
    {
        Log::info('SMS service is not connected to a provider yet.', [
            'provider_candidates' => $this->providerCandidates(),
            'phone' => $phone,
            'message' => $message,
            'context' => $context,
        ]);

        return false;
    }

    public function sendTestDriveStatusNotification(Ticket $ticket): bool
    {
        $phone = $ticket->user?->phone;

        if (!$phone) {
            return false;
        }

        return $this->send(
            $phone,
            'Lux Auto: lịch lái thử ' . $ticket->display_code . ' đã chuyển sang ' . $ticket->test_drive_status_label . '.',
            [
                'ticket_id' => $ticket->ticket_id,
                'status' => $ticket->status,
            ]
        );
    }

    public function providerCandidates(): array
    {
        return ['twilio', 'vnpt', 'viettel'];
    }
}
