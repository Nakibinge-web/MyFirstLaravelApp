@echo off
REM ============================================================================
REM Docker Environment Cleanup Script for Windows
REM ============================================================================
REM This script stops and removes all Docker containers, volumes, and build
REM cache for the Personal Financial Tracker application.
REM ============================================================================

echo.
echo ============================================================================
echo   Personal Financial Tracker - Docker Environment Cleanup
echo ============================================================================
echo.
echo WARNING: This will remove all containers, volumes, and cached data.
echo All database data will be lost!
echo.
set /p confirm="Are you sure you want to continue? (yes/no): "

if /i not "%confirm%"=="yes" (
    echo.
    echo Cleanup cancelled.
    exit /b 0
)

echo.
echo Starting cleanup process...
echo.

REM Stop all containers
echo [1/5] Stopping all containers...
docker-compose down
if %errorlevel% neq 0 (
    echo [WARNING] Failed to stop containers (they may not be running)
) else (
    echo [OK] Containers stopped
)
echo.

REM Remove containers and volumes
echo [2/5] Removing containers and volumes...
docker-compose down -v
if %errorlevel% neq 0 (
    echo [WARNING] Failed to remove containers and volumes
) else (
    echo [OK] Containers and volumes removed
)
echo.

REM Remove Docker images
echo [3/5] Removing Docker images...
set /p remove_images="Do you want to remove Docker images? (yes/no): "
if /i "%remove_images%"=="yes" (
    docker-compose down --rmi all
    if %errorlevel% neq 0 (
        echo [WARNING] Failed to remove some images
    ) else (
        echo [OK] Docker images removed
    )
) else (
    echo [SKIPPED] Docker images kept
)
echo.

REM Clean up Docker build cache
echo [4/5] Cleaning up Docker build cache...
set /p clean_cache="Do you want to clean Docker build cache? (yes/no): "
if /i "%clean_cache%"=="yes" (
    docker builder prune -f
    if %errorlevel% neq 0 (
        echo [WARNING] Failed to clean build cache
    ) else (
        echo [OK] Docker build cache cleaned
    )
) else (
    echo [SKIPPED] Build cache kept
)
echo.

REM Remove environment file
echo [5/5] Cleaning up environment files...
set /p remove_env="Do you want to remove .env.docker file? (yes/no): "
if /i "%remove_env%"=="yes" (
    if exist .env.docker (
        del .env.docker
        echo [OK] .env.docker removed
    ) else (
        echo [INFO] .env.docker does not exist
    )
) else (
    echo [SKIPPED] .env.docker kept
)
echo.

REM Display completion message
echo ============================================================================
echo   Cleanup Complete!
echo ============================================================================
echo.
echo The Docker environment has been cleaned up.
echo.
echo To set up the environment again, run:
echo   scripts\setup.bat
echo.
echo ============================================================================
