#!/bin/bash

# Pindah ke direktori dimana file ini berada
cd "$(dirname "$0")"

echo "======================================================="
echo "    Memulai CariTalent API Server (Mac)"
echo "======================================================="
echo ""

echo "[1/2] Menyalakan PostgreSQL..."
echo "Pastikan aplikasi Postgres (Postgres.app / DBngin) sudah 'Start' di Mac kamu."
echo "---"

echo "[2/2] Memulai Laravel Development Server..."
echo "Membuka API Documentation Swagger di browser..."
# Di Mac menggunakan 'open', bukan 'start' seperti di Windows
open http://127.0.0.1:8000/api/documentation
echo ""

php artisan serve
