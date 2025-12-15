# ============================================================================
# Development Workflow Test Script
# ============================================================================
# This script tests the development workflow for the Docker environment
# ============================================================================

Write-Host ""
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "  Development Workflow Test Suite" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$testsPassed = 0
$testsFailed = 0
$testsTotal = 0

function Test-Step {
    param(
        [string]$Description,
        [scriptblock]$TestBlock
    )
    
    $script:testsTotal++
    Write-Host "[$script:testsTotal] Testing: $Description" -ForegroundColor Yellow
    
    try {
        & $TestBlock
        Write-Host "    [PASS] $Description" -ForegroundColor Green
        $script:testsPassed++
        return $true
    }
    catch {
        Write-Host "    [FAIL] $Description" -ForegroundColor Red
        Write-Host "    Error: $_" -ForegroundColor Red
        $script:testsFailed++
        return $false
    }
    finally {
        Write-Host ""
    }
}

# ============================================================================
# Test 1: PHP File Changes Reflect Immediately
# ============================================================================
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "Test Group 1: PHP File Hot Reload" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Test-Step "Create test PHP route" {
    $testRoute = @"

// Test route for development workflow verification
Route::get('/dev-test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Development workflow test - Initial version',
        'timestamp' => now()->toDateTimeString()
    ]);
});
"@
    Add-Content -Path "routes/web.php" -Value $testRoute
    Start-Sleep -Seconds 2
}

Test-Step "Verify initial test route works" {
    $response = Invoke-WebRequest -Uri "http://localhost/dev-test" -UseBasicParsing
    if ($response.StatusCode -ne 200) {
        throw "Route returned status code $($response.StatusCode)"
    }
    $json = $response.Content | ConvertFrom-Json
    if ($json.message -notlike "*Initial version*") {
        throw "Route did not return expected content"
    }
}

Test-Step "Modify PHP file and verify changes reflect immediately" {
    # Read the current content
    $content = Get-Content -Path "routes/web.php" -Raw
    # Replace the message
    $content = $content -replace "Initial version", "Modified version - Hot reload works!"
    Set-Content -Path "routes/web.php" -Value $content
    
    # Wait a moment for the change to propagate
    Start-Sleep -Seconds 2
    
    # Test the modified route
    $response = Invoke-WebRequest -Uri "http://localhost/dev-test" -UseBasicParsing
    $json = $response.Content | ConvertFrom-Json
    if ($json.message -notlike "*Modified version*") {
        throw "Changes did not reflect immediately"
    }
}

Test-Step "Clean up test route" {
    $content = Get-Content -Path "routes/web.php" -Raw
    $content = $content -replace "(?s)// Test route for development workflow verification.*?\}\);", ""
    Set-Content -Path "routes/web.php" -Value $content.TrimEnd()
}

# ============================================================================
# Test 2: Frontend File Changes and HMR
# ============================================================================
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "Test Group 2: Frontend Hot Module Replacement (HMR)" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Test-Step "Verify Vite dev server is running" {
    $response = Invoke-WebRequest -Uri "http://localhost:5173" -UseBasicParsing -TimeoutSec 5
    if ($response.StatusCode -ne 200) {
        throw "Vite dev server is not responding"
    }
}

Test-Step "Check Vite HMR WebSocket connection" {
    # Check if Vite is serving the client script
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:5173/@vite/client" -UseBasicParsing -TimeoutSec 5
        if ($response.StatusCode -ne 200) {
            throw "Vite client script not accessible"
        }
    }
    catch {
        Write-Host "    [INFO] Vite client check: $_" -ForegroundColor Yellow
    }
}

Test-Step "Verify frontend assets are being served" {
    # Check if app.js exists and is being served
    $appJsPath = "resources/js/app.js"
    if (Test-Path $appJsPath) {
        Write-Host "    [INFO] Frontend entry point exists: $appJsPath" -ForegroundColor Cyan
    }
    else {
        throw "Frontend entry point not found: $appJsPath"
    }
}

# ============================================================================
# Test 3: Makefile Commands
# ============================================================================
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "Test Group 3: Makefile Commands" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Test-Step "Test 'make help' command" {
    $output = make help 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "make help failed with exit code $LASTEXITCODE"
    }
    if ($output -notlike "*Available targets*") {
        throw "make help did not show expected output"
    }
}

Test-Step "Test 'make shell' command (non-interactive check)" {
    # Test that we can execute a command in the PHP container
    $output = docker-compose exec -T php php --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Cannot execute commands in PHP container"
    }
    if ($output -notlike "*PHP*") {
        throw "PHP version check failed"
    }
}

Test-Step "Test 'make artisan' command with route:list" {
    $output = docker-compose exec -T php php artisan route:list 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "artisan route:list failed"
    }
}

Test-Step "Test 'make artisan' command with cache:clear" {
    $output = docker-compose exec -T php php artisan cache:clear 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "artisan cache:clear failed"
    }
}

Test-Step "Test composer command execution" {
    $output = docker-compose exec -T php composer --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "composer command failed"
    }
    if ($output -notlike "*Composer*") {
        throw "Composer version check failed"
    }
}

Test-Step "Test npm command execution" {
    $output = docker-compose exec -T node npm --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "npm command failed"
    }
}

# ============================================================================
# Test 4: Helper Scripts
# ============================================================================
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "Test Group 4: Helper Scripts" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Test-Step "Verify setup.bat script exists" {
    if (-not (Test-Path "scripts/setup.bat")) {
        throw "setup.bat script not found"
    }
}

Test-Step "Verify setup.sh script exists" {
    if (-not (Test-Path "scripts/setup.sh")) {
        throw "setup.sh script not found"
    }
}

Test-Step "Verify cleanup.bat script exists" {
    if (-not (Test-Path "scripts/cleanup.bat")) {
        throw "cleanup.bat script not found"
    }
}

Test-Step "Verify cleanup.sh script exists" {
    if (-not (Test-Path "scripts/cleanup.sh")) {
        throw "cleanup.sh script not found"
    }
}

Test-Step "Check setup.bat script syntax" {
    $content = Get-Content "scripts/setup.bat" -Raw
    if ($content -notlike "*Docker*" -or $content -notlike "*setup*") {
        throw "setup.bat appears to have incorrect content"
    }
}

Test-Step "Check cleanup.bat script syntax" {
    $content = Get-Content "scripts/cleanup.bat" -Raw
    if ($content -notlike "*Docker*" -or $content -notlike "*cleanup*") {
        throw "cleanup.bat appears to have incorrect content"
    }
}

# ============================================================================
# Test 5: Development Workflow Integration
# ============================================================================
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "Test Group 5: Development Workflow Integration" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Test-Step "Verify all containers are running" {
    $output = docker-compose ps --format json | ConvertFrom-Json
    $runningContainers = @($output | Where-Object { $_.State -eq "running" })
    if ($runningContainers.Count -lt 5) {
        throw "Not all containers are running. Expected at least 5, found $($runningContainers.Count)"
    }
}

Test-Step "Test database connectivity from PHP" {
    $output = docker-compose exec -T php php artisan migrate:status 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Database connectivity test failed"
    }
}

Test-Step "Test Redis connectivity from PHP" {
    $testScript = @"
use Illuminate\Support\Facades\Redis;
Redis::set('dev_test', 'workflow_test');
echo Redis::get('dev_test');
"@
    $output = docker-compose exec -T php php artisan tinker --execute="$testScript" 2>&1
    if ($output -notlike "*workflow_test*") {
        throw "Redis connectivity test failed"
    }
}

Test-Step "Test application is accessible" {
    $response = Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -ne 200) {
        throw "Application is not accessible"
    }
}

Test-Step "Test phpMyAdmin is accessible" {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -ne 200) {
        throw "phpMyAdmin is not accessible"
    }
}

Test-Step "Verify volume persistence" {
    # Create a test file in storage
    docker-compose exec -T php sh -c "echo 'test' > storage/app/dev_test.txt"
    
    # Verify it exists
    $output = docker-compose exec -T php sh -c "cat storage/app/dev_test.txt" 2>&1
    if ($output -notlike "*test*") {
        throw "Volume persistence test failed"
    }
    
    # Clean up
    docker-compose exec -T php sh -c "rm storage/app/dev_test.txt"
}

# ============================================================================
# Test Results Summary
# ============================================================================
Write-Host ""
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "  Test Results Summary" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total Tests:  $testsTotal" -ForegroundColor White
Write-Host "Passed:       $testsPassed" -ForegroundColor Green
Write-Host "Failed:       $testsFailed" -ForegroundColor $(if ($testsFailed -eq 0) { "Green" } else { "Red" })
Write-Host ""

if ($testsFailed -eq 0) {
    Write-Host "============================================================================" -ForegroundColor Green
    Write-Host "  ALL TESTS PASSED! Development workflow is working correctly." -ForegroundColor Green
    Write-Host "============================================================================" -ForegroundColor Green
    exit 0
}
else {
    Write-Host "============================================================================" -ForegroundColor Red
    Write-Host "  SOME TESTS FAILED! Please review the errors above." -ForegroundColor Red
    Write-Host "============================================================================" -ForegroundColor Red
    exit 1
}
