# Use the official PHP 8.2 image as a base
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    nodejs \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    postgresql-client \
    libpq-dev \
    nginx \
    netcat-traditional \
    imagemagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-install zip \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install sockets \
    && docker-php-ext-install pdo pdo_pgsql

RUN apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Copy existing application directory contents
COPY . .

RUN npm install && npm run dev

# Install PHP dependencies
RUN composer install --no-autoloader --no-scripts

# Generate autoload files
RUN composer dump-autoload --optimize

RUN php artisan livewire:publish --assets

# Set up permissions (if needed)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY nginx.conf /etc/nginx/nginx.conf

# Expose the specified port
EXPOSE 8000

# Set the entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Expose the port specified by the environment variable, default to 80
