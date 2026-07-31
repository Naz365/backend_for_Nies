#!/bin/bash

echo "Starting N.I. Engineering Backend Container Setup..."

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    cat <<EOT > /var/www/html/.env
APP_NAME="N.I. Engineering Services CMS"
APP_ENV=production
APP_KEY=base64:uJ3n1jO8g+d4zW1V8q5b9A2k4L6m8N0P2q4R6S8T0U=
APP_DEBUG=true
APP_URL=https://ni-engineering-backend.onrender.com
LOG_CHANNEL=stderr
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_STORE=array
SESSION_DRIVER=array
FILESYSTEM_DISK=public
EOT
fi

# Ensure SQLite database directory & file exists
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage, bootstrap cache, and public vendor directories exist
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/public/vendor

# Remove stale bootstrap cache
rm -f /var/www/html/bootstrap/cache/*.php

# Run database migrations and seeders cleanly
echo "Running Artisan Database Migrations & Seeders..."
php artisan migrate:fresh --seed --force || true
php artisan filament:assets || true
php artisan livewire:publish --assets || true
php artisan storage:link || true
php artisan view:clear || true

# Grant full 777 permissions and www-data ownership to storage, database, and cache
echo "Setting permissions for Apache web server..."
chown -R www-data:www-data /var/www/html
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public /var/www/html/.env

echo "Setup completed successfully. Starting Apache web server..."

# Execute Apache in foreground
exec "$@"
