# Master test script for Docker environment validation
Write-Host "========================================" -ForegroundColor Magenta
Write-Host "Docker Environment Validation Test Suite" -ForegroundColor Magenta
Write-Host "========================================" -ForegroundColor Magenta

# Test 13.1: Container builds and startup
Write-Host "`n### Test 13.1: Container Builds and Startup ###" -ForegroundColor Magenta
& .\test-docker-startup.ps1

# Wait for containers to be fully ready
Write-Host "`nWaiting 30 seconds for all services to be fully ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Test 13.2: Service connectivity
Write-Host "`n### Test 13.2: Service Connectivity ###" -ForegroundColor Magenta
& .\test-service-connectivity.ps1

# Test 13.3: Application functionality
Write-Host "`n### Test 13.3: Application Functionality ###" -ForegroundColor Magenta
& .\test-application-functionality.ps1

# Test 13.4: Volume persistence
Write-Host "`n### Test 13.4: Volume Persistence ###" -ForegroundColor Magenta
& .\test-volume-persistence.ps1

# Test 13.5: Development workflow
Write-Host "`n### Test 13.5: Development Workflow ###" -ForegroundColor Magenta
& .\test-development-workflow.ps1

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "All Docker tests complete!" -ForegroundColor Magenta
Write-Host "========================================" -ForegroundColor Magenta
