#!/bin/bash

# ============================================================================
# Docker Environment Setup Script
# ============================================================================
# This script sets up the Docker development environment for the
# Personal Financial Tracker Laravel application.
# ============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo "============================================================================"
echo "  Personal Financial Tracker - Docker Environment Setup"
echo "============================================================================"
echo ""

# Check if Docker is installed
echo "[1/9] Checking Docker installation..."
if ! command -v docker &> /dev/null; then
    echo -e "${RED}[ERROR]${NC} Docker is not installed or not in PATH."
    echo "Please install Docker from: https://www.docker.com/get-started"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Docker is installed"
echo ""

# Check if Docker Compose is installed
echo "[2/9] Checking Docker Compose installation..."
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}[ERROR]${NC} Docker Compose is not installed or not in PATH."
    echo "Please install Docker Compose from: https://docs.docker.com/compose/install/"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Docker Compose is installed"
echo ""

# Check if Docker daemon is running
echo "[3/9] Checking if Docker daemon is running..."
if ! docker info &> /dev/null; then
    echo -e "${RED}[ERROR]${NC} Docker daemon is not running."
    echo "Please start Docker and try again."
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Docker daemon is running"
echo ""

# Copy environment file
echo "[4/9] Setting up environment configuration..."
if [ ! -f .env.docker ]; then
    if [ -f .env.docker.example ]; then
        cp .env.docker.example .env.docker
        echo -e "${GREEN}[OK]${NC} Created .env.docker from .env.docker.example"
    else
        echo -e "${YELLOW}[WARNING]${NC} .env.docker.example not found, skipping..."
    fi
else
    echo -e "${GREEN}[OK]${NC} .env.docker already exists"
fi
echo ""

# Build Docker containers
echo "[5/9] Building Docker containers (this may take a few minutes)..."
if ! docker-compose build; then
    echo -e "${RED}[ERROR]${NC} Failed to build Docker containers"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Docker containers built successfully"
echo ""

# Start Docker containers
echo "[6/9] Starting Docker containers..."
if ! docker-compose up -d; then
    echo -e "${RED}[ERROR]${NC} Failed to start Docker containers"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Docker containers started successfully"
echo ""

# Wait for services to be ready
echo "[7/9] Waiting for services to be ready..."
sleep 10
echo -e "${GREEN}[OK]${NC} Services should be ready"
echo ""

# Install Composer dependencies
echo "[8/9] Installing Composer dependencies..."
if ! docker-compose exec -T php composer install; then
    echo -e "${YELLOW}[WARNING]${NC} Failed to install Composer dependencies"
    echo "You may need to run: docker-compose exec php composer install"
else
    echo -e "${GREEN}[OK]${NC} Composer dependencies installed"
fi
echo ""

# Install NPM dependencies
echo "Installing NPM dependencies..."
if ! docker-compose exec -T node npm install; then
    echo -e "${YELLOW}[WARNING]${NC} Failed to install NPM dependencies"
    echo "You may need to run: docker-compose exec node npm install"
else
    echo -e "${GREEN}[OK]${NC} NPM dependencies installed"
fi
echo ""

# Generate application key
echo "Generating application key..."
if ! docker-compose exec -T php php artisan key:generate; then
    echo -e "${YELLOW}[WARNING]${NC} Failed to generate application key"
    echo "You may need to run: docker-compose exec php php artisan key:generate"
else
    echo -e "${GREEN}[OK]${NC} Application key generated"
fi
echo ""

# Run database migrations
echo "[9/9] Running database migrations..."
if ! docker-compose exec -T php php artisan migrate --force; then
    echo -e "${YELLOW}[WARNING]${NC} Failed to run migrations"
    echo "You may need to run: docker-compose exec php php artisan migrate"
else
    echo -e "${GREEN}[OK]${NC} Database migrations completed"
fi
echo ""

# Run database seeders
echo "Running database seeders..."
if ! docker-compose exec -T php php artisan db:seed --force; then
    echo -e "${YELLOW}[WARNING]${NC} Failed to run seeders"
    echo "You may need to run: docker-compose exec php php artisan db:seed"
else
    echo -e "${GREEN}[OK]${NC} Database seeders completed"
fi
echo ""

# Display success message
echo "============================================================================"
echo "  Setup Complete!"
echo "============================================================================"
echo ""
echo "Your Docker development environment is ready!"
echo ""
echo "Available services:"
echo "  - Application:  http://localhost"
echo "  - phpMyAdmin:   http://localhost:8080"
echo "  - Vite HMR:     http://localhost:5173"
echo ""
echo "Useful commands:"
echo "  - View logs:           docker-compose logs -f"
echo "  - Stop containers:     docker-compose down"
echo "  - Restart containers:  docker-compose restart"
echo "  - Access PHP shell:    docker-compose exec php sh"
echo "  - Run artisan:         docker-compose exec php php artisan [command]"
echo "  - Run tests:           docker-compose exec php php artisan test"
echo ""
echo "For more information, see README.docker.md"
echo ""
echo "============================================================================"
