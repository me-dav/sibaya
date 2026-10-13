# syntax=docker/dockerfile:1.7
# siBAYA: Laravel 12 + PHP-FPM 8.3 + Nginx (target: web)

############################
# 1) base: PHP-FPM + ekstensi
############################
FROM php:8.3-fpm-alpine AS base

COPY --from=ghcr.io/mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

# pdo_pgsql/pgsql : PostgreSQL     gd : QR code + PDF sertifikat
# intl/bcmath/zip/exif/opcache/pcntl : umum untuk Laravel
RUN apk add --no-cache su-exec \
 && install-php-extensions pdo_pgsql pgsql bcmath intl gd zip exif opcache pcntl

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-sibaya.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-sibaya.conf

WORKDIR /var/www/html

############################
# 2) vendor: composer install (tanpa dev)
############################
FROM base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Layer terpisah supaya cache composer tidak ikut batal saat kode berubah
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts \
 && php artisan package:discover --ansi || true

############################
# 3) assets: build Vite + Tailwind
############################
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# Tailwind memindai view bawaan paginasi Laravel (lihat tailwind.config.js)
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

############################
# 4) production: image PHP-FPM final
############################
FROM base AS production
COPY --from=vendor /var/www/html /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
# Buang CRLF kalau file diedit di Windows, lalu jadikan executable
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
 && chmod +x /usr/local/bin/entrypoint.sh \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

############################
# 5) web: Nginx + file statis (public/)
############################
FROM nginx:1.27-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public
EXPOSE 80
