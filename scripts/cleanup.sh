#!/bin/bash

# ============================================================================
# Docker Environment Cleanup Script
# ============================================================================
# This script stops and removes all Docker containers, volumes, and build
# cache for the Personal Financial Tracker application.
# ============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo "============================================================================"
echo "  Personal Financial Tracker - Docker Environment Cleanup"
echo "============================================================================"
echo ""
echo -e "${YELLOW}WARNING:${NC} This will remove all containers, volumes, and cached data."
echo "All database data will be lost!"
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo ""
    echo "Cleanup cancelled."
    exit 0
fi

echo ""
echo "Starting cleanup process..."
echo ""

# Stop all containers
echo "[1/5] Stopping all containers..."
if docker-compose down; then
    echo -e "${GREEN}[OK]${NC} Containers stopped"
else
    echo -e "${YELLOW}[WARNING]${NC} Failed to stop containers (they may not be running)"
fi
echo ""

# Remove containers and volumes
echo "[2/5] Removing containers and volumes..."
if docker-compose down -v; then
    echo -e "${GREEN}[OK]${NC} Containers and volumes removed"
else
    echo -e "${YELLOW}[WARNING]${NC} Failed to remove containers and volumes"
fi
echo ""

# Remove Docker images
echo "[3/5] Removing Docker images..."
read -p "Do you want to remove Docker images? (yes/no): " remove_images
if [ "$remove_images" = "yes" ]; then
    if docker-compose down --rmi all; then
        echo -e "${GREEN}[OK]${NC} Docker images removed"
    else
        echo -e "${YELLOW}[WARNING]${NC} Failed to remove some images"
    fi
else
    echo -e "${YELLOW}[SKIPPED]${NC} Docker images kept"
fi
echo ""

# Clean up Docker build cache
echo "[4/5] Cleaning up Docker build cache..."
read -p "Do you want to clean Docker build cache? (yes/no): " clean_cache
if [ "$clean_cache" = "yes" ]; then
    if docker builder prune -f; then
        echo -e "${GREEN}[OK]${NC} Docker build cache cleaned"
    else
        echo -e "${YELLOW}[WARNING]${NC} Failed to clean build cache"
    fi
else
    echo -e "${YELLOW}[SKIPPED]${NC} Build cache kept"
fi
echo ""

# Remove environment file
echo "[5/5] Cleaning up environment files..."
read -p "Do you want to remove .env.docker file? (yes/no): " remove_env
if [ "$remove_env" = "yes" ]; then
    if [ -f .env.docker ]; then
        rm .env.docker
        echo -e "${GREEN}[OK]${NC} .env.docker removed"
    else
        echo -e "${YELLOW}[INFO]${NC} .env.docker does not exist"
    fi
else
    echo -e "${YELLOW}[SKIPPED]${NC} .env.docker kept"
fi
echo ""

# Display completion message
echo "============================================================================"
echo "  Cleanup Complete!"
echo "============================================================================"
echo ""
echo "The Docker environment has been cleaned up."
echo ""
echo "To set up the environment again, run:"
echo "  ./scripts/setup.sh"
echo ""
echo "============================================================================"
