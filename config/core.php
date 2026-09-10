<?php

use Nreljed\BookingModule\Booking\BookingServiceProvider;
use Nreljed\BookingModule\Properties\Models\Property;
use Nreljed\BookingModule\Properties\PropertiesServiceProvider;
use Nreljed\DashboardCore\Http\Middleware\HandleInertiaRequests;
use Nreljed\DashboardCore\Models\User;
use Nreljed\DashboardCore\Posts\Models\Post;
use Nreljed\DashboardCore\Posts\PostsServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Load Migrations
    |--------------------------------------------------------------------------
    |
    | Enable package migrations - the booking app does not ship its own
    | equivalents for the core tables.
    |
    */

    'load_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Inertia Request Middleware
    |--------------------------------------------------------------------------
    |
    | Shared props handler appended to the web group. Modules contribute
    | shared props declaratively via FeatureRegistry::getAllSharedData().
    |
    */

    'inertia_middleware' => HandleInertiaRequests::class,

    /*
    |--------------------------------------------------------------------------
    | Feature Service Providers
    |--------------------------------------------------------------------------
    |
    | Self-contained features discovered by the shared FeatureRegistry.
    | Posts is provided by dashboard-core; Bookings and Properties by the
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

    /*
    |--------------------------------------------------------------------------
    | Orphan Media Targets
    |--------------------------------------------------------------------------
    */

    'orphan_media' => [
        'posts' => Post::class,
        'avatars' => User::class,
        'properties' => Property::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Groups
    |--------------------------------------------------------------------------
    |
    | Additional settings groups beyond the core defaults. Booking-specific
    | groups (booking, properties) are declared by the feature providers.
    |
    */

    'settings' => [
        'groups' => [],
    ],

];
