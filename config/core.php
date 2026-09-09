<?php

use Nreljed\DashboardCore\Posts\PostsServiceProvider;
use Nreljed\BookingModule\Booking\BookingServiceProvider;
use Nreljed\BookingModule\Properties\PropertiesServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Service Providers
    |--------------------------------------------------------------------------
    |
    | Self-contained features discovered by the shared FeatureRegistry.
    | Posts is provided by dashboard-core; Bookings + Properties by the
    | booking-module package.
    |
    */

    'features' => [
        PostsServiceProvider::class,
        BookingServiceProvider::class,
        PropertiesServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Defaults
    |--------------------------------------------------------------------------
    |
    | Per-feature enabled/disabled defaults. These sit below the persisted
    | settings table (FeatureSettingsService) but above the fail-closed
    | fallback in InteractsWithFeatures::defaultEnabled(). An admin can
    | toggle any feature at runtime via the modules settings page; this
    | config only controls the out-of-the-box state.
    |
    */

    'features_enabled' => [
        'posts' => true,
        'bookings' => true,
        'properties' => true,
    ],

    'orphan_media_days' => 30,

    'settings' => [
        'groups' => [
            'general',
            'email',
            'booking',
            'properties',
        ],
    ],

];