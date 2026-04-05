#!/bin/bash

# Pindah ke direktori dimana file ini berada
cd "$(dirname "$0")"

echo "======================================================="
echo "   INITIALIZING CARITALENT PROJECT MAC (FIRST TIME)"
echo "======================================================="
echo ""

echo "[1/4] Menginstall dependensi (Composer & NPM)..."
composer install
npm install

echo "[2/4] Setup Environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env berhasil dibuat dari .env.example"
fi
php artisan key:generate

echo "[3/4] Setup Database..."
# Jika psql command tersedia, coba pastikan pembuatan config
echo "Catatan: Pastikan aplikasi Postgres.app di Mac kamu sudah berjalan!"
createdb caritalent_db 2>/dev/null || echo "Info: Database 'caritalent_db' kemungkinan sudah dibuat sebelumnya."

echo "[4/4] Menjalankan migrasi database..."
php artisan migrate

echo ""
echo "======================================================="
echo "   SETUP SELESAI! Silakan jalankan './running.sh' "
echo "   untuk mulai mengerjakan (ngoding)."
echo "======================================================="
