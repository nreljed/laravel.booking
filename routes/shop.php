<?php

use Illuminate\Support\Facades\Route;

// Customer portal (auth required, no special permission)
Route::middleware(['web', 'auth', 'verified'])
    ->prefix('/dashboard')->as('dashboard.')
    ->group(base_path('app/Shop/Routes/customer-portal.php'));

// Admin management (auth + shop.view permission)
Route::middleware(['web', 'auth', 'verified', 'permission:shop.view'])
    ->prefix('/dashboard/shop')->as('dashboard.shop.')
    ->group(base_path('app/Shop/Routes/web.php'));

// Admin shop settings (admin + shop.view permission)
Route::middleware(['web', 'auth', 'verified', 'admin', 'permission:shop.view'])
    ->prefix('/admin/shop')->as('admin.shop.')
    ->group(base_path('app/Shop/Routes/admin.php'));

// Public storefront / guest routes (no auth)
Route::middleware(['web'])
    ->group(function () {
        $p = 'app/Shop/Routes/';
        require base_path($p.'store-checkout.php');
        require base_path($p.'recovery.php');
        require base_path($p.'store-cart.php');
        require base_path($p.'store-wishlist.php');
        require base_path($p.'storefront.php');
        require base_path($p.'store-notify.php');
    });
