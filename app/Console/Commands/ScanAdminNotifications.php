<?php

namespace App\Console\Commands;

use App\Models\AdminNotification;
use App\Models\Car;
use App\Models\Delivery;
use App\Models\LiveLead;
use App\Models\Quote;
use App\Models\Review;
use App\Models\ServiceAppointment;
use App\Models\Ticket;
use App\Models\Warranty;
use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class ScanAdminNotifications extends Command
{
    protected $signature = 'admin:notifications:scan {--low-stock=2 : Available stock threshold for low inventory alerts}';

    protected $description = 'Scan operational data and create anti-spam admin notifications.';

    public function handle(AdminNotificationService $notifications): int
    {
        $created = 0;
        $created += $this->scanExpiringQuotes($notifications);
        $created += $this->scanTodayDeliveries($notifications);
        $created += $this->scanTodayTestDrives($notifications);
        $created += $this->scanTodayServices($notifications);
        $created += $this->scanExpiringWarranties($notifications);
        $created += $this->scanInventory($notifications, max(0, (int) $this->option('low-stock')));
        $created += $this->scanReviews($notifications);
        $created += $this->scanLiveLeads($notifications);

        $this->info('Admin notification scan completed. Checked/created signals: ' . number_format($created) . '.');

        return self::SUCCESS;
    }

    private function scanExpiringQuotes(AdminNotificationService $notifications): int
    {
        return Quote::query()
            ->whereIn('status', [Quote::STATUS_DRAFT, Quote::STATUS_SENT])
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->get()
            ->sum(function (Quote $quote) use ($notifications): int {
                $daysLeft = now()->startOfDay()->diffInDays($quote->expired_at?->copy()->startOfDay(), false);
                $priority = $daysLeft <= 1 ? AdminNotification::PRIORITY_HIGH : AdminNotification::PRIORITY_NORMAL;

                $notifications->createOnce(
                    'quotes',
                    'quote_expiring_soon',
                    'Bao gia ' . $quote->quote_code . ' sap het han',
                    'Bao gia can duoc cham soc truoc ngay het han ' . $quote->expired_at?->format('d/m/Y') . '.',
                    route('admin.quotes.show', $quote, false),
                    ['quote_id' => $quote->quote_id, 'expired_at' => $quote->expired_at?->toDateString()],
                    $priority,
                    null,
                    20
                );

                return 1;
            });
    }

    private function scanTodayDeliveries(AdminNotificationService $notifications): int
    {
        return Delivery::query()
            ->with('order')
            ->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_PREPARING, Delivery::STATUS_READY])
            ->whereDate('expected_delivery_date', now()->toDateString())
            ->get()
            ->sum(function (Delivery $delivery) use ($notifications): int {
                $orderId = $delivery->order_id;

                $notifications->createOnce(
                    'deliveries',
                    'delivery_today',
                    'Lich giao xe hom nay',
                    'Don ' . ($delivery->order?->display_code ?? ('#' . $orderId)) . ' can theo doi giao xe trong ngay.',
                    route('admin.orders.show', $orderId, false),
                    ['delivery_id' => $delivery->id, 'order_id' => $orderId],
                    AdminNotification::PRIORITY_HIGH,
                    null,
                    20
                );

                return 1;
            });
    }

    private function scanTodayTestDrives(AdminNotificationService $notifications): int
    {
        return Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->whereIn('status', [Ticket::STATUS_PENDING, Ticket::STATUS_APPROVED])
            ->whereDate('appointment_date', now()->toDateString())
            ->get()
            ->sum(function (Ticket $booking) use ($notifications): int {
                $notifications->createOnce(
                    'test_drives',
                    'test_drive_today',
                    'Lich lai thu hom nay',
                    'Lich ' . $booking->display_code . ' can sale xac nhan va theo doi.',
                    route('admin.test_drives.show', $booking->ticket_id, false),
                    ['ticket_id' => $booking->ticket_id],
                    AdminNotification::PRIORITY_HIGH,
                    null,
                    20
                );

                return 1;
            });
    }

    private function scanTodayServices(AdminNotificationService $notifications): int
    {
        return ServiceAppointment::query()
            ->pendingOrConfirmedToday()
            ->get()
            ->sum(function (ServiceAppointment $appointment) use ($notifications): int {
                $notifications->createOnce(
                    'services',
                    'service_today',
                    'Lich dich vu hom nay',
                    'Lich ' . $appointment->appointment_code . ' can CSKH theo doi trong ngay.',
                    route('admin.service-appointments.show', $appointment, false),
                    ['service_appointment_id' => $appointment->id],
                    AdminNotification::PRIORITY_HIGH,
                    null,
                    20
                );

                return 1;
            });
    }

    private function scanExpiringWarranties(AdminNotificationService $notifications): int
    {
        return Warranty::query()
            ->expiringWithin(30)
            ->get()
            ->sum(function (Warranty $warranty) use ($notifications): int {
                $notifications->createOnce(
                    'warranties',
                    'warranty_expiring_soon',
                    'Bao hanh sap het han',
                    'Phieu ' . $warranty->warranty_code . ' het han ngay ' . $warranty->end_date?->format('d/m/Y') . '.',
                    route('admin.warranties.show', $warranty, false),
                    ['warranty_id' => $warranty->id, 'end_date' => $warranty->end_date?->toDateString()],
                    AdminNotification::PRIORITY_NORMAL,
                    null,
                    72
                );

                return 1;
            });
    }

    private function scanInventory(AdminNotificationService $notifications, int $lowStockThreshold): int
    {
        return Car::query()
            ->with('modelInfo.brand')
            ->get()
            ->sum(function (Car $car) use ($notifications, $lowStockThreshold): int {
                $count = 0;
                $title = trim(($car->modelInfo?->brand?->name ? $car->modelInfo->brand->name . ' ' : '') . $car->name);

                if ($car->isOutOfStock()) {
                    $notifications->createOnce(
                        'inventory',
                        'inventory_out_of_stock',
                        'Xe het hang',
                        ($title ?: ('Xe #' . $car->car_id)) . ' da het ton kho vat ly.',
                        route('admin.cars.show', $car, false),
                        ['car_id' => $car->car_id, 'physical_stock' => $car->physicalStock()],
                        AdminNotification::PRIORITY_URGENT,
                        null,
                        24
                    );
                    $count++;
                } elseif ($car->isFullyReserved()) {
                    $notifications->createOnce(
                        'inventory',
                        'inventory_fully_reserved',
                        'Xe da giu het',
                        ($title ?: ('Xe #' . $car->car_id)) . ' khong con ton kha dung vi da duoc giu cho.',
                        route('admin.cars.show', $car, false),
                        ['car_id' => $car->car_id, 'available_stock' => $car->availableStock()],
                        AdminNotification::PRIORITY_HIGH,
                        null,
                        24
                    );
                    $count++;
                } elseif ($car->availableStock() <= $lowStockThreshold) {
                    $notifications->createOnce(
                        'inventory',
                        'inventory_low_available',
                        'Ton kho kha dung thap',
                        ($title ?: ('Xe #' . $car->car_id)) . ' chi con ' . $car->availableStock() . ' xe kha dung.',
                        route('admin.cars.show', $car, false),
                        ['car_id' => $car->car_id, 'available_stock' => $car->availableStock()],
                        AdminNotification::PRIORITY_NORMAL,
                        null,
                        24
                    );
                    $count++;
                }

                return $count;
            });
    }

    private function scanReviews(AdminNotificationService $notifications): int
    {
        return Review::query()
            ->where(function ($query): void {
                $query->where('status', Review::STATUS_PENDING)
                    ->orWhere('status', Review::STATUS_REPORTED)
                    ->orWhere('rating', '<=', 2);
            })
            ->latest()
            ->limit(100)
            ->get()
            ->sum(function (Review $review) use ($notifications): int {
                $type = $review->status === Review::STATUS_REPORTED
                    ? 'review_reported'
                    : ((int) $review->rating <= 2 ? 'review_low_rating' : 'review_pending');
                $priority = $review->status === Review::STATUS_REPORTED || (int) $review->rating <= 2
                    ? AdminNotification::PRIORITY_HIGH
                    : AdminNotification::PRIORITY_NORMAL;

                $notifications->createOnce(
                    'reviews',
                    $type,
                    $type === 'review_pending' ? 'Danh gia moi cho duyet' : 'Danh gia can xu ly',
                    'Review #' . $review->review_id . ' co trang thai ' . $review->statusLabel() . ', rating ' . $review->rating . '/5.',
                    route('admin.reviews.index', ['status' => $review->status], false),
                    ['review_id' => $review->review_id, 'rating' => $review->rating],
                    $priority
                );

                return 1;
            });
    }

    private function scanLiveLeads(AdminNotificationService $notifications): int
    {
        return LiveLead::query()
            ->where('status', LiveLead::STATUS_NEW)
            ->latest()
            ->limit(100)
            ->get()
            ->sum(function (LiveLead $lead) use ($notifications): int {
                $notifications->createOnce(
                    'live',
                    'live_lead_new',
                    'Live lead moi',
                    $lead->customerDisplayName() . ' de lai nhu cau ' . $lead->leadTypeLabel() . '.',
                    route('admin.live.leads.show', $lead, false),
                    ['live_lead_id' => $lead->id, 'lead_type' => $lead->lead_type],
                    AdminNotification::PRIORITY_HIGH
                );

                return 1;
            });
    }
}
