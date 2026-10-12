FROM php:8.3-fpm-alpine

# Install system dependencies & PostgreSQL dev libraries
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    oniguruma-dev

# Install PHP extensions yang wajib buat Laravel + PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring bcmath opcache pcntl gd

# Install Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy seluruh source code Laravel
COPY . /var/www

# Set permission storage & bootstrap/cache biar tidak error 500
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]