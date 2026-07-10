<?php

use App\Booking\BookingServiceProvider;
use App\Posts\PostsServiceProvider;
use App\Properties\PropertiesServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\PermissionGateServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

return array_filter([
    AppServiceProvider::class,
    BookingServiceProvider::class,
    PropertiesServiceProvider::class,
    PostsServiceProvider::class,
    PermissionGateServiceProvider::class,
    class_exists(TelescopeApplicationServiceProvider::class)
        ? TelescopeServiceProvider::class
        : null,
]);
