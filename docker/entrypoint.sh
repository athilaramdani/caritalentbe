#!/bin/bash
set -e

echo "========================================"
echo "  CariTalent Backend — Starting Up"
echo "========================================"

# Tunggu database siap
echo ""
echo "[1/5] Menunggu database siap..."
MAX_RETRIES=30
RETRY=0
until php artisan db:show --no-interaction > /dev/null 2>&1; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "  [ERROR] Database tidak dapat dijangkau setelah $MAX_RETRIES percobaan. Abort."
        exit 1
    fi
    echo "  Database belum siap (percobaan $RETRY/$MAX_RETRIES), coba lagi dalam 3 detik..."
    sleep 3
done
echo "  [OK] Database siap!"

# Cache config untuk production
echo ""
echo "[2/5] Optimasi Laravel untuk production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "  [OK] Cache selesai!"

# Buat symlink storage (jika belum ada)
echo ""
echo "[3/5] Setup storage link..."
php artisan storage:link --force 2>/dev/null || true
echo "  [OK] Storage link selesai!"

# Jalankan migrasi
echo ""
echo "[4/5] Menjalankan migrasi database..."
php artisan migrate --force --no-interaction
echo "  [OK] Migrasi selesai!"

# Jalankan seeder hanya jika database masih kosong
echo ""
echo "[5/5] Mengecek apakah seeder perlu dijalankan..."
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | grep -E '^[0-9]+$' | tail -1)

if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
    echo "  Database masih kosong, menjalankan seeder..."
    php artisan db:seed --force --no-interaction
    echo "  [OK] Seeder selesai!"
else
    echo "  Data sudah ada ($USER_COUNT users ditemukan), skip seeder."
fi

echo ""
echo "========================================"
echo "  ✅ Setup selesai! Menjalankan PHP-FPM"
echo "========================================"
echo ""

exec php-fpm
