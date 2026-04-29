#!/bin/bash

# Pindah ke direktori dimana file ini berada
cd "$(dirname "$0")"

echo "======================================================="
echo "     Memulai CariTalent API Server & PostgreSQL"
echo "======================================================="
echo ""

# 1. Detect dan start PostgreSQL service
echo "[1/2] Menyalakan service PostgreSQL lokal..."
echo "---"

if command -v psql &> /dev/null; then
    # Check PostgreSQL status dan start jika perlu
    if brew services list 2>/dev/null | grep -q postgresql; then
        # Menggunakan Homebrew PostgreSQL
        if brew services list | grep postgresql | grep -q started; then
            echo "[SUCCESS] PostgreSQL sudah berjalan dan siap digunakan."
        else
            echo "[INFO] Memulai PostgreSQL service via Homebrew..."
            brew services start postgresql@16 2>/dev/null || brew services start postgresql 2>/dev/null || {
                echo "[ERROR] Gagal memulai PostgreSQL. Silakan cek instalasi Anda."
                echo "        Jalankan: brew services list"
                exit 1
            }
            echo "[SUCCESS] PostgreSQL telah dimulai."
        fi
    else
        # PostgreSQL installed tapi bukan via Homebrew (e.g., Postgres.app)
        echo "[INFO] PostgreSQL terdeteksi. Pastikan aplikasi Postgres.app/DBngin sudah Start."
    fi
else
    echo "[ERROR] PostgreSQL tidak ditemukan."
    echo "        Silakan jalankan './initialize.sh' terlebih dahulu."
    exit 1
fi

echo "---"
echo ""

# 2. Start Laravel Development Server
echo "[2/2] Memulai Laravel Development Server..."
echo "[INFO] Membuka API Documentation di browser..."
echo ""

# Open browser dengan dokumentasi API
open http://127.0.0.1:8000/api/documentation 2>/dev/null || true

# Start Vite dev server di background (jika ada)
if [ -f "vite.config.js" ] && command -v npm &> /dev/null; then
    echo "[INFO] Memulai Vite dev server..."
    npm run dev &
    VITE_PID=$!
fi

# Start Laravel server (foreground)
echo ""
php artisan serve

# Cleanup if Vite was running
if [ ! -z "$VITE_PID" ]; then
    kill $VITE_PID 2>/dev/null || true
fi
