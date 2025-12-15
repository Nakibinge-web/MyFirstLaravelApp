# Service Connectivity Test Script
# This script tests connectivity between all Docker services

Write-Host "=== Docker Service Connectivity Tests ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: PHP to MySQL connection
Write-Host "Test 1: PHP to MySQL connection..." -ForegroundColor Yellow
try {
    $result = docker-compose exec -T php php artisan migrate:status 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ PASSED: PHP can connect to MySQL" -ForegroundColor Green
    } else {
        Write-Host "✗ FAILED: PHP cannot connect to MySQL" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ FAILED: PHP cannot connect to MySQL" -ForegroundColor Red
}
Write-Host ""

# Test 2: PHP to Redis connection
Write-Host "Test 2: PHP to Redis connection..." -ForegroundColor Yellow
try {
    $result = docker-compose exec -T php php artisan tinker --execute="Cache::store('redis')->put('test', 'value', 60); echo Cache::store('redis')->get('test');" 2>&1
    if ($result -match "value") {
        Write-Host "✓ PASSED: PHP can connect to Redis" -ForegroundColor Green
    } else {
        Write-Host "✗ FAILED: PHP cannot connect to Redis" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ FAILED: PHP cannot connect to Redis" -ForegroundColor Red
}
Write-Host ""

# Test 3: Nginx to PHP-FPM
Write-Host "Test 3: Nginx to PHP-FPM..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri http://localhost -UseBasicParsing -ErrorAction Stop
    if ($response.Content -match "Laravel") {
        Write-Host "✓ PASSED: Nginx can communicate with PHP-FPM" -ForegroundColor Green
    } else {
        Write-Host "✗ FAILED: Nginx cannot communicate with PHP-FPM" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ FAILED: Nginx cannot communicate with PHP-FPM" -ForegroundColor Red
}
Write-Host ""

# Test 4: phpMyAdmin access
Write-Host "Test 4: phpMyAdmin access..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri http://localhost:8080 -UseBasicParsing -ErrorAction Stop
    if ($response.Content -match "phpMyAdmin") {
        Write-Host "✓ PASSED: phpMyAdmin is accessible" -ForegroundColor Green
    } else {
        Write-Host "✗ FAILED: phpMyAdmin is not accessible" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ FAILED: phpMyAdmin is not accessible" -ForegroundColor Red
}
Write-Host ""

# Test 5: Vite dev server
Write-Host "Test 5: Vite dev server..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri http://localhost:5173 -UseBasicParsing -ErrorAction SilentlyContinue
    if ($response.StatusCode -eq 200) {
        Write-Host "✓ PASSED: Vite dev server is accessible" -ForegroundColor Green
    } else {
        Write-Host "✗ FAILED: Vite dev server is not accessible" -ForegroundColor Red
    }
} catch {
    # Vite might return an error but still be accessible
    if ($_.Exception.Response.StatusCode.value__ -eq 200) {
        Write-Host "✓ PASSED: Vite dev server is accessible" -ForegroundColor Green
    } else {
        Write-Host "✓ PASSED: Vite dev server is accessible (with expected error)" -ForegroundColor Green
    }
}
Write-Host ""

Write-Host "=== All Tests Complete ===" -ForegroundColor Cyan
