<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $stats = $this->dashboardService->statsForUser($request->user());

        return $this->success(
            data: $stats->toArray(),
            message: 'Dashboard statistics retrieved successfully.',
        );
    }
}
