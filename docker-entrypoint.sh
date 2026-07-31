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

# Ensure SQLite database directory & file exists
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Ensure storage and bootstrap cache directories exist
mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Remove any stale bootstrap cache files
rm -f /var/www/html/bootstrap/cache/*.php

# Grant full permissions and www-data ownership to the whole web tree
chown -R www-data:www-data /var/www/html
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env

# Run artisan setup as www-data user
su -s /bin/bash www-data -c "php artisan migrate:fresh --seed --force" || true
su -s /bin/bash www-data -c "php artisan filament:assets" || true
su -s /bin/bash www-data -c "php artisan storage:link" || true

# Re-grant permissions
chown -R www-data:www-data /var/www/html
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env

echo "Setup completed successfully. Starting Apache web server..."

# Execute Apache in foreground
exec "$@"
