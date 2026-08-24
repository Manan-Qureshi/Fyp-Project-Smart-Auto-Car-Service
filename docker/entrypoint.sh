#!/bin/sh

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Start Supervisord (Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
