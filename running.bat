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
net start !PG_SERVICE! >nul 2>&1

if %errorlevel% equ 2 (
    echo [PERHATIAN] Akses ditolak. Harap jalankan script ini 
    echo             sebagai "Administrator".
) else if %errorlevel% equ 0 (
    echo [SUCCESS] Service !PG_SERVICE! telah menyala.
) else (
    echo [INFO] PostgreSQL mungkin sudah berjalan atau siap digunakan.
)
echo ---
echo.

echo [2/2] Memulai Laravel Development Server...
php artisan serve

pause
