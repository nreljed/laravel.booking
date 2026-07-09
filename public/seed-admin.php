<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$key = $_GET['key'] ?? '';
$expected = $app['config']['app.deploy_token'] ?? '';
if (! $expected || ! hash_equals($expected, $key)) {
    http_response_code(403);
    exit('Invalid key');
}

header('Content-Type: text/plain');

try {
    $app->make(RolesAndPermissionsSeeder::class)->run();
    echo "RolesAndPermissionsSeeder: OK\n";

    $app->make(SettingsSeeder::class)->run();
    echo "SettingsSeeder: OK\n";

    if (! User::where('email', 'nstudio.webdesign@gmail.com')->exists()) {
        $admin = User::create([
            'name' => 'nstudio',
            'email' => 'nstudio.webdesign@gmail.com',
            'username' => 'nstudio',
            'password' => Hash::make('azerty123/*'),
        ]);
        $role = Role::where('slug', 'admin')->first();
        if ($role) {
            $admin->roles()->attach($role->id);
        }
        echo "Admin user created (nstudio.webdesign@gmail.com / azerty123/*)\n";
    } else {
        echo "Admin user already exists\n";
    }

    echo "\nDone!\n";
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage()."\n".$e->getTraceAsString();
}
