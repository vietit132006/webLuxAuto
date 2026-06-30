<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Quote extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Nháp',
        self::STATUS_SENT => 'Đã gửi',
        self::STATUS_ACCEPTED => 'Đã chấp nhận',
        self::STATUS_REJECTED => 'Đã từ chối',
        self::STATUS_EXPIRED => 'Hết hạn',
    ];

    protected $table = 'quotes';
    protected $primaryKey = 'quote_id';

    protected $fillable = [
        'quote_code',
        'public_token',
        'customer_id',
        'car_id',
        'user_id',
        'test_drive_id',
        'live_session_id',
        'live_lead_id',
        'vehicle_price',
        'discount_amount',
        'registration_fee',
        'plate_fee',
        'insurance_fee',
        'other_fee',
        'total_price',
        'status',
        'note',
        'expired_at',
        'sent_at',
        'viewed_at',
        'customer_responded_at',
        'customer_response_note',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'registration_fee' => 'decimal:2',
            'plate_fee' => 'decimal:2',
            'insurance_fee' => 'decimal:2',
            'other_fee' => 'decimal:2',
            'total_price' => 'decimal:2',
            'expired_at' => 'date',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'customer_responded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Quote $quote) {
            $quote->total_price = self::calculateTotal($quote->attributesToArray());

            if (!$quote->quote_code) {
                $quote->quote_code = self::generateQuoteCode();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function testDrive(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'test_drive_id', 'ticket_id');
    }

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function liveLead(): BelongsTo
    {
        return $this->belongsTo(LiveLead::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'quote_id', 'quote_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'quote_id', 'quote_id');
    }

    public function quotePromotions(): HasMany
    {
        return $this->hasMany(QuotePromotion::class, 'quote_id', 'quote_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusClass(): string
    {
        return 'quotes-status-' . str_replace('_', '-', (string) $this->status);
    }

    public function money(string $field): string
    {
        return number_format((float) $this->{$field}, 0, ',', '.') . ' đ';
    }

    public function promotionDiscountTotal(): float
    {
        $this->loadMissing('quotePromotions');

        return (float) $this->quotePromotions->sum(fn (QuotePromotion $quotePromotion): float => (float) $quotePromotion->discount_amount);
    }

    public function ensurePublicToken(): string
    {
        if (!$this->public_token) {
            $this->public_token = self::generatePublicToken();
        }

        return $this->public_token;
    }

    public function publicUrl(): ?string
    {
        if (!$this->public_token) {
            return null;
        }

        return route('quotes.public.show', [
            'quote' => $this->quote_code,
            'token' => $this->public_token,
        ]);
    }

    public function publicPdfUrl(): ?string
    {
        if (!$this->public_token) {
            return null;
        }

        return route('quotes.public.pdf', [
            'quote' => $this->quote_code,
            'token' => $this->public_token,
        ]);
    }

    public function isDateExpired(): bool
    {
        return $this->expired_at !== null && $this->expired_at->isPast() && !$this->expired_at->isToday();
    }

    public function canCustomerRespond(): bool
    {
        return !$this->isDateExpired()
            && in_array($this->status, [self::STATUS_SENT, self::STATUS_DRAFT], true);
    }

    public static function calculateTotal(array $data): float
    {
        $vehiclePrice = (float) ($data['vehicle_price'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $registrationFee = (float) ($data['registration_fee'] ?? 0);
        $plateFee = (float) ($data['plate_fee'] ?? 0);
        $insuranceFee = (float) ($data['insurance_fee'] ?? 0);
        $otherFee = (float) ($data['other_fee'] ?? 0);

        return max(0, $vehiclePrice - $discountAmount + $registrationFee + $plateFee + $insuranceFee + $otherFee);
    }

    public static function generateQuoteCode(): string
    {
        $lastCode = static::query()
            ->where('quote_code', 'like', 'BG%')
            ->orderByDesc('quote_id')
            ->lockForUpdate()
            ->value('quote_code');

        $nextNumber = 1;

        if ($lastCode && preg_match('/^BG(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'BG' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function generatePublicToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('public_token', $token)->exists());

        return $token;
    }
}
