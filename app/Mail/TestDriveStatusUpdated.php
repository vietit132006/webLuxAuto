<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestDriveStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
        $this->ticket->loadMissing(['user', 'car.brand', 'car.carModel.brand']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cập nhật lịch lái thử ' . $this->ticket->display_code
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test_drives.status_updated',
            with: [
                'ticket' => $this->ticket,
                'customerName' => $this->ticket->user?->name ?? 'Quý khách',
                'carName' => $this->carName(),
                'appointmentText' => $this->appointmentText(),
                'statusText' => $this->ticket->test_drive_status_label,
                'showroomContact' => $this->showroomContact(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function carName(): string
    {
        if (!$this->ticket->car) {
            return 'Xe đã đăng ký';
        }

        return trim(($this->ticket->car->brand->name ?? '') . ' ' . $this->ticket->car->name);
    }

    private function appointmentText(): string
    {
        if (!$this->ticket->appointment_date) {
            return 'Showroom sẽ liên hệ xác nhận lịch hẹn cụ thể.';
        }

        $time = $this->ticket->appointment_time
            ? ' ' . substr((string) $this->ticket->appointment_time, 0, 5)
            : '';

        return $this->ticket->appointment_date->format('d/m/Y') . $time;
    }

    private function showroomContact(): array
    {
        return [
            'name' => $this->ticket->showroom ?: config('app.name', 'Lux Auto'),
            'phone' => config('services.showroom.phone', '1900 636 068'),
            'email' => config('mail.from.address', 'support@luxauto.local'),
        ];
    }
}
