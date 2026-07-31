#!/bin/bash
set -e

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Ensure SQLite database file exists
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure bootstrap cache and storage permissions
mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache database

# Run artisan setup commands at runtime
php artisan key:generate --force || true
php artisan migrate:fresh --seed --force || true
php artisan filament:assets || true
php artisan storage:link || true

# Execute main process (Apache)
exec "$@"
