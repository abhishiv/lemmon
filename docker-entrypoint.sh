#!/bin/sh
set -e

# Run database migrations
php artisan migrate --force

# Run database seeders
# php artisan db:seed --force

# Start PHP-FPM server
# php artisan serve
# php artisan serve --host=0.0.0.0
php artisan octane:start -n --host 0.0.0.0