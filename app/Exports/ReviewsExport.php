<?php

namespace App\Exports;

use App\Models\Review;
use App\Support\ReviewQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReviewsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly array $filters = [],
        private readonly bool $approvedOnly = false
    ) {
    }

    public function query(): Builder
    {
        $query = Review::query()
            ->with(['user', 'car.carModel.brand', 'approvedBy', 'repliedBy'])
            ->withCount('images')
            ->select('reviews.*');

        ReviewQuery::applyFilters($query, $this->filters);

        if ($this->approvedOnly) {
            $query->where('status', Review::STATUS_APPROVED);
        }

        ReviewQuery::applySorting($query, $this->filters['sort'] ?? 'latest');

        return $query;
    }

    public function headings(): array
    {
        return [
            'Ngày gửi',
            'Khách hàng',
            'Email',
            'Xe',
            'Hãng',
            'Điểm sao',
            'Tiêu đề',
            'Nội dung',
            'Trạng thái',
            'Xác minh',
            'Số ảnh',
            'Hữu ích',
            'Báo cáo',
            'Nổi bật',
            'Người duyệt',
            'Ngày duyệt',
            'Người phản hồi',
            'Ngày phản hồi',
        ];
    }

    public function map($review): array
    {
        return [
            $review->created_at?->format('d/m/Y H:i'),
            $review->user?->name,
            $review->user?->email,
            $review->car?->name,
            $review->car?->carModel?->brand?->name,
            (int) $review->rating,
            $review->title,
            $review->comment,
            $review->statusLabel(),
            $review->verifiedLabel(),
            (int) ($review->images_count ?? 0),
            (int) $review->helpful_count,
            (int) $review->report_count,
            $review->is_featured ? 'Có' : 'Không',
            $review->approvedBy?->name,
            $review->approved_at?->format('d/m/Y H:i'),
            $review->repliedBy?->name,
            $review->replied_at?->format('d/m/Y H:i'),
        ];
    }
}
