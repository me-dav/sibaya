#!/bin/sh
set -e
cd /var/www/html

# Hanya untuk proses php-fpm (bukan untuk `docker compose run ... php artisan ...`)
if [ "$1" = "php-fpm" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "ERROR: APP_KEY kosong. Isi APP_KEY di file .env (lihat DEPLOY.md)." >&2
        exit 1
    fi

    # Volume storage kosong saat pertama kali dipasang, jadi buat strukturnya
    mkdir -p storage/app/public storage/app/private \
             storage/framework/cache/data storage/framework/sessions storage/framework/views \
             storage/logs bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        echo ">> Menjalankan migration..."
        su-exec www-data php artisan migrate --force
    fi

    echo ">> Membuat cache konfigurasi..."
    su-exec www-data php artisan optimize:clear >/dev/null 2>&1 || true
    su-exec www-data php artisan config:cache
    su-exec www-data php artisan event:cache
    su-exec www-data php artisan view:cache
    # route:cache gagal jika ada route berbentuk Closure. Aplikasi tetap jalan tanpanya.
    su-exec www-data php artisan route:cache \
        || echo "PERINGATAN: route:cache gagal (ada route Closure?). Lanjut tanpa cache route."
fi

exec "$@"
