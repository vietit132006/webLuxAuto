<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportApiController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function sales(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');
        if (! in_array($period, ['daily', 'monthly', 'yearly'], true)) {
            return response()->json(['message' => 'Invalid period'], 422);
        }

        return response()->json($this->reportService->salesRevenue($period));
    }

    public function bestSellers(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        return response()->json(['data' => $this->reportService->bestSellingCars($limit)]);
    }

    public function inventory(Request $request): JsonResponse
    {
        $threshold = min(1000, max(1, (int) $request->query('low_threshold', 5)));
        $report = $this->reportService->inventoryReport($threshold);

        return response()->json([
            'low_stock' => $report['low_stock']->values(),
            'out_of_stock' => $report['out_of_stock']->values(),
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        return response()->json($this->reportService->customerReport($limit));
    }

    public function topSpenders(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        return response()->json(['data' => $this->reportService->topCustomersBySpending($limit)]);
    }

    public function newVsReturning(Request $request): JsonResponse
    {
        $days = min(365, max(1, (int) $request->query('days', 30)));

        return response()->json($this->reportService->newVsReturning($days));
    }

    public function reviews(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query('limit', 10)));

        return response()->json($this->reportService->reviewReport($limit));
    }

    public function reviewAverages(Request $request): JsonResponse
    {
        $limit = min(200, max(1, (int) $request->query('limit', 50)));

        return response()->json(['data' => $this->reportService->averageRatingPerCar($limit)]);
    }

    public function mostReviewed(Request $request): JsonResponse
    {
        $limit = min(200, max(1, (int) $request->query('limit', 10)));

        return response()->json(['data' => $this->reportService->mostReviewedCars($limit)]);
    }

    public function reviewsForCar(Request $request, Car $car): JsonResponse
    {
        return response()->json([
            'car' => [
                'car_id' => $car->car_id,
                'name' => $car->name,
            ],
            'data' => $this->reportService->reviewReportForCar((int) $car->car_id),
        ]);
    }
}
