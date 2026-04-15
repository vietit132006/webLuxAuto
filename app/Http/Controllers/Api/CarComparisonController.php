<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompareCarsRequest;
use App\Services\CarComparisonService;
use Illuminate\Http\JsonResponse;

class CarComparisonController extends Controller
{
    public function __construct(
        protected CarComparisonService $comparisonService
    ) {}

    public function index(CompareCarsRequest $request): JsonResponse
    {
        $data = $this->comparisonService->compare($request->validated('ids'));

        return response()->json(['data' => $data]);
    }
}
