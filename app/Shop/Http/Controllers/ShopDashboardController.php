<?php

namespace App\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Shop\Models\Product;
use Inertia\Inertia;

class ShopDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $analytics = $this->dashboardService->getAnalytics();

        $lowStockCount = Product::where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        return Inertia::render('shop/dashboard', [
            'analytics' => $analytics,
            'lowStockCount' => $lowStockCount,
        ]);
    }
}
