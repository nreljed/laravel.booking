<?php

namespace App\Services;

use App\Models\User;
use App\Shop\Models\Cart;
use App\Shop\Models\Order;
use App\Shop\Models\OrderItem;
use App\Shop\Models\Product;
use App\Shop\Models\ProductCategory;
use App\Shop\Models\ProductReview;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', 300, function () {
            $stats = [
                'total_users' => [
                    'label' => 'Total Users',
                    'icon' => 'Users',
                    'value' => User::count(),
                ],
                'new_users_today' => [
                    'label' => 'New Today',
                    'icon' => 'UserPlus',
                    'value' => User::whereDate('created_at', today())->count(),
                ],
            ];

            $registry = app(FeatureRegistry::class);
            $moduleData = $registry->getAllDashboardData();

            foreach ($moduleData as $slug => $data) {
                $reserved = ['label', 'icon', 'total'];
                $hasTotal = isset($data['total']);
                if ($hasTotal) {
                    $stats[$slug] = [
                        'label' => $data['label'] ?? ucfirst($slug),
                        'icon' => $data['icon'] ?? 'FileText',
                        'value' => $data['total'],
                    ];
                }
                foreach ($data as $key => $value) {
                    if (in_array($key, $reserved, true)) {
                        continue;
                    }
                    $stats[$slug.'_'.$key] = [
                        'label' => $data['label'].' — '.ucfirst(str_replace('_', ' ', $key)),
                        'icon' => $data['icon'] ?? 'FileText',
                        'value' => $value,
                    ];
                }
            }

            return $stats;
        });
    }

    public function getAnalytics(int $days = 14): array
    {
        return Cache::remember('dashboard.analytics', 300, function () use ($days) {
            if (! Schema::hasTable('orders')) {
                return [
                    'kpis' => [],
                    'revenue_over_time' => [],
                    'recent_orders' => [],
                    'top_products' => [],
                    'order_status_counts' => [],
                    'total_revenue' => 0.0,
                    'total_orders' => 0,
                    'total_products' => 0,
                    'total_customers' => 0,
                    'active_products' => 0,
                    'abandoned_carts' => 0,
                    'recovered_carts' => 0,
                    'days' => $days,
                ];
            }

            $today = today();
            $yesterday = today()->subDay();
            $thisWeek = [now()->startOfWeek(), now()->endOfWeek()];
            $lastWeek = [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()];
            $thisMonth = [now()->startOfMonth(), now()->endOfMonth()];
            $lastMonth = [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()];
            $periodStart = today()->subDays($days - 1);

            $completedStatuses = ['completed', 'processing'];

            $revenueToday = Order::whereIn('status', $completedStatuses)->whereDate('created_at', $today)->sum('total');
            $revenueYesterday = Order::whereIn('status', $completedStatuses)->whereDate('created_at', $yesterday)->sum('total');

            $ordersToday = Order::whereDate('created_at', $today)->count();
            $ordersYesterday = Order::whereDate('created_at', $yesterday)->count();

            $aovThisMonth = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $thisMonth)->avg('total') ?? 0;
            $aovLastMonth = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $lastMonth)->avg('total') ?? 0;

            $totalCheckouts = Order::whereBetween('created_at', $thisMonth)->count();
            $completedCheckouts = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $thisMonth)->count();
            $conversionRate = $totalCheckouts > 0 ? round(($completedCheckouts / $totalCheckouts) * 100, 1) : 0;

            $previousTotalCheckouts = Order::whereBetween('created_at', $lastMonth)->count();
            $previousCompletedCheckouts = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $lastMonth)->count();
            $previousConversionRate = $previousTotalCheckouts > 0 ? round(($previousCompletedCheckouts / $previousTotalCheckouts) * 100, 1) : 0;

            $revenueThisMonth = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $thisMonth)->sum('total');
            $revenueLastMonth = Order::whereIn('status', $completedStatuses)->whereBetween('created_at', $lastMonth)->sum('total');

            // Period KPIs (based on $days)
            $periodRevenue = Order::whereIn('status', $completedStatuses)->whereDate('created_at', '>=', $periodStart)->sum('total');
            $prevPeriodRevenue = Order::whereIn('status', $completedStatuses)
                ->whereDate('created_at', '>=', (clone $periodStart)->subDays($days))
                ->whereDate('created_at', '<', $periodStart)
                ->sum('total');

            $periodOrders = Order::whereDate('created_at', '>=', $periodStart)->count();
            $prevPeriodOrders = Order::whereDate('created_at', '>=', (clone $periodStart)->subDays($days))
                ->whereDate('created_at', '<', $periodStart)
                ->count();

            $periodAov = $periodOrders > 0 ? round($periodRevenue / $periodOrders, 2) : 0;
            $prevPeriodAov = $prevPeriodOrders > 0 ? round($prevPeriodRevenue / $prevPeriodOrders, 2) : 0;

            // Customer insights
            $totalCustomers = User::count();
            $returningCustomers = Order::whereNotNull('user_id')
                ->select('user_id', DB::raw('count(*) as order_count'))
                ->groupBy('user_id')
                ->having('order_count', '>', 1)
                ->count();
            $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0;

            $customerLtv = $totalCustomers > 0
                ? round(Order::whereIn('status', $completedStatuses)->sum('total') / $totalCustomers, 2)
                : 0;

            $prevMonthCustomers = User::where('created_at', '<', now()->startOfMonth())->count();
            $prevMonthReturning = Order::whereNotNull('user_id')
                ->where('created_at', '<', now()->startOfMonth())
                ->select('user_id', DB::raw('count(*) as order_count'))
                ->groupBy('user_id')
                ->having('order_count', '>', 1)
                ->count();
            $prevRetentionRate = $prevMonthCustomers > 0
                ? round(($prevMonthReturning / $prevMonthCustomers) * 100, 1)
                : 0;

            // Customer growth over time (last 6 months)
            $customerGrowth = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $endOfMonth = (clone $month)->endOfMonth();
                $customerGrowth[] = [
                    'month' => $month->format('M'),
                    'customers' => User::where('created_at', '<=', $endOfMonth)->count(),
                    'new' => User::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->count(),
                ];
            }

            // Revenue breakdown by product category
            $revenueBreakdown = [];
            try {
                $categoryRevenue = OrderItem::select(
                    'product_category_product.category_id',
                    DB::raw('SUM(order_items.total) as total'),
                )
                    ->join('product_category_product', 'order_items.product_id', '=', 'product_category_product.product_id')
                    ->whereIn('order_items.order_id', function ($q) use ($completedStatuses) {
                        $q->select('id')->from('orders')
                            ->whereIn('status', $completedStatuses);
                    })
                    ->groupBy('product_category_product.category_id')
                    ->get();

                $totalRevenue = $categoryRevenue->sum('total');
                $categoryNames = ProductCategory::pluck('name', 'id')->toArray();

                $sorted = $categoryRevenue->sortByDesc('total')->values();
                $top5 = $sorted->take(5);
                $othersTotal = $sorted->skip(5)->sum('total');

                foreach ($top5 as $item) {
                    $name = $categoryNames[$item->category_id] ?? 'Uncategorized';
                    $revenueBreakdown[] = [
                        'name' => $name,
                        'value' => (float) $item->total,
                        'percentage' => $totalRevenue > 0 ? round(($item->total / $totalRevenue) * 100, 1) : 0,
                    ];
                }
                if ($othersTotal > 0) {
                    $revenueBreakdown[] = [
                        'name' => 'Others',
                        'value' => (float) $othersTotal,
                        'percentage' => $totalRevenue > 0 ? round(($othersTotal / $totalRevenue) * 100, 1) : 0,
                    ];
                }
            } catch (\Exception) {
                // pivot table may not exist
            }

            // If no category breakdown, fall back to simple product-based breakdown
            if (empty($revenueBreakdown)) {
                $productRevenue = OrderItem::select('product_name', DB::raw('SUM(total) as total'))
                    ->whereIn('order_id', function ($q) use ($completedStatuses) {
                        $q->select('id')->from('orders')->whereIn('status', $completedStatuses);
                    })
                    ->groupBy('product_name')
                    ->orderByDesc('total')
                    ->take(5)
                    ->get();

                $totalRev = $productRevenue->sum('total');
                foreach ($productRevenue as $item) {
                    $revenueBreakdown[] = [
                        'name' => $item->product_name,
                        'value' => (float) $item->total,
                        'percentage' => $totalRev > 0 ? round(($item->total / $totalRev) * 100, 1) : 0,
                    ];
                }
            }

            $activeProducts = Product::where('status', 'published')->count();

            return [
                'days' => $days,
                'kpis' => [
                    [
                        'label' => 'Revenue',
                        'subtitle' => "Last {$days} days",
                        'value' => (float) $periodRevenue,
                        'format' => 'currency',
                        'change' => $this->pctChange($prevPeriodRevenue, $periodRevenue),
                        'icon' => 'DollarSign',
                    ],
                    [
                        'label' => 'Orders',
                        'subtitle' => "Last {$days} days",
                        'value' => $periodOrders,
                        'format' => 'number',
                        'change' => $this->pctChange($prevPeriodOrders, $periodOrders),
                        'icon' => 'ShoppingCart',
                    ],
                    [
                        'label' => 'Avg Order Value',
                        'subtitle' => "Last {$days} days",
                        'value' => (float) $periodAov,
                        'format' => 'currency',
                        'change' => $this->pctChange($prevPeriodAov, $periodAov),
                        'icon' => 'TrendingUp',
                    ],
                    [
                        'label' => 'Conversion Rate',
                        'subtitle' => 'This month',
                        'value' => $conversionRate,
                        'format' => 'percent',
                        'change' => $conversionRate - $previousConversionRate,
                        'icon' => 'BarChart3',
                    ],
                    [
                        'label' => 'Total Customers',
                        'subtitle' => 'All time',
                        'value' => $totalCustomers,
                        'format' => 'number',
                        'change' => $this->pctChange($prevMonthCustomers, $totalCustomers),
                        'icon' => 'Users',
                    ],
                    [
                        'label' => 'Active Products',
                        'subtitle' => 'Published',
                        'value' => $activeProducts,
                        'format' => 'number',
                        'change' => 0,
                        'icon' => 'Package',
                    ],
                ],
                'revenue_over_time' => $this->getRevenueOverTime($days),
                'recent_orders' => $this->getRecentOrders(5),
                'top_products' => $this->getTopProducts(5),
                'order_status_counts' => Order::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
                'total_revenue' => (float) Order::whereIn('status', $completedStatuses)->sum('total'),
                'total_orders' => Order::count(),
                'total_products' => Product::count(),
                'total_customers' => $totalCustomers,
                'active_products' => $activeProducts,
                'abandoned_carts' => Cart::whereNotNull('abandoned_at')->whereNull('recovered_at')->whereNull('converted_to_order_id')->count(),
                'recovered_carts' => Cart::whereNotNull('recovered_at')->count(),
                'customer_insights' => [
                    'total_customers' => $totalCustomers,
                    'retention_rate' => $retentionRate,
                    'prev_retention_rate' => $prevRetentionRate,
                    'avg_ltv' => $customerLtv,
                    'growth' => $customerGrowth,
                ],
                'revenue_breakdown' => $revenueBreakdown,
                'low_stock_products' => Product::lowStock()
                    ->orderBy('stock_quantity')
                    ->take(5)
                    ->get(['id', 'name', 'slug', 'thumbnail_url', 'stock_quantity', 'low_stock_threshold', 'track_stock'])
                    ->toArray(),
                'pending_reviews' => ProductReview::with(['product:id,name,slug,thumbnail_url', 'user:id,name'])
                    ->where('is_approved', false)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->toArray(),
            ];
        });
    }

    public function clearStatsCache(): void
    {
        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.analytics');
    }

    private function getRevenueOverTime(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = Order::whereIn('status', ['completed', 'processing'])
                ->whereDate('created_at', $date)
                ->sum('total');
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => (float) $revenue,
                'orders' => $count,
            ];
        }

        return $data;
    }

    private function getRecentOrders(int $limit): array
    {
        return Order::with('items')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name ?? ($order->user?->name ?? 'Guest'),
                'total' => (float) $order->total,
                'status' => $order->status,
                'items_count' => $order->items->sum('quantity'),
                'created_at' => $order->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function getTopProducts(int $limit): array
    {
        return OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total) as total_revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_qty' => (int) $item->total_qty,
                'total_revenue' => (float) $item->total_revenue,
            ])
            ->toArray();
    }

    private function pctChange(float|int $previous, float|int $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
