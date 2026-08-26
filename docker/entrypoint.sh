#!/bin/sh

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link --force

# Run database migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Start Supervisord (Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
