<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Order;
use App\Models\OrderPromotion;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Models\QuotePromotion;
use App\Services\PromotionApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionApplicationService $promotionApplications)
    {
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $promotions = Promotion::query()
            ->with(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand'])
            ->withCount(['quotePromotions', 'orderPromotions'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = $filters['q'];

                $query->where(function ($inner) use ($search): void {
                    $inner->where('promotion_code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('gift_description', 'like', "%{$search}%");
                });
            })
            ->when($filters['promotion_type'] !== '', fn ($query) => $query->where('promotion_type', $filters['promotion_type']))
            ->when($filters['status'] !== '', function ($query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when($filters['featured'] !== '', fn ($query) => $query->where('is_featured', $filters['featured'] === '1'))
            ->when($filters['period'] === 'active', fn ($query) => $query->effective())
            ->when($filters['period'] === 'scheduled', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('status', Promotion::STATUS_SCHEDULED)
                        ->orWhere('start_at', '>', now());
                });
            })
            ->when($filters['period'] === 'expired', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('status', Promotion::STATUS_EXPIRED)
                        ->orWhere('end_at', '<', now());
                });
            });

        $this->applyTargetFilters($promotions, $filters);

        $promotions = $promotions
            ->orderedForDisplay()
            ->paginate(12)
            ->withQueryString();

        return view('admin.promotions.index', [
            'brands' => $this->brandsForSelect(),
            'carModels' => $this->modelsForSelect(),
            'filters' => $filters,
            'periodOptions' => $this->periodOptions(),
            'promotions' => $promotions,
            'stats' => $this->stats(),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', $this->formData(new Promotion([
            'status' => Promotion::STATUS_DRAFT,
            'discount_type' => Promotion::DISCOUNT_NONE,
            'promotion_type' => Promotion::TYPE_CASH_DISCOUNT,
            'is_public' => true,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $promotion = DB::transaction(function () use ($request, $data): Promotion {
            $data['promotion_code'] = $data['promotion_code'] ?: Promotion::generatePromotionCode();
            $data['slug'] = Promotion::uniqueSlug($data['slug'] ?: $data['title']);
            $data['created_by'] = Auth::id();

            if ($request->hasFile('banner_image')) {
                $data['banner_image'] = $request->file('banner_image')->store('promotions', 'public');
            }

            $promotion = Promotion::create($data);
            $this->syncTargets($promotion, $request);

            return $promotion;
        });

        return redirect()
            ->route('admin.promotions.edit', $promotion)
            ->with('success', 'Đã tạo chương trình khuyến mãi.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', $this->formData($promotion));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $this->validatedData($request, $promotion);

        DB::transaction(function () use ($request, $promotion, $data): void {
            $data['slug'] = Promotion::uniqueSlug($data['slug'] ?: $data['title'], $promotion->id);
            $data['promotion_code'] = $data['promotion_code'] ?: $promotion->promotion_code;

            if ($request->hasFile('banner_image')) {
                $this->deleteBanner($promotion->banner_image);
                $data['banner_image'] = $request->file('banner_image')->store('promotions', 'public');
            }

            $promotion->update($data);
            $this->syncTargets($promotion, $request);
        });

        return redirect()
            ->route('admin.promotions.edit', $promotion)
            ->with('success', 'Đã cập nhật chương trình khuyến mãi.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->loadCount(['quotePromotions', 'orderPromotions']);

        if (($promotion->quote_promotions_count + $promotion->order_promotions_count) > 0) {
            $promotion->update([
                'status' => Promotion::STATUS_ARCHIVED,
                'is_public' => false,
            ]);

            return redirect()
                ->route('admin.promotions')
                ->with('success', 'Khuyến mãi đã được dùng nên hệ thống đã chuyển sang lưu trữ thay vì xóa.');
        }

        $this->deleteBanner($promotion->banner_image);
        $promotion->delete();

        return redirect()
            ->route('admin.promotions')
            ->with('success', 'Đã xóa chương trình khuyến mãi.');
    }

    public function publish(Promotion $promotion): RedirectResponse
    {
        $promotion->update([
            'status' => Promotion::STATUS_ACTIVE,
            'is_public' => true,
        ]);

        return back()->with('success', 'Đã xuất bản chương trình khuyến mãi.');
    }

    public function archive(Promotion $promotion): RedirectResponse
    {
        $promotion->update([
            'status' => Promotion::STATUS_ARCHIVED,
            'is_public' => false,
        ]);

        return back()->with('success', 'Đã lưu trữ chương trình khuyến mãi.');
    }

    public function applicable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'car_id' => ['required', 'integer', Rule::exists('cars', 'car_id')],
            'vehicle_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $car = Car::query()
            ->with('modelInfo.brand')
            ->findOrFail($data['car_id']);

        $vehiclePrice = (float) ($data['vehicle_price'] ?? ($car->sale_price ?: $car->price));

        $promotions = $this->promotionApplications
            ->applicablePromotionsForCar($car)
            ->map(fn (Promotion $promotion): array => [
                'id' => $promotion->id,
                'promotion_code' => $promotion->promotion_code,
                'title' => $promotion->title,
                'type_label' => $promotion->typeLabel(),
                'discount_label' => $promotion->discountLabel(),
                'discount_amount' => $promotion->calculateDiscountAmount($vehiclePrice),
                'gift_description' => $promotion->gift_description,
                'target_summary' => $promotion->targetSummary(),
                'auto_apply' => $promotion->auto_apply,
                'end_at' => $promotion->end_at?->format('d/m/Y'),
            ])
            ->values();

        return response()->json([
            'promotions' => $promotions,
        ]);
    }

    public function report(): View
    {
        $topPromotions = Promotion::query()
            ->withCount(['quotePromotions', 'orderPromotions'])
            ->orderByDesc('usage_count')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $recentQuotePromotions = QuotePromotion::query()
            ->with(['promotion', 'quote.customer', 'quote.car.modelInfo.brand'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $recentOrderPromotions = OrderPromotion::query()
            ->with(['promotion', 'order.user'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        return view('admin.promotions.report', [
            'recentOrderPromotions' => $recentOrderPromotions,
            'recentQuotePromotions' => $recentQuotePromotions,
            'stats' => array_merge($this->stats(), [
                'orders_with_promotions' => Order::query()->whereHas('orderPromotions')->count(),
                'revenue_with_promotions' => (float) Order::query()->whereHas('orderPromotions')->sum('total_price'),
                'total_discount' => (float) QuotePromotion::sum('discount_amount') + (float) OrderPromotion::sum('discount_amount'),
            ]),
            'topPromotions' => $topPromotions,
        ]);
    }

    private function formData(Promotion $promotion): array
    {
        $promotion->loadMissing(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand']);

        return [
            'brands' => $this->brandsForSelect(),
            'cars' => $this->carsForSelect(),
            'carModels' => $this->modelsForSelect(),
            'discountTypes' => Promotion::DISCOUNT_TYPES,
            'promotion' => $promotion,
            'promotionTypes' => Promotion::TYPES,
            'statuses' => Promotion::STATUSES,
            'targetTypes' => PromotionTarget::TYPES,
        ];
    }

    private function validatedData(Request $request, ?Promotion $promotion = null): array
    {
        $data = $request->validate([
            'promotion_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('promotions', 'promotion_code')->ignore($promotion?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('promotions', 'slug')->ignore($promotion?->id),
            ],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'banner_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner_alt' => ['nullable', 'string', 'max:255'],
            'promotion_type' => ['required', Rule::in(array_keys(Promotion::TYPES))],
            'discount_type' => ['nullable', Rule::in(array_keys(Promotion::DISCOUNT_TYPES))],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'max_discount_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'gift_description' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status' => ['required', Rule::in(array_keys(Promotion::STATUSES))],
            'is_public' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'auto_apply' => ['nullable', 'boolean'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'priority' => ['nullable', 'integer', 'min:-999', 'max:999'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'target_all' => ['nullable', 'boolean'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', Rule::exists('brands', 'brand_id')],
            'model_ids' => ['nullable', 'array'],
            'model_ids.*' => ['integer', Rule::exists('car_models', 'id')],
            'car_ids' => ['nullable', 'array'],
            'car_ids.*' => ['integer', Rule::exists('cars', 'car_id')],
        ], [], [
            'promotion_code' => 'mã khuyến mãi',
            'title' => 'tên chương trình',
            'slug' => 'slug',
            'promotion_type' => 'loại khuyến mãi',
            'discount_type' => 'kiểu giảm giá',
            'discount_value' => 'giá trị giảm',
            'max_discount_value' => 'giảm tối đa',
            'start_at' => 'ngày bắt đầu',
            'end_at' => 'ngày kết thúc',
            'status' => 'trạng thái',
            'banner_image' => 'banner',
        ]);

        if (
            in_array($data['status'], [Promotion::STATUS_ACTIVE, Promotion::STATUS_SCHEDULED], true)
            && ! $request->user()?->can('promotions.publish')
        ) {
            throw ValidationException::withMessages([
                'status' => 'Bạn cần quyền xuất bản khuyến mãi để chuyển chương trình sang trạng thái hoạt động hoặc sắp diễn ra.',
            ]);
        }

        return [
            'promotion_code' => strtoupper(trim((string) ($data['promotion_code'] ?? ''))),
            'title' => trim((string) $data['title']),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'short_description' => $this->nullableString($data['short_description'] ?? null),
            'content' => $this->nullableString($data['content'] ?? null),
            'banner_alt' => $this->nullableString($data['banner_alt'] ?? null),
            'promotion_type' => $data['promotion_type'],
            'discount_type' => ($data['discount_type'] ?? null) ?: Promotion::DISCOUNT_NONE,
            'discount_value' => $this->nullableAmount($data['discount_value'] ?? null),
            'max_discount_value' => $this->nullableAmount($data['max_discount_value'] ?? null),
            'gift_description' => $this->nullableString($data['gift_description'] ?? null),
            'terms' => $this->nullableString($data['terms'] ?? null),
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
            'status' => $data['status'],
            'is_public' => $request->boolean('is_public'),
            'is_featured' => $request->boolean('is_featured'),
            'auto_apply' => $request->boolean('auto_apply'),
            'usage_limit' => $data['usage_limit'] ?? null,
            'priority' => (int) ($data['priority'] ?? 0),
            'seo_title' => $this->nullableString($data['seo_title'] ?? null),
            'seo_description' => $this->nullableString($data['seo_description'] ?? null),
        ];
    }

    private function syncTargets(Promotion $promotion, Request $request): void
    {
        $rows = collect();

        if ($request->boolean('target_all')) {
            $rows->push(['target_type' => PromotionTarget::TYPE_ALL, 'target_id' => null]);
        } else {
            foreach ((array) $request->input('brand_ids', []) as $id) {
                $rows->push(['target_type' => PromotionTarget::TYPE_BRAND, 'target_id' => (int) $id]);
            }

            foreach ((array) $request->input('model_ids', []) as $id) {
                $rows->push(['target_type' => PromotionTarget::TYPE_MODEL, 'target_id' => (int) $id]);
            }

            foreach ((array) $request->input('car_ids', []) as $id) {
                $rows->push(['target_type' => PromotionTarget::TYPE_CAR, 'target_id' => (int) $id]);
            }
        }

        if ($rows->isEmpty()) {
            $rows->push(['target_type' => PromotionTarget::TYPE_ALL, 'target_id' => null]);
        }

        $promotion->targets()->delete();

        $rows
            ->unique(fn (array $row): string => $row['target_type'] . ':' . ($row['target_id'] ?? 'all'))
            ->each(fn (array $row): PromotionTarget => $promotion->targets()->create($row));
    }

    private function applyTargetFilters($query, array $filters): void
    {
        if ($filters['brand_id']) {
            $brandId = $filters['brand_id'];
            $modelIds = CarModel::query()->where('brand_id', $brandId)->pluck('id');
            $carIds = Car::query()->whereIn('car_model_id', $modelIds)->pluck('car_id');

            $query->whereHas('targets', function ($targetQuery) use ($brandId, $modelIds, $carIds): void {
                $targetQuery->where('target_type', PromotionTarget::TYPE_ALL)
                    ->orWhere(function ($inner) use ($brandId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_BRAND)
                            ->where('target_id', $brandId);
                    })
                    ->orWhere(function ($inner) use ($modelIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_MODEL)
                            ->whereIn('target_id', $modelIds);
                    })
                    ->orWhere(function ($inner) use ($carIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_CAR)
                            ->whereIn('target_id', $carIds);
                    });
            });
        }

        if ($filters['model_id']) {
            $modelId = $filters['model_id'];
            $brandId = CarModel::query()->whereKey($modelId)->value('brand_id');
            $carIds = Car::query()->where('car_model_id', $modelId)->pluck('car_id');

            $query->whereHas('targets', function ($targetQuery) use ($modelId, $brandId, $carIds): void {
                $targetQuery->where('target_type', PromotionTarget::TYPE_ALL)
                    ->orWhere(function ($inner) use ($brandId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_BRAND)
                            ->where('target_id', $brandId);
                    })
                    ->orWhere(function ($inner) use ($modelId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_MODEL)
                            ->where('target_id', $modelId);
                    })
                    ->orWhere(function ($inner) use ($carIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_CAR)
                            ->whereIn('target_id', $carIds);
                    });
            });
        }
    }

    private function filters(Request $request): array
    {
        $promotionType = (string) $request->input('promotion_type', '');
        $status = (string) $request->input('status', '');
        $featured = (string) $request->input('featured', '');
        $period = (string) $request->input('period', '');
        $brandId = (string) $request->input('brand_id', '');
        $modelId = (string) $request->input('model_id', '');

        return [
            'q' => trim((string) $request->input('q', '')),
            'promotion_type' => array_key_exists($promotionType, Promotion::TYPES) ? $promotionType : '',
            'status' => array_key_exists($status, Promotion::STATUSES) ? $status : '',
            'featured' => in_array($featured, ['0', '1'], true) ? $featured : '',
            'period' => array_key_exists($period, $this->periodOptions()) ? $period : '',
            'brand_id' => ctype_digit($brandId) ? (int) $brandId : null,
            'model_id' => ctype_digit($modelId) ? (int) $modelId : null,
        ];
    }

    private function periodOptions(): array
    {
        return [
            'active' => 'Đang diễn ra',
            'scheduled' => 'Sắp diễn ra',
            'expired' => 'Đã hết hạn',
        ];
    }

    private function stats(): array
    {
        return [
            'total' => Promotion::count(),
            'active' => Promotion::query()->effective()->count(),
            'scheduled' => Promotion::query()
                ->where(function ($query): void {
                    $query->where('status', Promotion::STATUS_SCHEDULED)
                        ->orWhere('start_at', '>', now());
                })
                ->count(),
            'expired' => Promotion::query()
                ->where(function ($query): void {
                    $query->where('status', Promotion::STATUS_EXPIRED)
                        ->orWhere('end_at', '<', now());
                })
                ->count(),
            'featured' => Promotion::where('is_featured', true)->count(),
        ];
    }

    private function brandsForSelect()
    {
        return Brand::query()
            ->orderBy('name')
            ->get(['brand_id', 'name']);
    }

    private function modelsForSelect()
    {
        return CarModel::query()
            ->with('brand')
            ->orderBy('name')
            ->get(['id', 'brand_id', 'name']);
    }

    private function carsForSelect()
    {
        return Car::query()
            ->with('modelInfo.brand')
            ->orderByDesc('created_at')
            ->get(['car_id', 'car_model_id', 'name', 'vin', 'price', 'sale_price', 'stock', 'stock_quantity', 'reserved_quantity', 'status']);
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function deleteBanner(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
