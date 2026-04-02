@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

echo =======================================================
echo    INITIALIZING CARITALENT PROJECT (FIRST TIME SETUP)
echo =======================================================
echo.
echo [INFO] Script ini akan mengkonfigurasi driver PHP, 
echo        PostgreSQL, dan dependensi Laravel Anda.
echo.

:: 1. Cek PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di PATH. 
    echo         Pastikan XAMPP sudah terinstall.
    pause
    exit /b 1
)

:: 2. Aktifkan Driver PostgreSQL di php.ini
echo [1/5] Mengaktifkan driver PostgreSQL di PHP...
for /f "delims=" %%i in ('php -r "echo php_ini_loaded_file();"') do set PHP_INI_PATH=%%i
if "!PHP_INI_PATH!"=="" (
    :: Fallback untuk XAMPP default
    set PHP_INI_PATH=C:\xampp\php\php.ini
)

if exist "!PHP_INI_PATH!" (
    echo [INFO] Mengedit: !PHP_INI_PATH!
    powershell -Command "(Get-Content '!PHP_INI_PATH!') -replace ';extension=pdo_pgsql', 'extension=pdo_pgsql' -replace ';extension=pgsql', 'extension=pgsql' | Set-Content '!PHP_INI_PATH!'"
    echo [SUCCESS] Driver diaktifkan.
) else (
    echo [WARNING] php.ini tidak ditemukan. Silakan aktifkan pdo_pgsql dan pgsql manual.
)

:: 3. Cek/Install PostgreSQL
echo [2/5] Menyiapkan PostgreSQL server...
:: Cek service apa saja yang ada
set PG_SERVICE=
for /f "tokens=2 delims= " %%s in ('sc query state^= all ^| findstr "postgresql-x64-"') do (
    set PG_SERVICE=%%s
    goto :found_pg
)

:found_pg
if "!PG_SERVICE!"=="" (
    echo [INFO] PostgreSQL tidak ditemukan. Menginstall via WinGet...
    winget install --id PostgreSQL.PostgreSQL.16 --source winget --accept-package-agreements --accept-source-agreements
    echo [PENTING] Jendela installer akan muncul. Setel password ke: postgres
    echo [INFO] Setelah install selesai, jalankan lagi script ini.
    pause
    exit /b 0
)

echo [SUCCESS] Menemukan service: !PG_SERVICE!
net start !PG_SERVICE! >nul 2>&1

:: 4. Buat Database
echo [3/5] Membuat database 'caritalent_db'...
set PGPASSWORD=postgres

:: Cari psql secara dinamis (mendukung semua versi PostgreSQL)
set "PSQL_CMD=psql"
for /d %%v in ("C:\Program Files\PostgreSQL\*") do (
    if exist "%%~v\bin\psql.exe" (
        set "PSQL_CMD=%%~v\bin\psql.exe"
    )
)

:psql_check
"%PSQL_CMD%" --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] psql tidak ditemukan atau tidak dapat dijalankan.
    echo         Silakan periksa instalasi PostgreSQL Anda.
    pause
    exit /b 1
)

"%PSQL_CMD%" -U postgres -c "SELECT 1 FROM pg_database WHERE datname = 'caritalent_db'" | findstr /C:"1" >nul 2>&1
if errorlevel 1 (
    "%PSQL_CMD%" -U postgres -c "CREATE DATABASE caritalent_db;"
    echo [SUCCESS] Database 'caritalent_db' berhasil dibuat.
) else (
    echo [INFO] Database 'caritalent_db' sudah ada.
)

:: 5. Composer Install
echo [4/5] Menginstall dependensi (Composer)...
call composer install

:: 6. Migrasi
echo [5/5] Menjalankan migrasi database...
php artisan migrate

echo.
echo =======================================================
echo    SETUP SELESAI! Silakan jalankan 'running.bat' 
echo    untuk mulai mengerjakan.
echo =======================================================
pause
