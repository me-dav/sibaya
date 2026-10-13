# Deploy siBAYA dengan Docker (nginx + php-fpm + PostgreSQL)

Lihat ringkasan langkah di chat. Perintah cadangan dan pemecahan masalah ada di bawah.

## Update aplikasi
    cd /opt/sibaya && git pull
    docker compose up -d --build        # migration otomatis jika RUN_MIGRATIONS=true

## Backup
    # database
    docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' | gzip > backup-db-$(date +%F).sql.gz
    # file unggahan (audio soal, gambar materi)
    docker run --rm -v sibaya_storage_data:/data -v "$PWD":/backup alpine tar czf /backup/backup-storage-$(date +%F).tar.gz -C /data app

## Restore database
    gunzip -c backup-db-TANGGAL.sql.gz | docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"'

## Pemecahan masalah
| Gejala | Penyebab / solusi |
|---|---|
| Container `app` restart terus | `docker compose logs app`. Biasanya APP_KEY kosong atau migration gagal |
| 502 Bad Gateway | `app` belum siap/mati. `docker compose ps`, lalu `logs app` |
| 413 saat upload audio | Pastikan `client_max_body_size 12m` juga ada di nginx host |
| CSS/JS dimuat lewat http (mixed content) | Tambah `$middleware->trustProxies(at: '*');` dan APP_URL harus https |
| `route:cache` gagal | Ada route Closure di routes/web.php. Ganti dengan controller |
| Perubahan .env tidak terbaca | `docker compose up -d --force-recreate app` (config di-cache saat start) |
| Gambar materi 404 | Cek file ada di volume: `docker compose exec app ls storage/app/public` |
