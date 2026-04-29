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
echo [SEED] Membuat file seeder sementara...
:: Tulis PHP seeder ke file temp
call :GenerateSeeder > database\seeders\DummyDataSeeder.php
goto SeederDone
:GenerateSeeder
echo ^<?php
echo.
echo namespace Database\Seeders;
echo.
echo use Illuminate\Database\Seeder;
echo use Illuminate\Support\Facades\DB;
echo use Illuminate\Support\Facades\Hash;
echo use Carbon\Carbon;
echo.
echo class DummyDataSeeder extends Seeder
echo ^{
echo     public function run(^)
echo     ^{
echo // ============================================================
echo // CARITALENT DUMMY DATA SEEDER
echo // Semua data berbasis konteks Bandung
echo // ============================================================
echo.
echo DB::statement("SET session_replication_role = 'replica';"^) ;
echo.
echo // ============================================================
echo // GENRES
echo // ============================================================
echo DB::table('genres'^)-^>truncate(^);
echo DB::table('genres'^)-^>insert([
echo     ['id' =^> 1,  'name' =^> 'Pop Punk',          'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 2,  'name' =^> 'Heavy Metal',       'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 3,  'name' =^> 'DJ',                'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 4,  'name' =^> 'Solo Singer',       'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 5,  'name' =^> 'Hardcore',          'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 6,  'name' =^> 'Jazz',              'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 7,  'name' =^> 'Seniman Visual',    'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 8,  'name' =^> 'Street Performer',  'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 9,  'name' =^> 'Alternative Rock',  'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 10, 'name' =^> 'Indie Pop',         'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 11, 'name' =^> 'R^&B',               'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 12, 'name' =^> 'Acoustic',          'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo ]);
echo.
echo // ============================================================
echo // USERS
echo // password semua: password123
echo // ============================================================
echo DB::table('users'^)-^>truncate(^);
echo DB::table('users'^)-^>insert([
echo.
echo     // ---- ADMIN ----
echo     [
echo         'id'         =^> 1,
echo         'name'       =^> 'Aprilianza Muhammad Yusup',
echo         'email'      =^> 'aprilianza@caritalent.id',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560001',
echo         'role'       =^> 'admin',
echo         'created_at' =^> '2026-01-01 08:00:00',
echo         'updated_at' =^> '2026-01-01 08:00:00',
echo     ],
echo.
echo     // ---- EO Users ----
echo     [
echo         'id'         =^> 2,
echo         'name'       =^> 'Athila Ramdani Saputra',
echo         'email'      =^> 'athila@kafebraga.id',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560002',
echo         'role'       =^> 'eo',
echo         'created_at' =^> '2026-01-05 09:00:00',
echo         'updated_at' =^> '2026-01-05 09:00:00',
echo     ],
echo     [
echo         'id'         =^> 3,
echo         'name'       =^> 'Bill Stephen Sembiring',
echo         'email'      =^> 'bill@pasarbandoeng.id',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560003',
echo         'role'       =^> 'eo',
echo         'created_at' =^> '2026-01-07 10:00:00',
echo         'updated_at' =^> '2026-01-07 10:00:00',
echo     ],
echo     [
echo         'id'         =^> 8,
echo         'name'       =^> 'Jeany Ferliza Nayla',
echo         'email'      =^> 'jeany@bragapermai.id',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560008',
echo         'role'       =^> 'eo',
echo         'created_at' =^> '2026-01-08 10:30:00',
echo         'updated_at' =^> '2026-01-08 10:30:00',
echo     ],
echo.
echo     // ---- Talent Users ----
echo     [
echo         'id'         =^> 4,
echo         'name'       =^> 'Muhammad Irgiansyah',
echo         'email'      =^> 'irgi@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560004',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-10 11:00:00',
echo         'updated_at' =^> '2026-01-10 11:00:00',
echo     ],
echo     [
echo         'id'         =^> 5,
echo         'name'       =^> 'Arfian Ghifari Mahya',
echo         'email'      =^> 'arfian@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560005',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-12 11:30:00',
echo         'updated_at' =^> '2026-01-12 11:30:00',
echo     ],
echo     // Talent tambahan
echo     [
echo         'id'         =^> 6,
echo         'name'       =^> 'Rizky Maulana',
echo         'email'      =^> 'rizky.maulana@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560006',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-15 12:00:00',
echo         'updated_at' =^> '2026-01-15 12:00:00',
echo     ],
echo     [
echo         'id'         =^> 7,
echo         'name'       =^> 'Siti Nurhaliza Dewi',
echo         'email'      =^> 'siti.ndewi@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560007',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-18 13:00:00',
echo         'updated_at' =^> '2026-01-18 13:00:00',
echo     ],
echo     [
echo         'id'         =^> 9,
echo         'name'       =^> 'Dendi Prasetyo',
echo         'email'      =^> 'dendi.pras@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560009',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-20 09:00:00',
echo         'updated_at' =^> '2026-01-20 09:00:00',
echo     ],
echo     [
echo         'id'         =^> 10,
echo         'name'       =^> 'Fauzan Akbar Nugraha',
echo         'email'      =^> 'fauzan.akbar@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560010',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-22 10:00:00',
echo         'updated_at' =^> '2026-01-22 10:00:00',
echo     ],
echo     [
echo         'id'         =^> 11,
echo         'name'       =^> 'Hendra Wijaya',
echo         'email'      =^> 'hendra.wijaya@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560011',
echo         'role'       =^> 'eo',
echo         'created_at' =^> '2026-01-25 11:00:00',
echo         'updated_at' =^> '2026-01-25 11:00:00',
echo     ],
echo     [
echo         'id'         =^> 12,
echo         'name'       =^> 'Nandita Kusuma Wardhani',
echo         'email'      =^> 'nandita.kw@gmail.com',
echo         'password'   =^> Hash::make('password123'^),
echo         'phone'      =^> '081234560012',
echo         'role'       =^> 'talent',
echo         'created_at' =^> '2026-01-28 09:30:00',
echo         'updated_at' =^> '2026-01-28 09:30:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // TALENT PROFILES
echo // ============================================================
echo DB::table('talents'^)-^>truncate(^);
echo DB::table('talents'^)-^>insert([
echo.
echo     // Talent 1: Irgiansyah - Band Pop Punk (VERIFIED, banyak review)
echo     [
echo         'id'             =^> 1,
echo         'user_id'        =^> 4,
echo         'stage_name'     =^> 'The Rotten Bandung',
echo         'price_min'      =^> 500000,
echo         'price_max'      =^> 2000000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Band pop punk asal Dago Bandung, aktif sejak 2019. Sering manggung di kafe dan venue indie Bandung. Repertoar hits: Peach (The Jansen), Risk It All (Bruno Mars), Kamu Cuma Mau Enaknya Aja (Juicy Luicy), dan Bintang di Surga (Peterpan).',
echo         'portfolio_link' =^> 'https://youtube.com/@therottenbandung',
echo         'verified'       =^> true,
echo         'average_rating' =^> 4.80,
echo         'total_reviews'  =^> 5,
echo         'created_at'     =^> '2026-01-10 11:30:00',
echo         'updated_at'     =^> '2026-03-20 10:00:00',
echo     ],
echo.
echo     // Talent 2: Arfian - DJ (VERIFIED)
echo     [
echo         'id'             =^> 2,
echo         'user_id'        =^> 5,
echo         'stage_name'     =^> 'DJ Arfz Bdg',
echo         'price_min'      =^> 800000,
echo         'price_max'      =^> 3500000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'DJ asal Bandung Selatan dengan pengalaman 4 tahun. Spesialis EDM, Hiphop, dan Pop remix. Pernah mengisi di Braga Festival, Dago Culinary Night, dan Summarecon Mal Bandung.',
echo         'portfolio_link' =^> 'https://soundcloud.com/djarzfbdg',
echo         'verified'       =^> true,
echo         'average_rating' =^> 4.50,
echo         'total_reviews'  =^> 2,
echo         'created_at'     =^> '2026-01-12 12:00:00',
echo         'updated_at'     =^> '2026-03-10 09:00:00',
echo     ],
echo.
echo     // Talent 3: Rizky - Solo Singer (VERIFIED)
echo     [
echo         'id'             =^> 3,
echo         'user_id'        =^> 6,
echo         'stage_name'     =^> 'Rizky Maulana Acoustic',
echo         'price_min'      =^> 300000,
echo         'price_max'      =^> 1200000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Penyanyi solo dengan gitar akustik khas Bandung. Membawakan lagu-lagu Dewa 19, Sheila on 7, Juicy Luicy, dan hits OPM. Cocok untuk suasana kafe intimate dan dinner.',
echo         'portfolio_link' =^> 'https://youtube.com/@rizkymaulanaacoustic',
echo         'verified'       =^> true,
echo         'average_rating' =^> 4.67,
echo         'total_reviews'  =^> 3,
echo         'created_at'     =^> '2026-01-15 12:30:00',
echo         'updated_at'     =^> '2026-03-15 11:00:00',
echo     ],
echo.
echo     // Talent 4: Siti - Jazz Singer (VERIFIED)
echo     [
echo         'id'             =^> 4,
echo         'user_id'        =^> 7,
echo         'stage_name'     =^> 'Siti ND Jazz',
echo         'price_min'      =^> 600000,
echo         'price_max'      =^> 2500000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Vokalis jazz dan R^&B lulusan ISI Bandung. Membawakan jazz standar, bossa nova, hingga jazz-pop modern. Pernah tampil di Braga City Walk, 23 Paskal, dan berbagai pesta pernikahan mewah Bandung.',
echo         'portfolio_link' =^> 'https://instagram.com/sitindjazz',
echo         'verified'       =^> true,
echo         'average_rating' =^> 4.90,
echo         'total_reviews'  =^> 4,
echo         'created_at'     =^> '2026-01-18 13:30:00',
echo         'updated_at'     =^> '2026-03-18 14:00:00',
echo     ],
echo.
echo     // Talent 5: Dendi - Band Heavy Metal (UNVERIFIED - baru daftar, belum ada review)
echo     [
echo         'id'             =^> 5,
echo         'user_id'        =^> 9,
echo         'stage_name'     =^> 'Altar Sunda',
echo         'price_min'      =^> 700000,
echo         'price_max'      =^> 2800000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Band metal asal Cimahi-Bandung yang membawakan heavy metal dan thrash. Terinspirasi dari Burgerkill, Seringai, dan Metallica. Energi tinggi di atas panggung.',
echo         'portfolio_link' =^> 'https://youtube.com/@altarsunda',
echo         'verified'       =^> false,
echo         'average_rating' =^> 0.00,
echo         'total_reviews'  =^> 0,
echo         'created_at'     =^> '2026-01-20 09:30:00',
echo         'updated_at'     =^> '2026-01-20 09:30:00',
echo     ],
echo.
echo     // Talent 6: Fauzan - Band Indie Pop (UNVERIFIED - baru daftar)
echo     [
echo         'id'             =^> 6,
echo         'user_id'        =^> 10,
echo         'stage_name'     =^> 'Langit Sore',
echo         'price_min'      =^> 400000,
echo         'price_max'      =^> 1500000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Duo indie pop Bandung dengan nuansa dreamy dan lo-fi. Membawakan lagu sendiri dan cover hits The Jansen, Hindia, serta Feast. Cocok untuk suasana sore santai.',
echo         'portfolio_link' =^> 'https://spotify.com/artist/langitsore',
echo         'verified'       =^> false,
echo         'average_rating' =^> 0.00,
echo         'total_reviews'  =^> 0,
echo         'created_at'     =^> '2026-01-22 10:30:00',
echo         'updated_at'     =^> '2026-01-22 10:30:00',
echo     ],
echo.
echo     // Talent 7: Nandita - Seniman Visual / Street Performer (VERIFIED, 1 review)
echo     [
echo         'id'             =^> 7,
echo         'user_id'        =^> 12,
echo         'stage_name'     =^> 'Nandita Visual Art',
echo         'price_min'      =^> 250000,
echo         'price_max'      =^> 1000000,
echo         'city'           =^> 'Bandung',
echo         'bio'            =^> 'Seniman visual dan live painter asal Bandung. Spesialis live mural, lukis kanvas di depan penonton, dan performance art. Telah tampil di berbagai festival seni Bandung termasuk Bandung Art Month.',
echo         'portfolio_link' =^> 'https://instagram.com/nanditavisualart',
echo         'verified'       =^> true,
echo         'average_rating' =^> 5.00,
echo         'total_reviews'  =^> 1,
echo         'created_at'     =^> '2026-01-28 10:00:00',
echo         'updated_at'     =^> '2026-03-25 15:00:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // TALENT - GENRE PIVOT
echo // ============================================================
echo DB::table('genre_talent'^)-^>truncate(^);
echo DB::table('genre_talent'^)-^>insert([
echo     // The Rotten Bandung: Pop Punk, Alternative Rock, Hardcore
echo     ['talent_id' =^> 1, 'genre_id' =^> 1],
echo     ['talent_id' =^> 1, 'genre_id' =^> 9],
echo     ['talent_id' =^> 1, 'genre_id' =^> 5],
echo     // DJ Arfz Bdg: DJ
echo     ['talent_id' =^> 2, 'genre_id' =^> 3],
echo     // Rizky Maulana: Solo Singer, Acoustic, Indie Pop
echo     ['talent_id' =^> 3, 'genre_id' =^> 4],
echo     ['talent_id' =^> 3, 'genre_id' =^> 12],
echo     ['talent_id' =^> 3, 'genre_id' =^> 10],
echo     // Siti ND Jazz: Jazz, R^&B, Solo Singer
echo     ['talent_id' =^> 4, 'genre_id' =^> 6],
echo     ['talent_id' =^> 4, 'genre_id' =^> 11],
echo     ['talent_id' =^> 4, 'genre_id' =^> 4],
echo     // Altar Sunda: Heavy Metal, Hardcore
echo     ['talent_id' =^> 5, 'genre_id' =^> 2],
echo     ['talent_id' =^> 5, 'genre_id' =^> 5],
echo     // Langit Sore: Indie Pop, Acoustic, Alternative Rock
echo     ['talent_id' =^> 6, 'genre_id' =^> 10],
echo     ['talent_id' =^> 6, 'genre_id' =^> 12],
echo     ['talent_id' =^> 6, 'genre_id' =^> 9],
echo     // Nandita Visual Art: Seniman Visual, Street Performer
echo     ['talent_id' =^> 7, 'genre_id' =^> 7],
echo     ['talent_id' =^> 7, 'genre_id' =^> 8],
echo ]);
echo.
echo // ============================================================
echo // PORTFOLIO MEDIA
echo // ============================================================
echo DB::table('media'^)-^>truncate(^);
echo DB::table('media'^)-^>insert([
echo     ['id' =^> 1,  'talent_id' =^> 1, 'media_url' =^> 'https://storage.caritalent.id/media/talent1_live_braga.jpg',       'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 2,  'talent_id' =^> 1, 'media_url' =^> 'https://storage.caritalent.id/media/talent1_cover_peach.mp4',       'type' =^> 'video', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 3,  'talent_id' =^> 1, 'media_url' =^> 'https://storage.caritalent.id/media/talent1_promo.jpg',             'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 4,  'talent_id' =^> 2, 'media_url' =^> 'https://storage.caritalent.id/media/talent2_djset_dago.mp4',        'type' =^> 'video', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 5,  'talent_id' =^> 2, 'media_url' =^> 'https://storage.caritalent.id/media/talent2_booth_setup.jpg',       'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 6,  'talent_id' =^> 3, 'media_url' =^> 'https://storage.caritalent.id/media/talent3_acoustic_kafe.jpg',     'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 7,  'talent_id' =^> 3, 'media_url' =^> 'https://storage.caritalent.id/media/talent3_cover_kamu.mp3',        'type' =^> 'audio', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 8,  'talent_id' =^> 4, 'media_url' =^> 'https://storage.caritalent.id/media/talent4_jazz_braga.jpg',        'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 9,  'talent_id' =^> 4, 'media_url' =^> 'https://storage.caritalent.id/media/talent4_performance_clip.mp4',  'type' =^> 'video', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 10, 'talent_id' =^> 5, 'media_url' =^> 'https://storage.caritalent.id/media/talent5_metal_rehearsal.jpg',   'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 11, 'talent_id' =^> 6, 'media_url' =^> 'https://storage.caritalent.id/media/talent6_indiepop_cover.jpg',    'type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo     ['id' =^> 12, 'talent_id' =^> 7, 'media_url' =^> 'https://storage.caritalent.id/media/talent7_livepaint_festival.jpg','type' =^> 'image', 'created_at' =^> now(^), 'updated_at' =^> now(^)],
echo ]);
echo.
echo // ============================================================
echo // EVENTS
echo // EO 2 = Athila (Kafe Braga Permai), EO 3 = Bill (Pasar Bandoeng), EO 8 = Jeany (Braga Art Space), EO 11 = Hendra (Kopi Selasar)
echo // ============================================================
echo DB::table('events'^)-^>truncate(^);
echo DB::table('events'^)-^>insert([
echo.
echo     // --- Event 1: OPEN — cocok untuk apply talent ---
echo     [
echo         'id'           =^> 1,
echo         'organizer_id'      =^> 2,
echo         'title'        =^> 'Braga Punk Night Vol.5',
echo         'description'  =^> 'Malam punk rock bulanan di Kafe Braga Permai. Kami mencari band energetik yang siap mengguncang panggung. Setlist wajib ada cover The Jansen dan Neck Deep.',
echo         'budget'       =^> 2000000,
echo         'event_date'   =^> '2026-05-10',
echo         'venue_name'   =^> 'Kafe Braga Permai',
echo         'latitude'     =^> -6.9109,
echo         'longitude'    =^> 107.6089,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'open',
echo         'created_at'   =^> '2026-04-01 10:00:00',
echo         'updated_at'   =^> '2026-04-01 10:00:00',
echo     ],
echo.
echo     // --- Event 2: OPEN — mencari DJ ---
echo     [
echo         'id'           =^> 2,
echo         'organizer_id'      =^> 3,
echo         'title'        =^> 'Pasar Bandoeng Weekend Vibes',
echo         'description'  =^> 'Event weekend di Pasar Bandoeng Kota Baru Parahyangan. Butuh DJ untuk mengisi suasana dari sore hingga malam dengan lagu-lagu pop, hiphop, dan EDM hype.',
echo         'budget'       =^> 3000000,
echo         'event_date'   =^> '2026-05-17',
echo         'venue_name'   =^> 'Pasar Bandoeng - Kota Baru Parahyangan',
echo         'latitude'     =^> -6.8380,
echo         'longitude'    =^> 107.5361,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'open',
echo         'created_at'   =^> '2026-04-02 11:00:00',
echo         'updated_at'   =^> '2026-04-02 11:00:00',
echo     ],
echo.
echo     // --- Event 3: OPEN — mencari Jazz Singer ---
echo     [
echo         'id'           =^> 3,
echo         'organizer_id'      =^> 8,
echo         'title'        =^> 'Braga Jazz Evening',
echo         'description'  =^> 'Evening jazz intimate di Braga Art Space. Mencari penyanyi jazz berbakat untuk menemani tamu menikmati dinner dan wine. Repertoar jazz standar dan bossa nova diutamakan.',
echo         'budget'       =^> 2500000,
echo         'event_date'   =^> '2026-05-24',
echo         'venue_name'   =^> 'Braga Art Space',
echo         'latitude'     =^> -6.9116,
echo         'longitude'    =^> 107.6095,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'open',
echo         'created_at'   =^> '2026-04-03 09:00:00',
echo         'updated_at'   =^> '2026-04-03 09:00:00',
echo     ],
echo.
echo     // --- Event 4: OPEN — mencari Acoustic / Solo Singer ---
echo     [
echo         'id'           =^> 4,
echo         'organizer_id'      =^> 11,
echo         'title'        =^> 'Kopi Selasar Acoustic Sunday',
echo         'description'  =^> 'Sesi acoustic rutin tiap minggu di Kopi Selasar Sunaryo. Suasana santai, butuh musisi atau penyanyi dengan gitar akustik. Lagu-lagu hits seperti Dewa 19, Juicy Luicy, dan Bruno Mars sangat cocok.',
echo         'budget'       =^> 800000,
echo         'event_date'   =^> '2026-05-03',
echo         'venue_name'   =^> 'Kopi Selasar Sunaryo Art Space',
echo         'latitude'     =^> -6.8733,
echo         'longitude'    =^> 107.6218,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'open',
echo         'created_at'   =^> '2026-04-04 08:00:00',
echo         'updated_at'   =^> '2026-04-04 08:00:00',
echo     ],
echo.
echo     // --- Event 5: DRAFT — belum dipublish EO ---
echo     [
echo         'id'           =^> 5,
echo         'organizer_id'      =^> 2,
echo         'title'        =^> 'Braga Indie Fest 2026',
echo         'description'  =^> 'Festival indie tahunan Braga. Masih dalam tahap perencanaan, butuh beberapa band indie pop dan alternative untuk lineup.',
echo         'budget'       =^> 5000000,
echo         'event_date'   =^> '2026-06-20',
echo         'venue_name'   =^> 'Lapangan Kafe Braga Permai',
echo         'latitude'     =^> -6.9109,
echo         'longitude'    =^> 107.6089,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'draft',
echo         'created_at'   =^> '2026-04-05 14:00:00',
echo         'updated_at'   =^> '2026-04-05 14:00:00',
echo     ],
echo.
echo     // --- Event 6: CLOSED — sudah ditutup penerimaannya ---
echo     [
echo         'id'           =^> 6,
echo         'organizer_id'      =^> 3,
echo         'title'        =^> 'Pasar Bandoeng Metal Malam',
echo         'description'  =^> 'Malam heavy metal khusus untuk komunitas underground Bandung. Sudah menemukan band yang cocok.',
echo         'budget'       =^> 2500000,
echo         'event_date'   =^> '2026-05-01',
echo         'venue_name'   =^> 'Pasar Bandoeng - Kota Baru Parahyangan',
echo         'latitude'     =^> -6.8380,
echo         'longitude'    =^> 107.5361,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'closed',
echo         'created_at'   =^> '2026-03-15 10:00:00',
echo         'updated_at'   =^> '2026-03-28 16:00:00',
echo     ],
echo.
echo     // --- Event 7: COMPLETED — sudah selesai, ada review ---
echo     [
echo         'id'           =^> 7,
echo         'organizer_id'      =^> 2,
echo         'title'        =^> 'Braga Punk Night Vol.4',
echo         'description'  =^> 'Edisi keempat punk night Braga. Sudah berlangsung.',
echo         'budget'       =^> 1800000,
echo         'event_date'   =^> '2026-03-15',
echo         'venue_name'   =^> 'Kafe Braga Permai',
echo         'latitude'     =^> -6.9109,
echo         'longitude'    =^> 107.6089,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'completed',
echo         'created_at'   =^> '2026-02-20 10:00:00',
echo         'updated_at'   =^> '2026-03-16 09:00:00',
echo     ],
echo.
echo     // --- Event 8: COMPLETED — sudah selesai ---
echo     [
echo         'id'           =^> 8,
echo         'organizer_id'      =^> 8,
echo         'title'        =^> 'Braga Jazz Evening Maret',
echo         'description'  =^> 'Sesi jazz maret di Braga Art Space. Sudah selesai.',
echo         'budget'       =^> 2500000,
echo         'event_date'   =^> '2026-03-22',
echo         'venue_name'   =^> 'Braga Art Space',
echo         'latitude'     =^> -6.9116,
echo         'longitude'    =^> 107.6095,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'completed',
echo         'created_at'   =^> '2026-02-25 09:00:00',
echo         'updated_at'   =^> '2026-03-23 10:00:00',
echo     ],
echo.
echo     // --- Event 9: COMPLETED — sudah selesai ---
echo     [
echo         'id'           =^> 9,
echo         'organizer_id'      =^> 11,
echo         'title'        =^> 'Kopi Selasar Acoustic Maret',
echo         'description'  =^> 'Sesi acoustic bulan maret di Selasar. Sudah selesai.',
echo         'budget'       =^> 700000,
echo         'event_date'   =^> '2026-03-09',
echo         'venue_name'   =^> 'Kopi Selasar Sunaryo Art Space',
echo         'latitude'     =^> -6.8733,
echo         'longitude'    =^> 107.6218,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'completed',
echo         'created_at'   =^> '2026-02-10 08:00:00',
echo         'updated_at'   =^> '2026-03-10 08:00:00',
echo     ],
echo.
echo     // --- Event 10: COMPLETED ---
echo     [
echo         'id'           =^> 10,
echo         'organizer_id'      =^> 3,
echo         'title'        =^> 'Pasar Bandoeng DJ Night Februari',
echo         'description'  =^> 'DJ Night Februari di Pasar Bandoeng. Sudah selesai.',
echo         'budget'       =^> 3000000,
echo         'event_date'   =^> '2026-02-22',
echo         'venue_name'   =^> 'Pasar Bandoeng - Kota Baru Parahyangan',
echo         'latitude'     =^> -6.8380,
echo         'longitude'    =^> 107.5361,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'completed',
echo         'created_at'   =^> '2026-01-25 10:00:00',
echo         'updated_at'   =^> '2026-02-23 09:00:00',
echo     ],
echo.
echo     // --- Event 11: CANCELLED ---
echo     [
echo         'id'           =^> 11,
echo         'organizer_id'      =^> 8,
echo         'title'        =^> 'Braga Art Night - Dibatalkan',
echo         'description'  =^> 'Event seni visual malam yang terpaksa dibatalkan karena perubahan jadwal venue.',
echo         'budget'       =^> 1500000,
echo         'event_date'   =^> '2026-04-05',
echo         'venue_name'   =^> 'Braga Art Space',
echo         'latitude'     =^> -6.9116,
echo         'longitude'    =^> 107.6095,
echo         'city'         =^> 'Bandung',
echo         'status'       =^> 'cancelled',
echo         'created_at'   =^> '2026-03-01 09:00:00',
echo         'updated_at'   =^> '2026-03-20 11:00:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // EVENT - GENRE PIVOT
echo // ============================================================
echo DB::table('event_genre'^)-^>truncate(^);
echo DB::table('event_genre'^)-^>insert([
echo     // Event 1 - Punk Night: Pop Punk, Hardcore, Alternative Rock
echo     ['event_id' =^> 1, 'genre_id' =^> 1],
echo     ['event_id' =^> 1, 'genre_id' =^> 5],
echo     ['event_id' =^> 1, 'genre_id' =^> 9],
echo     // Event 2 - Weekend Vibes: DJ
echo     ['event_id' =^> 2, 'genre_id' =^> 3],
echo     // Event 3 - Jazz Evening: Jazz, R^&B
echo     ['event_id' =^> 3, 'genre_id' =^> 6],
echo     ['event_id' =^> 3, 'genre_id' =^> 11],
echo     // Event 4 - Acoustic Sunday: Solo Singer, Acoustic, Indie Pop
echo     ['event_id' =^> 4, 'genre_id' =^> 4],
echo     ['event_id' =^> 4, 'genre_id' =^> 12],
echo     ['event_id' =^> 4, 'genre_id' =^> 10],
echo     // Event 5 - Indie Fest: Indie Pop, Alternative Rock
echo     ['event_id' =^> 5, 'genre_id' =^> 10],
echo     ['event_id' =^> 5, 'genre_id' =^> 9],
echo     // Event 6 - Metal Night: Heavy Metal, Hardcore
echo     ['event_id' =^> 6, 'genre_id' =^> 2],
echo     ['event_id' =^> 6, 'genre_id' =^> 5],
echo     // Event 7 - Punk Vol.4: Pop Punk, Alternative Rock
echo     ['event_id' =^> 7, 'genre_id' =^> 1],
echo     ['event_id' =^> 7, 'genre_id' =^> 9],
echo     // Event 8 - Jazz Maret: Jazz, R^&B
echo     ['event_id' =^> 8, 'genre_id' =^> 6],
echo     ['event_id' =^> 8, 'genre_id' =^> 11],
echo     // Event 9 - Acoustic Maret: Acoustic, Solo Singer
echo     ['event_id' =^> 9, 'genre_id' =^> 12],
echo     ['event_id' =^> 9, 'genre_id' =^> 4],
echo     // Event 10 - DJ Night: DJ
echo     ['event_id' =^> 10, 'genre_id' =^> 3],
echo     // Event 11 - Art Night: Seniman Visual, Street Performer
echo     ['event_id' =^> 11, 'genre_id' =^> 7],
echo     ['event_id' =^> 11, 'genre_id' =^> 8],
echo ]);
echo.
echo // ============================================================
echo // APPLICATIONS
echo // Mencakup berbagai kondisi: pending, accepted, rejected, cancelled
echo // source: apply / invitation
echo // ============================================================
echo DB::table('applications'^)-^>truncate(^);
echo DB::table('applications'^)-^>insert([
echo.
echo     // == KONDISI 1: Apply biasa, masih PENDING (Event 1 - Punk Night) ==
echo     // The Rotten Bandung apply ke Braga Punk Night Vol.5
echo     [
echo         'id'             =^> 1,
echo         'event_id'       =^> 1,
echo         'talent_id'      =^> 4,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Halo. Kami The Rotten Bandung, band pop punk dengan pengalaman 5 tahun. Siap tampil maksimal di Braga Punk Night. Setlist kami ada cover Peach (The Jansen), Risk It All (Bruno Mars versi punk), dan beberapa lagu original.',
echo         'proposed_price' =^> 1500000,
echo         'status'         =^> 'pending',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-02 13:00:00',
echo         'updated_at'     =^> '2026-04-02 13:00:00',
echo     ],
echo     // Langit Sore juga apply ke Braga Punk Night Vol.5 (kurang cocok genre tapi tetap coba)
echo     [
echo         'id'             =^> 2,
echo         'event_id'       =^> 1,
echo         'talent_id'      =^> 10,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Halo, kami Langit Sore, duo indie dengan nuansa alternative. Kami bisa membawakan set yang energetik untuk Punk Night, dengan sentuhan alternative yang fresh.',
echo         'proposed_price' =^> 1200000,
echo         'status'         =^> 'pending',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-03 10:00:00',
echo         'updated_at'     =^> '2026-04-03 10:00:00',
echo     ],
echo.
echo     // == KONDISI 2: Apply REJECTED (Event 2 - DJ Night, talent yang apply bukan DJ) ==
echo     // The Rotten Bandung apply ke DJ night, di-reject karena genre tidak cocok
echo     [
echo         'id'             =^> 3,
echo         'event_id'       =^> 2,
echo         'talent_id'      =^> 4,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Kami bisa membawakan suasana seru dengan band, meski ini event DJ night.',
echo         'proposed_price' =^> 1000000,
echo         'status'         =^> 'rejected',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-02 14:00:00',
echo         'updated_at'     =^> '2026-04-03 09:00:00',
echo     ],
echo     // DJ Arfz apply ke Event 2 - ACCEPTED (akan membuat booking)
echo     [
echo         'id'             =^> 4,
echo         'event_id'       =^> 2,
echo         'talent_id'      =^> 5,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Saya DJ Arfz dari Bandung Selatan, spesialis EDM dan Pop remix. Siap mengisi Weekend Vibes Pasar Bandoeng dari jam 16.00 sampai selesai.',
echo         'proposed_price' =^> 2500000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 2500000,
echo         'created_at'     =^> '2026-04-02 15:00:00',
echo         'updated_at'     =^> '2026-04-03 10:00:00',
echo     ],
echo.
echo     // == KONDISI 3: Apply ke Event Jazz, lanjut ke booking ==
echo     // Siti ND Jazz apply ke Braga Jazz Evening - ACCEPTED
echo     [
echo         'id'             =^> 5,
echo         'event_id'       =^> 3,
echo         'talent_id'      =^> 7,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Selamat siang. Saya Siti ND, vokalis jazz lulusan ISI Bandung. Sangat tertarik untuk tampil di Braga Jazz Evening. Repertoar saya mencakup jazz standar, bossa nova, dan jazz-pop. Bisa menyesuaikan suasana dinner Anda.',
echo         'proposed_price' =^> 2000000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 2000000,
echo         'created_at'     =^> '2026-04-03 11:00:00',
echo         'updated_at'     =^> '2026-04-04 09:00:00',
echo     ],
echo     // Rizky juga apply ke Jazz Evening - REJECTED (sudah ada yang accepted)
echo     [
echo         'id'             =^> 6,
echo         'event_id'       =^> 3,
echo         'talent_id'      =^> 6,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Saya Rizky, penyanyi acoustic. Bisa juga membawakan jazz-pop ringan untuk dinner.',
echo         'proposed_price' =^> 900000,
echo         'status'         =^> 'rejected',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-03 12:00:00',
echo         'updated_at'     =^> '2026-04-04 09:30:00',
echo     ],
echo.
echo     // == KONDISI 4: Invitation dari EO ke Talent (Event 4 - Acoustic Sunday) ==
echo     // EO Hendra invites Rizky Maulana lewat invitation
echo     [
echo         'id'             =^> 7,
echo         'event_id'       =^> 4,
echo         'talent_id'      =^> 6,
echo         'source'         =^> 'invitation',
echo         'message'        =^> 'Kami mengundang Anda untuk tampil di Kopi Selasar Acoustic Sunday. Kami sudah menonton performa Anda dan yakin cocok dengan suasana kami.',
echo         'proposed_price' =^> 700000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 700000,
echo         'created_at'     =^> '2026-04-04 10:00:00',
echo         'updated_at'     =^> '2026-04-04 14:00:00',
echo     ],
echo.
echo     // == KONDISI 5: Invitation pending, belum direspons talent ==
echo     // EO Athila invites Langit Sore untuk Event 5 Indie Fest (masih draft, tapi invite duluan)
echo     [
echo         'id'             =^> 8,
echo         'event_id'       =^> 5,
echo         'talent_id'      =^> 10,
echo         'source'         =^> 'invitation',
echo         'message'        =^> 'Halo Langit Sore. Kami sedang mempersiapkan Braga Indie Fest 2026 dan sangat tertarik mengundang kalian sebagai salah satu lineup. Harga yang kami tawarkan 1.2jt.',
echo         'proposed_price' =^> 1200000,
echo         'status'         =^> 'pending',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-06 09:00:00',
echo         'updated_at'     =^> '2026-04-06 09:00:00',
echo     ],
echo.
echo     // == KONDISI 6: Invitation REJECTED oleh talent ==
echo     // EO Bill invites Altar Sunda ke Event 6 Metal Night - talent reject
echo     [
echo         'id'             =^> 9,
echo         'event_id'       =^> 6,
echo         'talent_id'      =^> 9,
echo         'source'         =^> 'invitation',
echo         'message'        =^> 'Halo Altar Sunda. Kami butuh band metal untuk Pasar Bandoeng Metal Malam. Tertarik?',
echo         'proposed_price' =^> 2000000,
echo         'status'         =^> 'rejected',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-03-16 10:00:00',
echo         'updated_at'     =^> '2026-03-17 09:00:00',
echo     ],
echo.
echo     // == KONDISI 7: Apply yang kemudian di-CANCEL oleh talent (masih pending) ==
echo     // Altar Sunda apply ke Braga Punk Night Vol.5, lalu cancel
echo     [
echo         'id'             =^> 10,
echo         'event_id'       =^> 1,
echo         'talent_id'      =^> 9,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Kami Altar Sunda, metal dari Bandung, bisa membawakan energi hardcore untuk Punk Night.',
echo         'proposed_price' =^> 1800000,
echo         'status'         =^> 'rejected',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-04-02 16:00:00',
echo         'updated_at'     =^> '2026-04-03 08:00:00',
echo     ],
echo.
echo     // == KONDISI COMPLETED: Applications untuk event yang sudah selesai ==
echo     // Event 7 (Punk Vol.4) - The Rotten Bandung tampil, booking completed
echo     [
echo         'id'             =^> 11,
echo         'event_id'       =^> 7,
echo         'talent_id'      =^> 4,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'The Rotten Bandung siap untuk Punk Night Vol.4.',
echo         'proposed_price' =^> 1500000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 1500000,
echo         'created_at'     =^> '2026-02-21 10:00:00',
echo         'updated_at'     =^> '2026-02-22 09:00:00',
echo     ],
echo     // Event 8 (Jazz Maret) - Siti ND, booking completed
echo     [
echo         'id'             =^> 12,
echo         'event_id'       =^> 8,
echo         'talent_id'      =^> 7,
echo         'source'         =^> 'invitation',
echo         'message'        =^> 'Terima kasih atas undangannya, saya sangat senang bisa tampil di Braga Art Space.',
echo         'proposed_price' =^> 2000000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 2000000,
echo         'created_at'     =^> '2026-02-26 09:00:00',
echo         'updated_at'     =^> '2026-02-27 10:00:00',
echo     ],
echo     // Event 9 (Acoustic Maret) - Rizky Maulana, booking completed
echo     [
echo         'id'             =^> 13,
echo         'event_id'       =^> 9,
echo         'talent_id'      =^> 6,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Siap tampil di Kopi Selasar.',
echo         'proposed_price' =^> 600000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 600000,
echo         'created_at'     =^> '2026-02-11 09:00:00',
echo         'updated_at'     =^> '2026-02-12 10:00:00',
echo     ],
echo     // Event 10 (DJ Night Feb) - DJ Arfz, booking completed
echo     [
echo         'id'             =^> 14,
echo         'event_id'       =^> 10,
echo         'talent_id'      =^> 5,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'DJ Arfz siap guncang Pasar Bandoeng.',
echo         'proposed_price' =^> 2500000,
echo         'status'         =^> 'accepted',
echo         'offered_price'   =^> 2500000,
echo         'created_at'     =^> '2026-01-26 10:00:00',
echo         'updated_at'     =^> '2026-01-27 09:00:00',
echo     ],
echo     // Event 7 (Punk Vol.4) - Langit Sore juga apply tapi rejected
echo     [
echo         'id'             =^> 15,
echo         'event_id'       =^> 7,
echo         'talent_id'      =^> 10,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Langit Sore ingin ikut Punk Vol.4.',
echo         'proposed_price' =^> 1000000,
echo         'status'         =^> 'rejected',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-02-21 11:00:00',
echo         'updated_at'     =^> '2026-02-22 09:30:00',
echo     ],
echo     // Event 11 (Art Night - CANCELLED) - Nandita Visual Art sudah apply sebelum cancel
echo     [
echo         'id'             =^> 16,
echo         'event_id'       =^> 11,
echo         'talent_id'      =^> 12,
echo         'source'         =^> 'apply',
echo         'message'        =^> 'Nandita Visual Art siap untuk live painting di Braga Art Night.',
echo         'proposed_price' =^> 800000,
echo         'status'         =^> 'pending',
echo         'offered_price'   =^> null,
echo         'created_at'     =^> '2026-03-02 10:00:00',
echo         'updated_at'     =^> '2026-03-02 10:00:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // BOOKINGS
echo // Dibuat otomatis saat application accepted
echo // ============================================================
echo DB::table('bookings'^)-^>truncate(^);
echo DB::table('bookings'^)-^>insert([
echo.
echo     // Booking 1: Event 2 (DJ Night) - DJ Arfz - CONFIRMED (belum terlaksana)
echo     [
echo         'id'             =^> 1,
echo         'application_id' =^> 4,
echo         'agreed_price'   =^> 2500000,
echo         'status'         =^> 'confirmed',
echo         'created_at'     =^> '2026-04-03 10:05:00',
echo         'updated_at'     =^> '2026-04-03 10:05:00',
echo     ],
echo     // Booking 2: Event 3 (Jazz Evening) - Siti ND - CONFIRMED (belum terlaksana)
echo     [
echo         'id'             =^> 2,
echo         'application_id' =^> 5,
echo         'agreed_price'   =^> 2000000,
echo         'status'         =^> 'confirmed',
echo         'created_at'     =^> '2026-04-04 09:05:00',
echo         'updated_at'     =^> '2026-04-04 09:05:00',
echo     ],
echo     // Booking 3: Event 4 (Acoustic Sunday) - Rizky - CONFIRMED (belum terlaksana)
echo     [
echo         'id'             =^> 3,
echo         'application_id' =^> 7,
echo         'agreed_price'   =^> 700000,
echo         'status'         =^> 'confirmed',
echo         'created_at'     =^> '2026-04-04 14:05:00',
echo         'updated_at'     =^> '2026-04-04 14:05:00',
echo     ],
echo.
echo     // Booking 4: Event 7 (Punk Vol.4) - The Rotten Bandung - COMPLETED + ada review
echo     [
echo         'id'             =^> 4,
echo         'application_id' =^> 11,
echo         'agreed_price'   =^> 1500000,
echo         'status'         =^> 'completed',
echo         'created_at'     =^> '2026-02-22 09:05:00',
echo         'updated_at'     =^> '2026-03-16 09:00:00',
echo     ],
echo     // Booking 5: Event 8 (Jazz Maret) - Siti ND - COMPLETED + ada review
echo     [
echo         'id'             =^> 5,
echo         'application_id' =^> 12,
echo         'agreed_price'   =^> 2000000,
echo         'status'         =^> 'completed',
echo         'created_at'     =^> '2026-02-27 10:05:00',
echo         'updated_at'     =^> '2026-03-23 10:00:00',
echo     ],
echo     // Booking 6: Event 9 (Acoustic Maret) - Rizky - COMPLETED + ada review
echo     [
echo         'id'             =^> 6,
echo         'application_id' =^> 13,
echo         'agreed_price'   =^> 600000,
echo         'status'         =^> 'completed',
echo         'created_at'     =^> '2026-02-12 10:05:00',
echo         'updated_at'     =^> '2026-03-10 08:00:00',
echo     ],
echo     // Booking 7: Event 10 (DJ Night Feb) - DJ Arfz - COMPLETED + ada review
echo     [
echo         'id'             =^> 7,
echo         'application_id' =^> 14,
echo         'agreed_price'   =^> 2500000,
echo         'status'         =^> 'completed',
echo         'created_at'     =^> '2026-01-27 09:05:00',
echo         'updated_at'     =^> '2026-02-23 09:00:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // REVIEWS
echo // Hanya setelah booking completed
echo // ============================================================
echo DB::table('reviews'^)-^>truncate(^);
echo DB::table('reviews'^)-^>insert([
echo.
echo     // Review untuk The Rotten Bandung dari Braga Punk Night Vol.4
echo     [
echo         'id'         =^> 1,
echo         'booking_id' =^> 4,
echo         'rating'     =^> 5,
echo         'comment'    =^> 'The Rotten Bandung luar biasa. Energi di panggung sangat tinggi, penonton langsung hype dari lagu pertama. Cover Peach dari The Jansen dibawakan dengan sempurna. Pasti kami undang lagi.',
echo         'created_at' =^> '2026-03-16 20:00:00',
echo         'updated_at' =^> '2026-03-16 20:00:00',
echo     ],
echo     // Review untuk Siti ND dari Jazz Maret
echo     [
echo         'id'         =^> 2,
echo         'booking_id' =^> 5,
echo         'rating'     =^> 5,
echo         'comment'    =^> 'Siti ND sungguh memukau. Suaranya sangat cocok untuk suasana dinner jazz yang kami inginkan. Tamu-tamu sangat terkesan, beberapa bahkan meminta kartu kontaknya langsung. Profesional dan tepat waktu.',
echo         'created_at' =^> '2026-03-23 21:00:00',
echo         'updated_at' =^> '2026-03-23 21:00:00',
echo     ],
echo     // Review untuk Rizky dari Acoustic Maret
echo     [
echo         'id'         =^> 3,
echo         'booking_id' =^> 6,
echo         'rating'     =^> 4,
echo         'comment'    =^> 'Rizky tampil bagus dan bikin suasana Kopi Selasar makin nyaman. Pilihan lagunya pas banget, ada Dewa 19 dan Juicy Luicy. Cukup memuaskan, meski sound system sedikit kurang optimal dari sisinya.',
echo         'created_at' =^> '2026-03-10 20:00:00',
echo         'updated_at' =^> '2026-03-10 20:00:00',
echo     ],
echo     // Review untuk DJ Arfz dari DJ Night Feb
echo     [
echo         'id'         =^> 4,
echo         'booking_id' =^> 7,
echo         'rating'     =^> 4,
echo         'comment'    =^> 'DJ Arfz berhasil bikin Pasar Bandoeng malam itu hidup banget. Set EDM-nya bagus dan crowd terus antusias. Satu catatan kecil: transisi antar lagu di awal agak terburu-buru, tapi keseluruhan memuaskan.',
echo         'created_at' =^> '2026-02-23 22:00:00',
echo         'updated_at' =^> '2026-02-23 22:00:00',
echo     ],
echo ]);
echo.
echo // ============================================================
echo // NOTIFICATIONS
echo // ============================================================
echo DB::table('notifications'^)-^>truncate(^);
echo DB::table('notifications'^)-^>insert([
echo.
echo     // Notif untuk Talent 1 (Irgi/The Rotten Bandung)
echo     ['id' =^> 1,  'user_id' =^> 4, 'title' =^> 'Lamaran Diterima.',            'body' =^> 'Selamat. Lamaran Anda ke Braga Punk Night Vol.4 telah diterima oleh Kafe Braga Permai.',                             'type' =^> 'application', 'reference_id' =^> 11, 'is_read' =^> true,  'created_at' =^> '2026-02-22 09:05:00', 'updated_at' =^> '2026-02-22 09:30:00'],
echo     ['id' =^> 2,  'user_id' =^> 4, 'title' =^> 'Review Baru Masuk',             'body' =^> 'Kafe Braga Permai memberikan review bintang 5 untuk penampilan Anda di Braga Punk Night Vol.4.',                    'type' =^> 'review',       'reference_id' =^> 1,  'is_read' =^> true,  'created_at' =^> '2026-03-16 20:00:00', 'updated_at' =^> '2026-03-17 08:00:00'],
echo     ['id' =^> 3,  'user_id' =^> 4, 'title' =^> 'Lamaran Ditolak',               'body' =^> 'Mohon maaf, lamaran Anda ke Pasar Bandoeng DJ Night ditolak karena genre tidak sesuai.',                             'type' =^> 'application', 'reference_id' =^> 3,  'is_read' =^> false, 'created_at' =^> '2026-04-03 09:00:00', 'updated_at' =^> '2026-04-03 09:00:00'],
echo.
echo     // Notif untuk Talent 2 (Arfian/DJ Arfz)
echo     ['id' =^> 4,  'user_id' =^> 5, 'title' =^> 'Lamaran Diterima.',            'body' =^> 'Selamat. Lamaran Anda ke Pasar Bandoeng Weekend Vibes telah diterima.',                                                'type' =^> 'application', 'reference_id' =^> 4,  'is_read' =^> true,  'created_at' =^> '2026-04-03 10:05:00', 'updated_at' =^> '2026-04-03 10:30:00'],
echo     ['id' =^> 5,  'user_id' =^> 5, 'title' =^> 'Booking Dikonfirmasi',          'body' =^> 'Booking Anda untuk Pasar Bandoeng Weekend Vibes tanggal 17 Mei 2026 sudah dikonfirmasi.',                              'type' =^> 'booking',     'reference_id' =^> 1,  'is_read' =^> false, 'created_at' =^> '2026-04-03 10:05:00', 'updated_at' =^> '2026-04-03 10:05:00'],
echo     ['id' =^> 6,  'user_id' =^> 5, 'title' =^> 'Review Baru Masuk',             'body' =^> 'Pasar Bandoeng memberikan review bintang 4 untuk penampilan DJ Anda di DJ Night Februari.',                           'type' =^> 'review',       'reference_id' =^> 4,  'is_read' =^> true,  'created_at' =^> '2026-02-23 22:00:00', 'updated_at' =^> '2026-02-24 08:00:00'],
echo.
echo     // Notif untuk Talent 3 (Rizky)
echo     ['id' =^> 7,  'user_id' =^> 6, 'title' =^> 'Undangan Manggung Baru.',      'body' =^> 'Kopi Selasar Sunaryo mengundang Anda untuk tampil di Acoustic Sunday 3 Mei 2026. Cek detailnya.',                      'type' =^> 'invitation',  'reference_id' =^> 7,  'is_read' =^> true,  'created_at' =^> '2026-04-04 10:00:00', 'updated_at' =^> '2026-04-04 10:15:00'],
echo     ['id' =^> 8,  'user_id' =^> 6, 'title' =^> 'Undangan Diterima - Booking.', 'body' =^> 'Anda menerima undangan Kopi Selasar. Booking Anda untuk 3 Mei 2026 telah dikonfirmasi.',                               'type' =^> 'booking',     'reference_id' =^> 3,  'is_read' =^> true,  'created_at' =^> '2026-04-04 14:05:00', 'updated_at' =^> '2026-04-04 14:20:00'],
echo     ['id' =^> 9,  'user_id' =^> 6, 'title' =^> 'Lamaran Ditolak',               'body' =^> 'Lamaran Anda ke Braga Jazz Evening ditolak. Jangan menyerah, terus cari event lain.',                                  'type' =^> 'application', 'reference_id' =^> 6,  'is_read' =^> false, 'created_at' =^> '2026-04-04 09:30:00', 'updated_at' =^> '2026-04-04 09:30:00'],
echo.
echo     // Notif untuk Talent 4 (Siti ND)
echo     ['id' =^> 10, 'user_id' =^> 7, 'title' =^> 'Lamaran Diterima.',            'body' =^> 'Selamat. Lamaran Anda ke Braga Jazz Evening 24 Mei 2026 diterima oleh Braga Art Space.',                               'type' =^> 'application', 'reference_id' =^> 5,  'is_read' =^> true,  'created_at' =^> '2026-04-04 09:05:00', 'updated_at' =^> '2026-04-04 09:20:00'],
echo     ['id' =^> 11, 'user_id' =^> 7, 'title' =^> 'Review Baru - Bintang 5.',     'body' =^> 'Braga Art Space memberikan review bintang 5 untuk penampilan Anda di Jazz Maret. Luar biasa.',                         'type' =^> 'review',       'reference_id' =^> 2,  'is_read' =^> false, 'created_at' =^> '2026-03-23 21:00:00', 'updated_at' =^> '2026-03-23 21:00:00'],
echo.
echo     // Notif untuk Talent 5 (Altar Sunda)
echo     ['id' =^> 12, 'user_id' =^> 9, 'title' =^> 'Undangan Ditolak',              'body' =^> 'Anda menolak undangan dari Pasar Bandoeng untuk Metal Malam.',                                                         'type' =^> 'invitation',  'reference_id' =^> 9,  'is_read' =^> true,  'created_at' =^> '2026-03-17 09:00:00', 'updated_at' =^> '2026-03-17 09:30:00'],
echo.
echo     // Notif untuk Talent 6 (Langit Sore)
echo     ['id' =^> 13, 'user_id' =^> 10, 'title' =^> 'Undangan Manggung Baru.',     'body' =^> 'Kafe Braga Permai mengundang Langit Sore untuk tampil di Braga Indie Fest 2026. Cek detailnya.',                        'type' =^> 'invitation',  'reference_id' =^> 8,  'is_read' =^> false, 'created_at' =^> '2026-04-06 09:00:00', 'updated_at' =^> '2026-04-06 09:00:00'],
echo.
echo     // Notif untuk EO 2 (Athila - Kafe Braga Permai)
echo     ['id' =^> 14, 'user_id' =^> 2, 'title' =^> 'Lamaran Baru Masuk.',           'body' =^> 'The Rotten Bandung melamar untuk Braga Punk Night Vol.5. Segera review lamarannya.',                                   'type' =^> 'application', 'reference_id' =^> 1,  'is_read' =^> true,  'created_at' =^> '2026-04-02 13:00:00', 'updated_at' =^> '2026-04-02 13:30:00'],
echo     ['id' =^> 15, 'user_id' =^> 2, 'title' =^> 'Lamaran Baru Masuk.',           'body' =^> 'Langit Sore melamar untuk Braga Punk Night Vol.5.',                                                                    'type' =^> 'application', 'reference_id' =^> 2,  'is_read' =^> false, 'created_at' =^> '2026-04-03 10:00:00', 'updated_at' =^> '2026-04-03 10:00:00'],
echo.
echo     // Notif untuk EO 3 (Bill - Pasar Bandoeng)
echo     ['id' =^> 16, 'user_id' =^> 3, 'title' =^> 'DJ Arfz Terima Booking.',       'body' =^> 'DJ Arfz menerima booking untuk Pasar Bandoeng Weekend Vibes. Event Anda siap.',                                        'type' =^> 'booking',     'reference_id' =^> 1,  'is_read' =^> true,  'created_at' =^> '2026-04-03 10:10:00', 'updated_at' =^> '2026-04-03 10:30:00'],
echo.
echo     // Notif untuk EO 8 (Jeany - Braga Art Space)
echo     ['id' =^> 17, 'user_id' =^> 8, 'title' =^> 'Siti ND Terima Booking.',       'body' =^> 'Siti ND Jazz menerima booking untuk Braga Jazz Evening 24 Mei. Event Anda siap.',                                      'type' =^> 'booking',     'reference_id' =^> 2,  'is_read' =^> false, 'created_at' =^> '2026-04-04 09:10:00', 'updated_at' =^> '2026-04-04 09:10:00'],
echo ]);
echo.
echo DB::statement("SET session_replication_role = 'origin';"^);
echo.
echo echo "=== SEEDER SELESAI ===\n";
echo     ^}
echo ^}
exit /b 0
:SeederDone
echo [SUCCESS] File seeder berhasil dibuat di database/seeders/DummyDataSeeder.php
:: Jalankan seeder via artisan
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
