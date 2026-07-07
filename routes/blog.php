<?php

use Illuminate\Support\Facades\Route;

// Blog (Posts) management — auth + posts.view permission
Route::middleware(['web', 'auth', 'verified', 'permission:posts.view'])
    ->prefix('/dashboard/posts')->as('dashboard.posts.')
    ->group(base_path('app/Blog/Routes/web.php'));
