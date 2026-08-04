<?php

use App\Booking\BookingServiceProvider;
use App\Properties\PropertiesServiceProvider;
use Nreljed\DashboardCore\Blog\BlogServiceProvider;

return [

    'features' => [
        BlogServiceProvider::class,
        BookingServiceProvider::class,
        PropertiesServiceProvider::class,
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
