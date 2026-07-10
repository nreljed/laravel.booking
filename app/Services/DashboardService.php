<?php

namespace App\Services;

use App\Booking\BookingServiceProvider;
use App\Models\User;
use App\Posts\PostsServiceProvider;
use App\Properties\PropertiesServiceProvider;
use Illuminate\Support\Facades\Cache;

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

            try {
                $moduleData = [];
                foreach ([
                    'bookings' => BookingServiceProvider::getDashboardData(),
                    'properties' => PropertiesServiceProvider::getDashboardData(),
                    'posts' => PostsServiceProvider::getDashboardData(),
                ] as $slug => $data) {
                    if (! empty($data)) {
                        $moduleData[$slug] = $data;
                    }
                }

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
            } catch (\Throwable) {
            }

            return $stats;
        });
    }

    public function getAnalytics(int $days = 14): array
    {
        return Cache::remember('dashboard.analytics', 300, function () use ($days) {
            $startDate = now()->subDays($days);

            return [
                'total_customers' => User::count(),
                'new_users' => User::where('created_at', '>=', $startDate)->count(),
                'recent_bookings' => [],
                'recent_properties' => [],
                'recent_posts' => [],
            ];
        });
    }

    public function clearStatsCache(): void
    {
        Cache::forget('dashboard.stats');
        Cache::forget('dashboard.analytics');
    }
}
