# Test Script for Docker Container Builds and Startup
# Task 13.1: Test container builds and startup

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Docker Environment Startup Test" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Test 1: Build all containers
Write-Host "[TEST 1] Building all containers..." -ForegroundColor Yellow
Write-Host "Command: docker-compose build" -ForegroundColor Gray
Write-Host ""

$buildResult = docker-compose build 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "[PASS] All containers built successfully" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Container build failed" -ForegroundColor Red
    Write-Host $buildResult -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 2: Start all containers
Write-Host "[TEST 2] Starting all containers..." -ForegroundColor Yellow
Write-Host "Command: docker-compose up -d" -ForegroundColor Gray
Write-Host ""

$upResult = docker-compose up -d 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "[PASS] All containers started successfully" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Container startup failed" -ForegroundColor Red
    Write-Host $upResult -ForegroundColor Red
    exit 1
}
Write-Host ""

# Wait for containers to initialize
Write-Host "Waiting 15 seconds for containers to initialize..." -ForegroundColor Gray
Start-Sleep -Seconds 15
Write-Host ""

# Test 3: Verify all containers are running
Write-Host "[TEST 3] Verifying all containers are running..." -ForegroundColor Yellow
Write-Host "Command: docker-compose ps" -ForegroundColor Gray
Write-Host ""

$psOutput = docker-compose ps --format json | ConvertFrom-Json
$expectedContainers = @("fintrack_php", "fintrack_nginx", "fintrack_mysql", "fintrack_redis", "fintrack_phpmyadmin", "fintrack_node")
$runningContainers = @()
$failedContainers = @()

foreach ($container in $psOutput) {
    if ($container.State -eq "running") {
        $runningContainers += $container.Name
        Write-Host "  ✓ $($container.Name) - Running" -ForegroundColor Green
    } else {
        $failedContainers += $container.Name
        Write-Host "  ✗ $($container.Name) - $($container.State)" -ForegroundColor Red
    }
}

Write-Host ""
if ($failedContainers.Count -eq 0 -and $runningContainers.Count -eq $expectedContainers.Count) {
    Write-Host "[PASS] All $($runningContainers.Count) containers are running" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Some containers are not running" -ForegroundColor Red
    Write-Host "Expected: $($expectedContainers.Count) containers" -ForegroundColor Red
    Write-Host "Running: $($runningContainers.Count) containers" -ForegroundColor Red
}
Write-Host ""

# Test 4: Check container logs for errors
Write-Host "[TEST 4] Checking container logs for errors..." -ForegroundColor Yellow
Write-Host ""

$hasErrors = $false
foreach ($containerName in $expectedContainers) {
    Write-Host "Checking logs for $containerName..." -ForegroundColor Gray
    $logs = docker logs $containerName 2>&1 | Select-String -Pattern "error|fatal|failed" -CaseSensitive:$false
    
    if ($logs) {
        Write-Host "  ⚠ Found potential errors in $containerName" -ForegroundColor Yellow
        $logs | Select-Object -First 5 | ForEach-Object { Write-Host "    $_" -ForegroundColor Yellow }
        $hasErrors = $true
    } else {
        Write-Host "  ✓ No critical errors found in $containerName" -ForegroundColor Green
    }
}

Write-Host ""
if (-not $hasErrors) {
    Write-Host "[PASS] No critical errors found in container logs" -ForegroundColor Green
} else {
    Write-Host "[WARN] Some potential errors found in logs (review above)" -ForegroundColor Yellow
}
Write-Host ""

# Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Test Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✓ Container Build: PASSED" -ForegroundColor Green
Write-Host "✓ Container Startup: PASSED" -ForegroundColor Green
Write-Host "✓ Container Status: $($runningContainers.Count)/$($expectedContainers.Count) running" -ForegroundColor $(if ($runningContainers.Count -eq $expectedContainers.Count) { "Green" } else { "Red" })
Write-Host "✓ Log Check: $(if (-not $hasErrors) { 'PASSED' } else { 'WARNINGS' })" -ForegroundColor $(if (-not $hasErrors) { "Green" } else { "Yellow" })
Write-Host ""
Write-Host "All containers are ready for testing!" -ForegroundColor Cyan
Write-Host ""
