<?php

namespace App\Console\Commands;

use App\Booking\BookingServiceProvider;
use App\Models\Permission;
use App\Models\Role;
use App\Posts\PostsServiceProvider;
use App\Properties\PropertiesServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync feature permissions into the permissions table and assign to admin role';

    public function handle(): int
    {
        if (! Schema::hasTable('permissions')) {
            $this->error('Permissions table does not exist. Run migrations first.');

            return Command::FAILURE;
        }

        $existing = Permission::pluck('slug')->toArray();
        $synced = 0;

        foreach ([BookingServiceProvider::class, PropertiesServiceProvider::class, PostsServiceProvider::class] as $provider) {
            foreach ($provider::permissions() as $perm) {
                if (! in_array($perm['slug'], $existing)) {
                    Permission::create([
                        'name' => $perm['name'],
                        'slug' => $perm['slug'],
                        'description' => $perm['description'] ?? '',
                        'guard_name' => 'web',
                    ]);
                    $this->line("  Created: {$perm['slug']}");
                    $synced++;
                }
            }
        }

        $this->info("Synced {$synced} new permission(s).");

        $permSlugs = [];
        foreach ([BookingServiceProvider::class, PropertiesServiceProvider::class, PostsServiceProvider::class] as $provider) {
            foreach ($provider::permissions() as $perm) {
                $permSlugs[] = $perm['slug'];
            }
        }
        $featurePerms = Permission::whereIn('slug', $permSlugs)->pluck('id');

        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $existingIds = $admin->permissions()->pluck('permissions.id')->toArray();
            $newIds = $featurePerms->diff($existingIds)->values()->toArray();

            if (! empty($newIds)) {
                $admin->permissions()->attach($newIds);
                $this->line('  Assigned '.count($newIds).' feature permission(s) to admin role.');
            } else {
                $this->line('  No new feature permissions to assign to admin role.');
            }
        }

        return Command::SUCCESS;
    }
}
