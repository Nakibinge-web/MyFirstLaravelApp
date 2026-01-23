<?php

echo "Testing Redis Connection Speed...\n";

$start = microtime(true);
$redis = new Redis();
$connected = $redis->connect('redis', 6379);
$connectTime = (microtime(true) - $start) * 1000;

if ($connected) {
    echo "✅ Connected in: {$connectTime}ms\n";
    
    // Test ping
    $pingStart = microtime(true);
    $pong = $redis->ping();
    $pingTime = (microtime(true) - $pingStart) * 1000;
    echo "✅ Ping: {$pingTime}ms\n";
    
    // Test set/get
    $setStart = microtime(true);
    $redis->set('test_key', 'test_value');
    $value = $redis->get('test_key');
    $setGetTime = (microtime(true) - $setStart) * 1000;
    echo "✅ Set/Get: {$setGetTime}ms\n";
    
    // Test Laravel cache
    $cacheStart = microtime(true);
    $redis->set('fintrack_cache_test', 'cached_value');
    $cachedValue = $redis->get('fintrack_cache_test');
    $cacheTime = (microtime(true) - $cacheStart) * 1000;
    echo "✅ Cache Test: {$cacheTime}ms\n";
    
} else {
    echo "❌ Connection failed\n";
}

$totalTime = (microtime(true) - $start) * 1000;
echo "⏱️  Total Time: {$totalTime}ms\n";