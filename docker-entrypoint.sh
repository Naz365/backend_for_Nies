#!/bin/bash

echo "Starting N.I. Engineering Backend Container Setup..."

# Ensure .env file exists from environment variables if not present
if [ ! -f /var/www/html/.env ]; then
    cat <<EOT > /var/www/html/.env
APP_NAME="N.I. Engineering Services CMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=\${APP_URL:-https://ni-engineering-backend.onrender.com}
LOG_CHANNEL=stderr
DB_CONNECTION=\${DB_CONNECTION:-sqlite}
DB_DATABASE=\${DB_DATABASE:-/var/www/html/database/database.sqlite}
CACHE_STORE=array
SESSION_DRIVER=array
FILESYSTEM_DISK=public
EOT
fi

# Ensure SQLite database directory & file exists if SQLite is active
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
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

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction || true
fi

# Run additive database migrations strictly (Failure must fail container startup)
echo "Running Additive Artisan Database Migrations..."
php artisan migrate --force
php artisan filament:assets || true
php artisan livewire:publish --assets || true
php artisan storage:link || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true

# Grant appropriate permissions
echo "Setting permissions for Apache web server..."
chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Setup completed successfully. Starting Apache web server..."

# Execute Apache in foreground
exec "$@"

