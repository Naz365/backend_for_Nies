#!/bin/bash

echo "Starting N.I. Engineering Backend Container Setup..."

# Ensure SQLite database directory & file exists
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Ensure storage and bootstrap cache directories exist
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix ownership and permissions for Apache www-data user
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY is present
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:uJ3n1jO8g+d4zW1V8q5b9A2k4L6m8N0P2q4R6S8T0U="
fi

# Run artisan setup commands safely
php artisan key:generate --force || true
php artisan migrate:fresh --seed --force || true
php artisan filament:assets || true
php artisan storage:link || true
php artisan config:clear || true
php artisan route:clear || true

# Re-fix ownership after artisan commands
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "Setup completed successfully. Starting Apache web server..."

# Execute Apache in foreground
exec "$@"
