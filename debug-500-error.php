<?php
/**
 * Debug script to identify 500 error causes
 * Run this in the container to diagnose issues
 */

echo "=== Laravel 500 Error Debug Script ===\n\n";

// Check if we're in a Laravel environment
if (!file_exists('artisan')) {
    echo "❌ Not in Laravel root directory\n";
    exit(1);
}

echo "✅ Laravel application found\n\n";

// Check critical files
$criticalFiles = [
    '.env' => 'Environment file',
    'bootstrap/app.php' => 'Bootstrap file',
    'config/app.php' => 'App configuration',
    'storage/logs' => 'Log directory',
    'bootstrap/cache' => 'Cache directory',
];

echo "=== File System Check ===\n";
foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description: $file\n";
    } else {
        echo "❌ Missing $description: $file\n";
    }
}

// Check permissions
echo "\n=== Permission Check ===\n";
$directories = ['storage', 'bootstrap/cache'];
foreach ($directories as $dir) {
    if (is_writable($dir)) {
        echo "✅ $dir is writable\n";
    } else {
        echo "❌ $dir is not writable\n";
    }
}

// Check environment variables
echo "\n=== Environment Variables ===\n";
$envVars = [
    'APP_KEY' => 'Application encryption key',
    'APP_ENV' => 'Application environment',
    'APP_DEBUG' => 'Debug mode',
    'DATABASE_URL' => 'Database connection',
    'LOG_CHANNEL' => 'Logging channel',
];

foreach ($envVars as $var => $description) {
    $value = getenv($var);
    if ($value !== false && $value !== '') {
        if ($var === 'APP_KEY') {
            echo "✅ $description: [SET]\n";
        } elseif ($var === 'DATABASE_URL') {
            echo "✅ $description: [SET]\n";
        } else {
            echo "✅ $description: $value\n";
        }
    } else {
        echo "❌ Missing $description: $var\n";
    }
}

// Try to bootstrap Laravel
echo "\n=== Laravel Bootstrap Test ===\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel bootstrap successful\n";
    
    // Try to get config
    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        echo "✅ Laravel kernel bootstrap successful\n";
        
        // Check database connection if configured
        if (getenv('DATABASE_URL')) {
            try {
                $pdo = $app->make('db')->connection()->getPdo();
                echo "✅ Database connection successful\n";
            } catch (Exception $e) {
                echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Laravel kernel bootstrap failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
}

// Check recent logs
echo "\n=== Recent Log Entries ===\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -10);
    foreach ($recentLogs as $log) {
        echo trim($log) . "\n";
    }
} else {
    echo "No log file found at $logFile\n";
}

echo "\n=== Debug Complete ===\n";