<?php

use Illuminate\Support\Facades\Route;

// Shop JSON API (sanctum / stateful)
Route::middleware(['api'])
    ->prefix('api')->as('api.')
    ->group(function () {
        $p = 'app/Shop/Routes/';
        require base_path($p.'api.php');
        require base_path($p.'api-auth.php');
    });
