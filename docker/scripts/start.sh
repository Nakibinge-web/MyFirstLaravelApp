#!/bin/sh

# =============================================================================
# Application Startup Script
# =============================================================================
# This script handles the startup sequence for the Laravel application
# =============================================================================

set -e

echo "🚀 Starting Personal Financial Tracker..."

# Configure Nginx to use the correct PORT (Render sets this dynamically)
export PORT=${PORT:-80}
echo "🔧 Configuring Nginx to listen on port $PORT..."

# Update nginx configuration with the correct port
sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/http.d/default.conf

# Verify the configuration was updated correctly
echo "📋 Nginx configuration:"
grep "listen" /etc/nginx/http.d/default.conf

# Test nginx configuration
echo "🔍 Testing Nginx configuration..."
nginx -t

# Wait for database to be ready (if DATABASE_URL is set)
if [ ! -z "$DATABASE_URL" ]; then
    echo "⏳ Waiting for database connection..."
    
    # Try to run migrations, but don't fail if database isn't ready yet
    php artisan migrate --force || echo "⚠️  Database migration failed - will retry later"
    echo "✅ Database migrations attempted"
fi

# Cache Laravel configurations for better performance
echo "📦 Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L "/var/www/html/public/storage" ]; then
    php artisan storage:link
    echo "🔗 Storage link created"
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Application startup completed"

# Start supervisor to manage PHP-FPM and Nginx
echo "🚀 Starting services with supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf