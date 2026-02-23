# PowerShell script to get network information for accessing the application

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Personal Financial Tracker - Network Info" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get IP Address
Write-Host "Finding your server's IP address..." -ForegroundColor Yellow
Write-Host ""

$ipAddresses = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.IPAddress -notlike "127.*" -and 
    $_.IPAddress -notlike "169.254.*" -and
    $_.PrefixOrigin -eq "Dhcp" -or $_.PrefixOrigin -eq "Manual"
}

if ($ipAddresses) {
    Write-Host "Your IP Address(es):" -ForegroundColor Green
    foreach ($ip in $ipAddresses) {
        $adapter = Get-NetAdapter | Where-Object { $_.ifIndex -eq $ip.InterfaceIndex }
        Write-Host "  - $($ip.IPAddress) ($($adapter.Name))" -ForegroundColor White
    }
    
    $primaryIP = $ipAddresses[0].IPAddress
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Access URLs:" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Main Application:  http://$primaryIP" -ForegroundColor White
    Write-Host "phpMyAdmin:        http://${primaryIP}:8080" -ForegroundColor White
    Write-Host ""
    
    # Check if Docker is running
    Write-Host "Checking Docker status..." -ForegroundColor Yellow
    try {
        $dockerStatus = docker-compose ps 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✓ Docker containers are running" -ForegroundColor Green
            Write-Host ""
            docker-compose ps
        } else {
            Write-Host "✗ Docker containers are not running" -ForegroundColor Red
            Write-Host "Run 'docker-compose up -d' to start them" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "✗ Docker is not running or not installed" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Next Steps:" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "1. Update .env file:" -ForegroundColor White
    Write-Host "   APP_URL=http://$primaryIP" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Restart Docker containers:" -ForegroundColor White
    Write-Host "   docker-compose down && docker-compose up -d" -ForegroundColor Gray
    Write-Host ""
    Write-Host "3. Access from other devices on your network:" -ForegroundColor White
    Write-Host "   http://$primaryIP" -ForegroundColor Gray
    Write-Host ""
    Write-Host "4. If you can't access, check Windows Firewall:" -ForegroundColor White
    Write-Host "   - Allow port 80 (HTTP)" -ForegroundColor Gray
    Write-Host "   - Allow port 8080 (phpMyAdmin)" -ForegroundColor Gray
    Write-Host ""
    
    # Test if port 80 is listening
    Write-Host "Testing if port 80 is accessible..." -ForegroundColor Yellow
    try {
        $connection = Test-