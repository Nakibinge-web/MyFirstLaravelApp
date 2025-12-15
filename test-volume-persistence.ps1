# Test Volume Persistence Script
# This script tests that MySQL and Redis data persists across container restarts

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Testing Docker Volume Persistence" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Ensure containers are running
Write-Host "[Step 1] Checking container status..." -ForegroundColor Yellow
$containers = docker-compose ps --services --filter "status=running"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to check container status" -ForegroundColor Red
    exit 1
}

Write-Host "Running containers:" -ForegroundColor Green
docker-compose ps
Write-Host ""

# Step 2: Create test data in MySQL database
Write-Host "[Step 2] Creating test data in MySQL database..." -ForegroundColor Yellow

# Create a test table and insert test data
$testData = @"
CREATE TABLE IF NOT EXISTS volume_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO volume_test (test_value) VALUES ('persistence_test_$(Get-Date -Format 'yyyyMMdd_HHmmss')');
"@

# Execute SQL commands
docker-compose exec -T mysql mysql -uroot -proot personal_financial_tracker -e "$testData"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to create test data in MySQL" -ForegroundColor Red
    exit 1
}

# Verify data was inserted
Write-Host "Verifying MySQL test data was created..." -ForegroundColor Green
$mysqlCount = docker-compose exec -T mysql mysql -uroot -proot personal_financial_tracker -e "SELECT COUNT(*) as count FROM volume_test;" -s -N
Write-Host "MySQL records created: $mysqlCount" -ForegroundColor Green
Write-Host ""

# Step 3: Create test data in Redis
Write-Host "[Step 3] Creating test data in Redis..." -ForegroundColor Yellow

$redisTestKey = "volume_test_key"
$redisTestValue = "persistence_test_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

docker-compose exec -T redis redis-cli SET $redisTestKey $redisTestValue
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to create test data in Redis" -ForegroundColor Red
    exit 1
}

# Verify Redis data was set
$redisValue = docker-compose exec -T redis redis-cli GET $redisTestKey
Write-Host "Redis test data created: $redisTestKey = $redisValue" -ForegroundColor Green
Write-Host ""

# Step 4: Stop all containers
Write-Host "[Step 4] Stopping all containers..." -ForegroundColor Yellow
docker-compose stop
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to stop containers" -ForegroundColor Red
    exit 1
}

Write-Host "All containers stopped successfully" -ForegroundColor Green
Write-Host ""

# Wait a moment to ensure containers are fully stopped
Start-Sleep -Seconds 3

# Step 5: Restart all containers
Write-Host "[Step 5] Restarting all containers..." -ForegroundColor Yellow
docker-compose up -d
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to restart containers" -ForegroundColor Red
    exit 1
}

Write-Host "Containers restarted successfully" -ForegroundColor Green
Write-Host ""

# Wait for services to be fully ready
Write-Host "Waiting for services to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Step 6: Verify MySQL data persisted
Write-Host "[Step 6] Verifying MySQL data persistence..." -ForegroundColor Yellow

$mysqlCountAfter = docker-compose exec -T mysql mysql -uroot -proot personal_financial_tracker -e "SELECT COUNT(*) as count FROM volume_test;" -s -N
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to query MySQL after restart" -ForegroundColor Red
    exit 1
}

Write-Host "MySQL records after restart: $mysqlCountAfter" -ForegroundColor Green

if ($mysqlCount -eq $mysqlCountAfter) {
    Write-Host "✓ MySQL data persisted successfully!" -ForegroundColor Green
} else {
    Write-Host "✗ MySQL data did NOT persist (before: $mysqlCount, after: $mysqlCountAfter)" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 7: Verify Redis data persisted
Write-Host "[Step 7] Verifying Redis data persistence..." -ForegroundColor Yellow

$redisValueAfter = docker-compose exec -T redis redis-cli GET $redisTestKey
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Failed to query Redis after restart" -ForegroundColor Red
    exit 1
}

Write-Host "Redis value after restart: $redisTestKey = $redisValueAfter" -ForegroundColor Green

if ($redisValue.Trim() -eq $redisValueAfter.Trim()) {
    Write-Host "✓ Redis data persisted successfully!" -ForegroundColor Green
} else {
    Write-Host "✗ Redis data did NOT persist (before: $redisValue, after: $redisValueAfter)" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 8: Cleanup test data
Write-Host "[Step 8] Cleaning up test data..." -ForegroundColor Yellow

docker-compose exec -T mysql mysql -uroot -proot personal_financial_tracker -e "DROP TABLE IF EXISTS volume_test;"
docker-compose exec -T redis redis-cli DEL $redisTestKey

Write-Host "Test data cleaned up successfully" -ForegroundColor Green
Write-Host ""

# Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Volume Persistence Test Results" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✓ MySQL volume persistence: PASSED" -ForegroundColor Green
Write-Host "✓ Redis volume persistence: PASSED" -ForegroundColor Green
Write-Host ""
Write-Host "All volume persistence tests completed successfully!" -ForegroundColor Green
Write-Host ""
