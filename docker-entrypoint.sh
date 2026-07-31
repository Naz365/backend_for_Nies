#!/bin/bash

echo "Starting N.I. Engineering Backend Container Setup..."

# Ensure .env file exists with default production variables
if [ ! -f /var/www/html/.env ]; then
    cat <<EOT > /var/www/html/.env
APP_NAME="N.I. Engineering Services CMS"
APP_ENV=production
APP_KEY=base64:uJ3n1jO8g+d4zW1V8q5b9A2k4L6m8N0P2q4R6S8T0U=
APP_DEBUG=true
APP_URL=https://ni-engineering-backend.onrender.com
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_STORE=file
SESSION_DRIVER=file
FILESYSTEM_DISK=public
EOT
fi

# Ensure SQLite database directory & file exists with full permissions
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chmod -R 777 /var/www/html/database

# Ensure storage and bootstrap cache directories exist with full permissions
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations and seeding explicitly
echo "Running Artisan Database Migrations & Seeders..."
php artisan migrate:fresh --seed --force
php artisan filament:assets
php artisan storage:link || true
php artisan config:clear
php artisan route:clear

# Fix ownership for Apache www-data user
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env

echo "Setup completed successfully. Starting Apache web server..."

# Execute Apache in foreground
exec "$@"
