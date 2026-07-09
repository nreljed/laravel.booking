<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = $_GET['key'] ?? '';
$expected = $app['config']['app.deploy_token'] ?? '';

if (!$expected || !hash_equals($expected, $key)) {
    http_response_code(403);
    exit('Invalid key');
}

$command = $_GET['cmd'] ?? 'list';
header('Content-Type: text/plain');

try {
    $exitCode = $kernel->call($command, ['--force' => true]);
    echo "Exit code: $exitCode\n\n";
    echo $kernel->output();
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage();
}
