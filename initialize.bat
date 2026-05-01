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
echo [1/6] Mengaktifkan driver PostgreSQL di PHP...
for /f "delims=" %%i in ('php -r "echo php_ini_loaded_file();"') do set PHP_INI_PATH=%%i
if "!PHP_INI_PATH!"=="" (
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
echo [2/6] Menyiapkan PostgreSQL server...
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
echo [3/6] Membuat database 'caritalent_db'...
set PGPASSWORD=postgres
set "PSQL_CMD=psql"
for /d %%v in ("C:\Program Files\PostgreSQL\*") do (
    if exist "%%~v\bin\psql.exe" (
        set "PSQL_CMD=%%~v\bin\psql.exe"
    )
)
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
echo [4/6] Menginstall dependensi (Composer)...
call composer install
:: 6. Migrasi
echo [5/6] Menjalankan migrasi database...
php artisan migrate
:: 7. Seed Data Dummy
echo [6/6] Memasukkan data dummy CariTalent...
echo [SEED] Menjalankan seeder...
php artisan db:seed --class=DummyDataSeeder
if %errorlevel% neq 0 (
    echo [WARNING] Seeder gagal otomatis. Coba jalankan manual:
    echo           php artisan db:seed --class=DummyDataSeeder
) else (
    echo [SUCCESS] Data dummy berhasil dimasukkan.
)
echo.
echo =======================================================
echo    RINGKASAN DATA DUMMY YANG DIMASUKKAN:
echo =======================================================
echo.
echo   USERS (12 user):
echo     - 1 Admin     : Aprilianza Muhammad Yusup
echo     - 4 EO        : Athila (Kafe Braga Permai),
echo                     Bill (Pasar Bandoeng),
echo                     Jeany (Braga Art Space),
echo                     Hendra (Kopi Selasar)
echo     - 7 Talent    : Irgiansyah (The Rotten Bandung - Pop Punk),
echo                     Arfian (DJ Arfz Bdg),
echo                     Rizky (Acoustic/Solo),
echo                     Siti ND (Jazz),
echo                     Dendi (Altar Sunda - Metal),
echo                     Fauzan (Langit Sore - Indie),
echo                     Nandita (Visual Art)
echo.
echo   EVENTS (11 event):
echo     - 4 OPEN      : Braga Punk Night Vol.5, Pasar Bandoeng Weekend Vibes,
echo                     Braga Jazz Evening, Kopi Selasar Acoustic Sunday
echo     - 1 DRAFT     : Braga Indie Fest 2026
echo     - 1 CLOSED    : Pasar Bandoeng Metal Malam
echo     - 3 COMPLETED : Braga Punk Vol.4, Jazz Maret, Acoustic Maret, DJ Night Feb
echo     - 1 CANCELLED : Braga Art Night
echo.
echo   APPLICATIONS (16 records):
echo     - PENDING     : apply dan invitation belum direspons
echo     - ACCEPTED    : lanjut ke booking
echo     - REJECTED    : ditolak EO atau talent
echo     - CANCELLED   : dibatalkan oleh talent
echo.
echo   BOOKINGS (7 records):
echo     - 3 CONFIRMED : DJ Weekend Vibes, Jazz Evening, Acoustic Sunday
echo     - 4 COMPLETED : semua ada review
echo.
echo   REVIEWS (14 records):
echo     - Rating 4-5 bintang dari berbagai EO
echo.
echo   PASSWORD SEMUA USER: password123
echo.
echo =======================================================
echo    SETUP SELESAI. Silakan jalankan 'running.bat'
echo    untuk mulai mengerjakan.
echo =======================================================
pause
