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

# Ensure bootstrap cache and storage permissions
mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache database

# Ensure APP_KEY is present
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:uJ3n1jO8g+d4zW1V8q5b9A2k4L6m8N0P2q4R6S8T0U="
fi

# Run artisan setup commands at runtime
php artisan key:generate --force || true
php artisan migrate:fresh --seed --force || true
php artisan filament:assets || true
php artisan storage:link || true
php artisan config:clear || true
php artisan route:clear || true

# Execute main process (Apache)
exec "$@"
