# Base Image
FROM php:8.2-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-linux-headers --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    libzip-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Working Directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer Dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx & Supervisor Configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose Port
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
