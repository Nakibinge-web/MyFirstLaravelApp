<?php

// Performance debugging script
$start = microtime(true);

echo "🔍 Performance Debug Report\n";
echo "==========================\n\n";

// Test Redis connection speed
$redisStart = microtime(true);
try {
    $redis = new Redis();
    $redis->connect('redis', 6379);
    $redis->ping();
    $redisTime = (microtime(true) - $redisStart) * 1000;
    echo "✅ Redis Connection: {$redisTime}ms\n";
} catch (Exception $e) {
    echo "❌ Redis Connection Failed: " . $e->getMessage() . "\n";
}

// Test database connection speed
$dbStart = microtime(true);
try {
    $pdo = new PDO('mysql:host=mysql;dbname=personal_financial_tracker', 'root', 'root');
    $pdo->query('SELECT 1');
    $dbTime = (microtime(true) - $dbStart) * 1000;
    echo "✅ Database Connection: {$dbTime}ms\n";
} catch (Exception $e) {
    echo "❌ Database Connection Failed: " . $e->getMessage() . "\n";
}

// Test file system speed
$fsStart = microtime(true);
file_put_contents('/tmp/test.txt', 'test');
unlink('/tmp/test.txt');
$fsTime = (microtime(true) - $fsStart) * 1000;
echo "✅ File System: {$fsTime}ms\n";

// Memory usage
$memory = memory_get_usage(true) / 1024 / 1024;
$peakMemory = memory_get_peak_usage(true) / 1024 / 1024;
echo "📊 Memory Usage: {$memory}MB (Peak: {$peakMemory}MB)\n";

$totalTime = (microtime(true) - $start) * 1000;
echo "\n⏱️  Total Debug Time: {$totalTime}ms\n";