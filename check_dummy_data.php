<?php

/**
 * CARITALENT DUMMY DATA VERIFIER
 * Script ini digunakan untuk menguji seeder dan memastikan tidak ada field
 * yang kosong/null secara tidak sengaja di seluruh tabel database.
 * 
 * Cara menjalankan: php check_dummy_data.php
 */

echo "\n\e[1;36m=== CARITALENT DUMMY DATA VERIFIER ===\e[0m\n\n";

echo "Menginisialisasi Laravel Application...\n";
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Menggunakan SQLite database temporer agar bisa dicek meskipun PostgreSQL mati
echo "Mengonfigurasi database pengujian (SQLite memory)...\n";
config([
    'database.default' => 'sqlite_verify',
    'database.connections.sqlite_verify' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'foreign_key_constraints' => true,
    ]
]);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    DB::purge();
    
    echo "Running migrations di database pengujian...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    
    echo "Running DummyDataSeeder...\n";
    Artisan::call('db:seed', ['--class' => 'DummyDataSeeder', '--force' => true]);
    
    echo "\e[1;32m✓ Migrasi & Seeding sukses tanpa error!\e[0m\n\n";
    
} catch (\Exception $e) {
    echo "\e[1;31m✗ ERROR saat melakukan setup database pengujian:\e[0m\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

$tables = [
    'users'         => ['nullable' => ['remember_token']],
    'genres'        => ['nullable' => []],
    'talents'       => ['nullable' => []],
    'genre_talent'  => ['nullable' => []],
    'events'        => ['nullable' => ['latitude', 'longitude']],
    'event_genre'   => ['nullable' => []],
    'applications'  => ['nullable' => ['message', 'proposed_price', 'offered_price']],
    'bookings'      => ['nullable' => []],
    'reviews'       => ['nullable' => []],
    'notifications' => ['nullable' => ['action', 'reference_type', 'reference_id', 'data', 'read_at']],
];

$hasWarning = false;

foreach ($tables as $table => $meta) {
    echo "\e[1;35mChecking tabel: $table\e[0m\n";
    
    if (!Schema::hasTable($table)) {
        echo "  \e[1;31m✗ Tabel $table tidak ditemukan di database!\e[0m\n\n";
        $hasWarning = true;
        continue;
    }
    
    $rows = DB::table($table)->get();
    $count = $rows->count();
    echo "  Total Record: \e[1;33m$count\e[0m\n";
    
    if ($count === 0) {
        echo "  \e[1;31m⚠ WARNING: Tabel kosong (0 data)\e[0m\n\n";
        $hasWarning = true;
        continue;
    }
    
    // Ambil kolom di tabel
    $columns = Schema::getColumnListing($table);
    
    foreach ($columns as $column) {
        $nullCount = 0;
        $emptyCount = 0;
        
        foreach ($rows as $row) {
            $val = $row->$column;
            if ($val === null) {
                $nullCount++;
            } elseif ($val === '') {
                $emptyCount++;
            }
        }
        
        $isNullableOk = in_array($column, $meta['nullable']);
        
        if ($nullCount > 0 || $emptyCount > 0) {
            if ($isNullableOk) {
                // Null yang wajar (misal read_at jika belum dibaca, atau offered_price jika apply biasa)
                echo sprintf("  - %-20s : %d null, %d empty (Wajar/Nullable)\n", $column, $nullCount, $emptyCount);
            } else {
                // Warning! Ada field kosong yang tidak didokumentasikan/seharusnya terisi
                echo sprintf("  - \e[1;31m%-20s : %d null, %d empty (⚠ WARNING: Seharusnya terisi!)\e[0m\n", $column, $nullCount, $emptyCount);
                $hasWarning = true;
            }
        } else {
            echo sprintf("  - %-20s : OK (Terisi penuh)\n", $column);
        }
    }
    echo "\n";
}

echo "========================================\n";
if ($hasWarning) {
    echo "\e[1;31mPemeriksaan Selesai dengan beberapa catatan (lihat WARNING di atas).\e[0m\n";
} else {
    echo "\e[1;32m✓ SEMUA DATA SEEDER TELAH SESUAI DAN TERISI DENGAN LENGKAP!\e[0m\n";
}
echo "========================================\n\n";
