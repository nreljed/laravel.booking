<?php

use App\Booking\BookingServiceProvider;
use App\Properties\PropertiesServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Nreljed\DashboardCore\Blog\BlogServiceProvider;
use Nreljed\DashboardCore\DashboardCoreServiceProvider;
use Nreljed\DashboardCore\Providers\PermissionGateServiceProvider;

return [

    /*
     * Package Service Providers...
     */
    DashboardCoreServiceProvider::class,
    BlogServiceProvider::class,
    PermissionGateServiceProvider::class,

    /*
     * Application Service Providers...
     */
    AppServiceProvider::class,
    TelescopeServiceProvider::class,
    BookingServiceProvider::class,
    PropertiesServiceProvider::class,

];
