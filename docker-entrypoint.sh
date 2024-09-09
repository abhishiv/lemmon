#!/bin/sh
set -e

# Run database migrations
php artisan migrate --force

# Run database seeders
php artisan db:seed --force

# Start PHP-FPM server
php-fpm
