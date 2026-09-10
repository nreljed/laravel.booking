<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Nreljed\BookingModule\Booking\BookingServiceProvider;
use Nreljed\BookingModule\Properties\PropertiesServiceProvider;
use Nreljed\DashboardCore\DashboardCoreServiceProvider;
use Nreljed\DashboardCore\Posts\PostsServiceProvider;
use Nreljed\DashboardCore\Providers\PermissionGateServiceProvider;

return array_filter([

    /*
     * Package Service Providers...
     */
    DashboardCoreServiceProvider::class,
    BookingServiceProvider::class,
    PropertiesServiceProvider::class,
    PostsServiceProvider::class,
    PermissionGateServiceProvider::class,

    /*
     * Application Service Providers...
     */
    AppServiceProvider::class,
    class_exists(TelescopeApplicationServiceProvider::class)
        ? TelescopeServiceProvider::class
        : null,

]);
