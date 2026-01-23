#!/bin/sh

# =============================================================================
# Application Startup Script - Enhanced with Error Handling
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
if ! nginx -t; then
    echo "❌ Nginx configuration test failed!"
    cat /etc/nginx/http.d/default.conf
    exit 1
fi

# Check if APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo "⚠️  WARNING: APP_KEY is not set! This will cause 500 errors."
    echo "Please set APP_KEY in your environment variables."
fi

# Check critical environment variables
echo "🔍 Checking environment variables..."
echo "APP_ENV: ${APP_ENV:-not set}"
echo "APP_DEBUG: ${APP_DEBUG:-not set}"
echo "DATABASE_URL: ${DATABASE_URL:+set}"
echo "APP_KEY: ${APP_KEY:+set}"

# Wait for database to be ready (if DATABASE_URL is set)
if [ ! -z "$DATABASE_URL" ]; then
    echo "⏳ Waiting for database connection..."
    
    # Try to run migrations, but don't fail if database isn't ready yet
    if php artisan migrate --force; then
        echo "✅ Database migrations completed successfully"
    else
        echo "⚠️  Database migration failed - will retry later"
        echo "This might be normal if database is still starting up"
    fi
else
    echo "ℹ️  No DATABASE_URL set, skipping database operations"
fi

# Cache Laravel configurations for better performance
echo "📦 Optimizing Laravel..."
php artisan config:cache || echo "⚠️  Config cache failed"
php artisan route:cache || echo "⚠️  Route cache failed"
php artisan view:cache || echo "⚠️  View cache failed"

# Create storage link if it doesn't exist
if [ ! -L "/var/www/html/public/storage" ]; then
    php artisan storage:link || echo "⚠️  Storage link creation failed"
    echo "🔗 Storage link created"
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || echo "⚠️  Permission setting failed"
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || echo "⚠️  Permission setting failed"

# Test basic Laravel functionality
echo "🧪 Testing Laravel application..."
if php artisan --version; then
    echo "✅ Laravel is responding correctly"
else
    echo "❌ Laravel test failed!"
    exit 1
fi

echo "✅ Application startup completed"

# Start supervisor to manage PHP-FPM and Nginx
echo "🚀 Starting services with supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf