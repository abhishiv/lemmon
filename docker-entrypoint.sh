#!/bin/sh

# Start PHP-FPM in the background
php-fpm -D

# Wait for PHP-FPM to be ready
while ! nc -z 0.0.0.0 9000; do  
  echo "sleeping" 
  sleep 0.1
done

# Start NGINX
nginx -g 'daemon off;'
