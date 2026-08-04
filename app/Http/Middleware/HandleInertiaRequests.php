<?php

namespace App\Http\Middleware;

use App\Booking\BookingServiceProvider;
use App\Properties\PropertiesServiceProvider;
use Nreljed\DashboardCore\Http\Middleware\HandleInertiaRequests as BaseHandleInertiaRequests;

class HandleInertiaRequests extends BaseHandleInertiaRequests
{
    private static function providers(): array
    {
        return [
            BookingServiceProvider::class,
            PropertiesServiceProvider::class,
        ];
    }
}
