@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
title CariTalent Backend Server

echo.
echo  =====================================================
echo    CariTalent Backend - Laravel API Server
echo  =====================================================
echo.

:: ==========================================
:: CEK PHP
:: ==========================================
php --version >nul 2>&1
if !errorlevel! neq 0 (
    echo [ERROR] PHP tidak ditemukan! Pastikan PHP sudah ada di PATH.
    pause
    exit /b 1
)

:: ==========================================
:: CEK COMPOSER DEPENDENCIES
:: ==========================================
if not exist "vendor\" (
    echo [INFO] Vendor folder belum ada. Menjalankan composer install...
    echo        Ini mungkin memakan beberapa menit...
    composer install
    if !errorlevel! neq 0 (
        echo [ERROR] Composer install gagal!
        pause
        exit /b 1
    )
    echo [OK] Dependencies berhasil diinstall.
    echo.
)

:: ==========================================
:: CEK .ENV FILE
:: ==========================================
if not exist ".env" (
    echo [INFO] File .env belum ada. Menyalin dari .env.example...
    copy .env.example .env >nul
    echo [INFO] Generating APP_KEY...
    php artisan key:generate
    echo [OK] File .env sudah dibuat.
    echo.
)

:: ==========================================
:: CEK DATABASE SQLite
:: ==========================================
if not exist "database\database.sqlite" (
    echo [INFO] Database SQLite belum ada. Membuat database...
    type nul > database\database.sqlite
    echo [INFO] Menjalankan migrasi database...
    php artisan migrate --force
    echo [OK] Database berhasil dibuat.
    echo.
) else (
    echo [OK] Database SQLite sudah ada.
)

:: ==========================================
:: GENERATE SWAGGER DOCS
:: ==========================================
echo [INFO] Generating API Documentation...
php artisan l5-swagger:generate >nul 2>&1
echo [OK] Swagger docs siap.
echo.

:: ==========================================
:: TAMPILKAN INFO API ENDPOINTS
:: ==========================================
echo  =====================================================
echo    Server akan berjalan di: http://127.0.0.1:8000
echo    API Base URL           : http://127.0.0.1:8000/api/v1
echo    API Documentation      : http://127.0.0.1:8000/api/documentation
echo  =====================================================
echo.
echo  Endpoint Utama:
echo    POST   /api/v1/auth/register   - Daftar akun baru
echo    POST   /api/v1/auth/login      - Login
echo    GET    /api/v1/events          - Daftar event (public)
echo    GET    /api/v1/talents         - Daftar talent (public)
echo    GET    /api/v1/genres          - Daftar genre (public)
echo.
echo  Tekan Ctrl+C untuk menghentikan server.
echo.

:: Buka browser ke Swagger docs
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000/api/documentation

:: Jalankan server
php artisan serve --host=127.0.0.1 --port=8000
