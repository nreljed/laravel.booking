<?php

namespace App\Providers;

use App\Booking\Models\Booking;
use App\Policies\BookingPolicy;
use App\Policies\PropertyPolicy;
use App\Properties\Models\Property;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
