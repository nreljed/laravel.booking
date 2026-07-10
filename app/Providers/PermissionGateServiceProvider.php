<?php

namespace App\Providers;

use App\Booking\BookingServiceProvider;
use App\Models\Permission;
use App\Models\User;
use App\Posts\PostsServiceProvider;
use App\Properties\PropertiesServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class PermissionGateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPermissionGates();
    }

    protected function registerPermissionGates(): void
    {
        $permissions = $this->getAllPermissionSlugs();

        foreach ($permissions as $slug) {
            Gate::define($slug, function (User $user) use ($slug) {
                return $user->hasPermission($slug);
            });
        }
    }

    protected function getAllPermissionSlugs(): array
    {
        try {
            $corePermissions = [
                'manage-users',
                'manage-settings',
                'manage-roles',
                'view-system-health',
                'manage_options',
                'view-dashboard',
                'view-profile',
                'edit-profile',
                'delete-account',
                'view-notifications',
                'manage-notifications',
            ];

            $moduleSlugs = [];
            foreach ([BookingServiceProvider::class, PropertiesServiceProvider::class, PostsServiceProvider::class] as $provider) {
                foreach ($provider::permissions() as $perm) {
                    $moduleSlugs[] = $perm['slug'];
                }
            }

            $dbSlugs = [];
            try {
                $dbSlugs = Permission::pluck('slug')->toArray();
            } catch (\Throwable) {
            }

            return array_unique(array_merge($corePermissions, $moduleSlugs, $dbSlugs));
        } catch (\Throwable) {
            return [];
        }
    }
}
