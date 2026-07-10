<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Migrate old crm_user role → customer (for existing installs)
        $oldCrmUser = Role::where('slug', 'crm_user')->first();
        if ($oldCrmUser) {
            $customerRole = Role::where('slug', 'customer')->first();
            if ($customerRole) {
                // Move users from old crm_user to customer
                foreach ($oldCrmUser->users as $user) {
                    if (! $user->hasRole('customer')) {
                        $user->roles()->attach($customerRole->id);
                    }
                }
                $oldCrmUser->users()->detach();
            }
            $oldCrmUser->permissions()->detach();
            $oldCrmUser->delete();
        }

        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Create, edit, delete users'],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'description' => 'Change application settings'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'description' => 'Assign and manage roles'],
            ['name' => 'View System Health', 'slug' => 'view-system-health', 'description' => 'View system diagnostics'],
            ['name' => 'Manage Options', 'slug' => 'manage_options', 'description' => 'Module-level settings and system operations (admin only)'],
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'description' => 'Access the main dashboard'],
            ['name' => 'View Profile', 'slug' => 'view-profile', 'description' => 'View own profile'],
            ['name' => 'Edit Profile', 'slug' => 'edit-profile', 'description' => 'Edit own name, email, password'],
            ['name' => 'Delete Account', 'slug' => 'delete-account', 'description' => 'Delete own account'],
            ['name' => 'View Notifications', 'slug' => 'view-notifications', 'description' => 'View notifications'],
            ['name' => 'Manage Notifications', 'slug' => 'manage-notifications', 'description' => 'Mark read, delete notifications'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Full access to everything']
        );

        $customerRole = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'description' => 'Account management: profile, notifications, account settings']
        );

        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        $corePerms = Permission::whereIn('slug', [
            'view-dashboard',
            'view-profile',
            'edit-profile',
            'delete-account',
            'view-notifications',
            'manage-notifications',
        ])->pluck('id');

        $customerRole->permissions()->sync($corePerms);
    }
}
