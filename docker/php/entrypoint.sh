#!/bin/sh
set -e

LOCK_HASH_FILE="/var/www/html/vendor/.composer.lock.sha256"
CURRENT_LOCK_HASH=""
if [ -f /var/www/html/composer.lock ]; then
    CURRENT_LOCK_HASH=$(sha256sum /var/www/html/composer.lock | awk '{print $1}')
fi

if [ ! -f /var/www/html/vendor/autoload.php ] || [ ! -f "$LOCK_HASH_FILE" ] || [ "$(cat "$LOCK_HASH_FILE" 2>/dev/null)" != "$CURRENT_LOCK_HASH" ]; then
    echo "Syncing Composer dependencies into Linux vendor volume..."
    composer install --no-interaction --optimize-autoloader
    if [ -n "$CURRENT_LOCK_HASH" ]; then
        echo "$CURRENT_LOCK_HASH" > "$LOCK_HASH_FILE"
    fi
fi

exec docker-php-entrypoint "$@"