<?php

namespace App\Support;

use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewQuery
{
    public static function cleanFilters(Request|array $input): array
    {
        $value = fn (string $key, mixed $default = '') => $input instanceof Request
            ? $input->input($key, $default)
            : ($input[$key] ?? $default);

        $status = (string) $value('status', '');
        $verifiedType = (string) $value('verified_type', '');
        $hasImages = (string) $value('has_images', '');
        $sort = (string) $value('sort', 'latest');

        return [
            'q' => trim((string) $value('q', '')),
            'car_id' => self::positiveInt($value('car_id')),
            'brand_id' => self::positiveInt($value('brand_id')),
            'model_id' => self::positiveInt($value('model_id')),
            'rating' => self::rating($value('rating')),
            'status' => array_key_exists($status, Review::STATUSES) ? $status : '',
            'verified_type' => array_key_exists($verifiedType, Review::VERIFIED_TYPES) ? $verifiedType : '',
            'has_images' => in_array($hasImages, ['0', '1'], true) ? $hasImages : '',
            'date_from' => self::date($value('date_from')),
            'date_to' => self::date($value('date_to')),
            'sort' => in_array($sort, ['latest', 'oldest', 'rating_desc', 'rating_asc', 'reports_desc'], true) ? $sort : 'latest',
        ];
    }

    public static function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? '', function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('comment', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('internal_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['car_id'] ?? null, fn (Builder $query, int $carId) => $query->where('car_id', $carId))
            ->when($filters['brand_id'] ?? null, function (Builder $query, int $brandId): void {
                $query->whereHas('car.carModel.brand', fn (Builder $brandQuery) => $brandQuery->where('brand_id', $brandId));
            })
            ->when($filters['model_id'] ?? null, function (Builder $query, int $modelId): void {
                $query->whereHas('car', fn (Builder $carQuery) => $carQuery->where('car_model_id', $modelId));
            })
            ->when($filters['rating'] ?? null, fn (Builder $query, int $rating) => $query->where('rating', $rating))
            ->when(($filters['status'] ?? '') !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['verified_type'] ?? '') !== '', fn (Builder $query) => $query->where('verified_type', $filters['verified_type']))
            ->when(($filters['has_images'] ?? '') === '1', fn (Builder $query) => $query->whereHas('images'))
            ->when(($filters['has_images'] ?? '') === '0', fn (Builder $query) => $query->whereDoesntHave('images'))
            ->when($filters['date_from'] ?? '', function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'] ?? '', function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            });
    }

    public static function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('review_id'),
            'rating_desc' => $query->orderByDesc('rating')->orderByDesc('created_at')->orderByDesc('review_id'),
            'rating_asc' => $query->orderBy('rating')->orderByDesc('created_at')->orderByDesc('review_id'),
            'reports_desc' => $query->orderByDesc('report_count')->orderByDesc('created_at')->orderByDesc('review_id'),
            default => $query->orderByDesc('created_at')->orderByDesc('review_id'),
        };
    }

    private static function positiveInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function rating(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value >= 1 && (int) $value <= 5 ? (int) $value : null;
    }

    private static function date(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }
}
