@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

echo =======================================================
echo     Memulai CariTalent API Server ^& PostgreSQL
echo =======================================================
echo.

echo [1/2] Menyalakan service PostgreSQL lokal...
echo ---

:: Detect PostgreSQL service version
set PG_SERVICE=
for /f "tokens=2 delims= " %%s in ('sc query state^= all ^| findstr "postgresql-x64-"') do (
    set PG_SERVICE=%%s
    goto :found_pg
)

:found_pg
if "!PG_SERVICE!"=="" (
    echo [ERROR] PostgreSQL service tidak ditemukan. 
    echo         Jalankan 'initialize.bat' dulu.
    pause
    exit /b 1
)

echo [INFO] Menemukan service: !PG_SERVICE!

:: Cek apakah service sudah berjalan
sc query !PG_SERVICE! | find "RUNNING" >nul
if !errorlevel! equ 0 (
    echo [SUCCESS] Service !PG_SERVICE! sudah berjalan dan siap digunakan.
) else (
    :: Cek apakah script dijalankan sebagai Administrator
    net session >nul 2>&1
    if !errorlevel! neq 0 (
        echo [PERHATIAN] Akses ditolak. Harap jalankan script ini 
        echo             sebagai "Administrator" untuk menyalakan database.
    ) else (
        net start !PG_SERVICE! >nul 2>&1
        echo [SUCCESS] Service !PG_SERVICE! telah menyala.
    )
)
echo ---
echo.

echo [2/2] Memulai Laravel Development Server...
echo [INFO] Membuka API Documentation di browser...
start http://127.0.0.1:8000/api/documentation
php artisan serve

pause
