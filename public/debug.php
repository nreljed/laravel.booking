<?php

/**
 * Laravel Deployment Health Check
 *
 * A self-contained diagnostic tool for verifying Laravel deployments.
 * Drop this file in your public/ directory and visit /debug.php?key=health
 *
 * Usage:
 *   /debug.php?key=health    — Show all diagnostic info
 *   /debug.php               — Show non-sensitive info only
 *   /debug.php?key=health&json=1 — Machine-readable JSON output
 *
 * Reusable for any Laravel project — just copy public/debug.php to the new project.
 */

// ---- Configuration ----
$accessKey = 'health'; // Change this to a custom key for production
$appRoot = \dirname(__DIR__); // Parent of public/

// ---- Security ----
$authenticated = isset($_GET['key']) && $_GET['key'] === $accessKey;
$jsonMode = isset($_GET['json']) && $_GET['json'] === '1';

// ---- Collectors ----
$results = [
    'status' => 'running',
    'timestamp' => \date('Y-m-d H:i:s'),
    'checks' => [],
];

function check(string $group, string $label, bool $passed, ?string $detail = null): bool {
    global $results;
    $results['checks'][] = [
        'group' => $group,
        'label' => $label,
        'passed' => $passed,
        'detail' => $detail,
    ];
    return $passed;
}

// ===================================================================
// 1. PHP & Server Info
// ===================================================================
check('system', 'PHP Version', true, PHP_VERSION);
check('system', 'Server Software', true, $_SERVER['SERVER_SOFTWARE'] ?? 'unknown');
check('system', 'Server Protocol', true, $_SERVER['SERVER_PROTOCOL'] ?? 'unknown');
check('system', 'HTTP Host', true, $_SERVER['HTTP_HOST'] ?? 'unknown');
check('system', 'Document Root', true, $_SERVER['DOCUMENT_ROOT'] ?? 'unknown');
check('system', 'PHP SAPI', true, PHP_SAPI);
check('system', 'PHP OS', true, PHP_OS);
check('system', 'Memory Limit', true, \ini_get('memory_limit'));
check('system', 'Max Execution Time', true, \ini_get('max_execution_time') . 's');
check('system', 'Upload Max Filesize', true, \ini_get('upload_max_filesize'));
check('system', 'Post Max Size', true, \ini_get('post_max_size'));

// ===================================================================
// 2. Key PHP Extensions
// ===================================================================
$requiredExts = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'gd', 'fileinfo', 'bcmath', 'zip', 'openssl'];
foreach ($requiredExts as $ext) {
    check('extensions', "Extension: $ext", \extension_loaded($ext));
}

// ===================================================================
// 3. App Root & Critical Paths
// ===================================================================
check('filesystem', 'App Root', \is_dir($appRoot), $appRoot);

$paths = [
    'public/index.php' => 'file',
    'artisan' => 'file',
    '.env' => 'file',
    'vendor/autoload.php' => 'file',
    'vendor/composer/autoload_real.php' => 'file',
    'vendor/symfony/polyfill-mbstring/bootstrap.php' => 'file',
    'vendor/symfony/deprecation-contracts/function.php' => 'file',
    'storage' => 'dir',
    'storage/logs' => 'dir',
    'storage/framework' => 'dir',
    'storage/framework/views' => 'dir',
    'bootstrap/cache' => 'dir',
    'public/build/manifest.json' => 'file',
    'public/.htaccess' => 'file',
];

foreach ($paths as $relPath => $type) {
    $fullPath = $appRoot . '/' . $relPath;
    $exists = $type === 'dir' ? \is_dir($fullPath) : \file_exists($fullPath);
    $label = $type === 'dir' ? "Directory: $relPath" : "File: $relPath";
    $detail = null;
    if ($authenticated && $exists) {
        $detail = \realpath($fullPath);
        if ($type === 'file') {
            $detail .= ' (' . \round(\filesize($fullPath) / 1024, 1) . ' KB)';
        }
    }
    check('filesystem', $label, $exists, $detail);
}

// ===================================================================
// 4. Directory Permissions (authenticated only)
// ===================================================================
if ($authenticated) {
    $permPaths = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache', 'vendor'];
    foreach ($permPaths as $relPath) {
        $fullPath = $appRoot . '/' . $relPath;
        if (\is_dir($fullPath)) {
            $perms = \substr(\sprintf('%o', \fileperms($fullPath)), -4);
            $writable = \is_writable($fullPath);
            check('permissions', "Permissions: $relPath", $writable, "mode=$perms writable=" . ($writable ? 'yes' : 'no'));
        }
    }
}

// ===================================================================
// 5. .env File (authenticated only)
// ===================================================================
if ($authenticated) {
    $envPath = $appRoot . '/.env';
    if (\file_exists($envPath)) {
        check('env', '.env exists', true, 'Found at ' . \realpath($envPath));
        $envContent = \file_get_contents($envPath);
        $envVars = [];
        foreach (\explode("\n", $envContent) as $line) {
            $line = \trim($line);
            if ($line === '' || \str_starts_with($line, '#')) continue;
            if (\str_contains($line, '=')) {
                [$key, $val] = \explode('=', $line, 2);
                $key = \trim($key);
                $val = \trim($val);
                // Mask secrets
                if (\in_array($key, ['APP_KEY', 'DB_PASSWORD', 'DEPLOY_TOKEN', 'GITHUB_TOKEN'])) {
                    $val = \strlen($val) > 8 ? \substr($val, 0, 4) . '****' . \substr($val, -4) : '****';
                }
                $envVars[$key] = $val;
            }
        }
        $requiredEnv = ['APP_KEY', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($requiredEnv as $key) {
            $present = isset($envVars[$key]);
            check('env', "ENV: $key", $present, $present ? $envVars[$key] : 'MISSING');
        }
        check('env', 'ENV: DB_PASSWORD', isset($envVars['DB_PASSWORD']), 'present');
    } else {
        check('env', '.env file', false, 'NOT FOUND at ' . $envPath);
    }
}

// ===================================================================
// 6. Vendor Autoload Test
// ===================================================================
$autoloadPath = $appRoot . '/vendor/autoload.php';
if (\file_exists($autoloadPath)) {
    try {
        $loader = require $autoloadPath;
        check('autoload', 'vendor/autoload.php', true, 'Loaded successfully');
        // Verify autoloader finds classes
        if (\class_exists('\Illuminate\Support\Facades\App')) {
            check('autoload', 'Laravel Facade autoload', true);
        } else {
            check('autoload', 'Laravel Facade autoload', false, 'Illuminate\Support\Facades\App not found');
        }
        if (\class_exists('\Symfony\Component\HttpFoundation\Request')) {
            check('autoload', 'Symfony autoload', true);
        } else {
            check('autoload', 'Symfony autoload', false);
        }
    } catch (\Throwable $e) {
        check('autoload', 'vendor/autoload.php', false, 'Error: ' . $e->getMessage());
    }
} else {
    check('autoload', 'vendor/autoload.php', false, 'File does not exist');
}

// ===================================================================
// 7. Laravel Bootstrap (authenticated only)
// ===================================================================
if ($authenticated && \file_exists($autoloadPath)) {
    try {
        $app = require_once $appRoot . '/bootstrap/app.php';
        if ($app instanceof \Illuminate\Foundation\Application) {
            check('laravel', 'Laravel Application boot', true, 'Laravel ' . $app::VERSION);
            check('laravel', 'Laravel Environment', true, $app->environment());
            check('laravel', 'Laravel Debug Mode', true, $app->hasDebugModeEnabled() ? 'ENABLED' : 'DISABLED');
        } else {
            check('laravel', 'Laravel Application boot', false, 'bootstrap/app.php did not return Application');
        }
    } catch (\Throwable $e) {
        check('laravel', 'Laravel Application boot', false, \get_class($e) . ': ' . $e->getMessage());
        // Check if it's a database error — common issue
        if (\str_contains($e->getMessage(), 'database') || \str_contains($e->getMessage(), 'SQLSTATE')) {
            check('laravel', '→ Likely database issue', false, 'Check DB credentials in .env');
        }
        if (\str_contains($e->getMessage(), 'file_put_contents')) {
            check('laravel', '→ Likely storage permissions', false, 'Check storage/ directory is writable');
        }
    }
}

// ===================================================================
// 8. Database Connection (authenticated only)
// ===================================================================
if ($authenticated && \file_exists($autoloadPath)) {
    $dbDriver = 'unknown';
    try {
        // Try to read DB config from .env directly (safer than loading Laravel config)
        $envContent = \file_exists($appRoot . '/.env') ? \file_get_contents($appRoot . '/.env') : '';
        \preg_match('/DB_CONNECTION=(.+)/', $envContent, $m);
        $dbDriver = \trim($m[1] ?? 'mysql');
        \preg_match('/DB_HOST=(.+)/', $envContent, $m);
        $dbHost = \trim($m[1] ?? '127.0.0.1');
        \preg_match('/DB_PORT=(.+)/', $envContent, $m);
        $dbPort = \trim($m[1] ?? '3306');
        \preg_match('/DB_DATABASE=(.+)/', $envContent, $m);
        $dbName = \trim($m[1] ?? '');
        \preg_match('/DB_USERNAME=(.+)/', $envContent, $m);
        $dbUser = \trim($m[1] ?? '');
        \preg_match('/DB_PASSWORD=(.+)/', $envContent, $m);
        $dbPass = \trim($m[1] ?? '');

        if ($dbDriver === 'sqlite') {
            check('database', 'DB Driver', true, 'SQLite');
            if (isset($dbName) && \file_exists($dbName)) {
                check('database', 'SQLite File', true, $dbName);
            } else {
                check('database', 'SQLite File', false, 'Not found: ' . ($dbName ?? 'unknown'));
            }
        } elseif (\in_array($dbDriver, ['mysql', 'mariadb'])) {
            if (\extension_loaded('pdo_mysql')) {
                try {
                    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
                    $pdo = new PDO($dsn, $dbUser, $dbPass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 3,
                    ]);
                    $serverVer = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
                    check('database', "MySQL: $dbName @ $dbHost:$dbPort", true, "Connected! Server: $serverVer");
                } catch (\PDOException $e) {
                    check('database', "MySQL: $dbName @ $dbHost:$dbPort", false, 'Connection failed: ' . $e->getMessage());
                }
            } else {
                check('database', 'MySQL driver', false, 'pdo_mysql extension not loaded');
            }
        } else {
            check('database', 'DB Driver', true, $dbDriver . ' (not tested by this check)');
        }
    } catch (\Throwable $e) {
        check('database', 'Database check', false, $e->getMessage());
    }
}

// ===================================================================
// 9. Build Assets (Vite manifest)
// ===================================================================
$manifestPath = $appRoot . '/public/build/manifest.json';
if (\file_exists($manifestPath)) {
    $manifest = \json_decode(\file_get_contents($manifestPath), true);
    $entryCount = \is_array($manifest) ? \count($manifest) : 0;
    check('assets', 'Vite manifest', $entryCount > 0, "$entryCount entries in manifest.json");
} else {
    check('assets', 'Vite manifest', false, 'public/build/manifest.json not found — run npm run build');
}

// ===================================================================
// 10. Summary
// ===================================================================
$total = \count($results['checks']);
$passed = \count(\array_filter($results['checks'], fn($c) => $c['passed']));
$failed = $total - $passed;
$results['status'] = $failed === 0 ? 'passed' : ($passed > 0 ? 'partial' : 'failed');
$results['summary'] = "$passed/$total checks passed";

// ===================================================================
// Output
// ===================================================================
if ($jsonMode) {
    \header('Content-Type: application/json');
    echo \json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---- HTML Output ----
$red = '#dc2626';
$green = '#16a34a';
$amber = '#d97706';
$gray = '#6b7280';

\header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex,nofollow">
<title>Laravel Health Check</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; color: #111827; padding: 24px; }
  .container { max-width: 960px; margin: 0 auto; }
  h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
  .subtitle { color: #6b7280; font-size: 0.875rem; margin-bottom: 24px; }
  .status-bar { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
  .status-badge { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; }
  .status-passed { background: #dcfce7; color: #166534; }
  .status-partial { background: #fef3c7; color: #92400e; }
  .status-failed { background: #fee2e2; color: #991b1b; }
  .group { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); margin-bottom: 16px; overflow: hidden; }
  .group-header { padding: 12px 16px; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid #e5e7eb; }
  .check { display: flex; align-items: flex-start; padding: 8px 16px; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
  .check:last-child { border-bottom: none; }
  .icon { margin-right: 10px; flex-shrink: 0; width: 18px; height: 18px; text-align: center; line-height: 18px; font-size: 14px; }
  .label { flex: 1; }
  .detail { color: #6b7280; font-size: 0.8125rem; margin-left: 8px; }
  .footer { text-align: center; color: #9ca3af; font-size: 0.75rem; margin-top: 24px; padding: 16px; }
  .authenticated-note { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.875rem; color: #92400e; }
  .json-link { float: right; font-size: 0.75rem; text-decoration: none; color: #6366f1; }
</style>
</head>
<body>
<div class="container">
  <div style="display:flex;align-items:center;justify-content:space-between;">
    <div>
      <h1>🔍 Laravel Health Check</h1>
      <p class="subtitle"><?= $results['timestamp'] ?> — <?= \htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?></p>
    </div>
    <a href="?key=<?= \htmlspecialchars($accessKey) ?>&json=1" class="json-link">View as JSON</a>
  </div>

  <div class="status-bar">
    <div class="status-badge status-<?= $results['status'] ?>">
      <?= $results['summary'] ?>
    </div>
  </div>

  <?php if (!$authenticated) { ?>
  <div class="authenticated-note">
    ⚠️ Showing basic info only. Add <code>?key=health</code> to unlock full diagnostics (DB, .env, permissions, Laravel boot).
  </div>
  <?php } ?>

  <?php
  $groups = [];
  foreach ($results['checks'] as $check) {
      $groups[$check['group']][] = $check;
  }
  ?>

  <?php foreach ($groups as $groupName => $checks) { ?>
  <div class="group">
    <div class="group-header">
      <?= \htmlspecialchars(\ucfirst($groupName)) ?>
      <span style="font-weight:400;color:#6b7280;">
        (<?= \count(\array_filter($checks, fn($c) => $c['passed'])) ?>/<?= \count($checks) ?>)
      </span>
    </div>
    <?php foreach ($checks as $check) { ?>
    <div class="check">
      <span class="icon"><?= $check['passed'] ? '✓' : ($check['detail'] !== null ? '✗' : '—') ?></span>
      <span class="label" style="color: <?= $check['passed'] ? 'inherit' : $red ?>;">
        <?= \htmlspecialchars($check['label']) ?>
        <?php if ($check['detail'] !== null) { ?>
        <span class="detail">— <?= \htmlspecialchars((string)$check['detail']) ?></span>
        <?php } ?>
      </span>
    </div>
    <?php } ?>
  </div>
  <?php } ?>

  <div class="footer">
    Reusable Laravel Health Check — drop in any project's <code>public/</code> directory
  </div>
</div>
</body>
</html>
