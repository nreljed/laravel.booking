<?php

use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Nreljed\BookingModule\Booking\BookingServiceProvider;
use Nreljed\BookingModule\Properties\PropertiesServiceProvider;
use Nreljed\DashboardCore\DashboardCoreServiceProvider;
use Nreljed\DashboardCore\Posts\PostsServiceProvider;
use Nreljed\DashboardCore\Providers\PermissionGateServiceProvider;
use Nreljed\DashboardCore\PublicSite\PublicSiteServiceProvider;

return array_filter([

    /*
     * Package Service Providers...
     */
    DashboardCoreServiceProvider::class,
    PostsServiceProvider::class,
    PublicSiteServiceProvider::class,
    BookingServiceProvider::class,
    PropertiesServiceProvider::class,
    PermissionGateServiceProvider::class,

    /*
     * Application Service Providers...
     */
    AppServiceProvider::class,
    class_exists(TelescopeApplicationServiceProvider::class)
        ? TelescopeServiceProvider::class
        : null,

]);
