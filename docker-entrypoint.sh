#!/bin/bash
set -e

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# Ensure SQLite database directory & file exists
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage and bootstrap cache directories exist
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix ownership and permissions for Apache www-data user
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env

# Set fallback APP_KEY if missing
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:uJ3n1jO8g+d4zW1V8q5b9A2k4L6m8N0P2q4R6S8T0U="
fi

# Run artisan commands with www-data permissions
su -s /bin/bash www-data -c "php artisan key:generate --force" || true
su -s /bin/bash www-data -c "php artisan migrate:fresh --seed --force" || true
su -s /bin/bash www-data -c "php artisan filament:assets" || true
su -s /bin/bash www-data -c "php artisan storage:link" || true
su -s /bin/bash www-data -c "php artisan config:clear" || true
su -s /bin/bash www-data -c "php artisan route:clear" || true

# Execute main process (Apache)
exec "$@"
