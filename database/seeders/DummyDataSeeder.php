<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
// ============================================================
// CARITALENT DUMMY DATA SEEDER
// Semua data berbasis konteks Bandung
// ============================================================

DB::statement("SET session_replication_role = 'replica';") ;

// ============================================================
// GENRES
// ============================================================
DB::table('genres')->truncate();
DB::table('genres')->insert([
    ['id' => 1,  'name' => 'Pop Punk',          'created_at' => now(), 'updated_at' => now()],
    ['id' => 2,  'name' => 'Heavy Metal',       'created_at' => now(), 'updated_at' => now()],
    ['id' => 3,  'name' => 'DJ',                'created_at' => now(), 'updated_at' => now()],
    ['id' => 4,  'name' => 'Solo Singer',       'created_at' => now(), 'updated_at' => now()],
    ['id' => 5,  'name' => 'Hardcore',          'created_at' => now(), 'updated_at' => now()],
    ['id' => 6,  'name' => 'Jazz',              'created_at' => now(), 'updated_at' => now()],
    ['id' => 7,  'name' => 'Seniman Visual',    'created_at' => now(), 'updated_at' => now()],
    ['id' => 8,  'name' => 'Street Performer',  'created_at' => now(), 'updated_at' => now()],
    ['id' => 9,  'name' => 'Alternative Rock',  'created_at' => now(), 'updated_at' => now()],
    ['id' => 10, 'name' => 'Indie Pop',         'created_at' => now(), 'updated_at' => now()],
    ['id' => 11, 'name' => 'R&B',               'created_at' => now(), 'updated_at' => now()],
    ['id' => 12, 'name' => 'Acoustic',          'created_at' => now(), 'updated_at' => now()],
]);

// ============================================================
// USERS
// password semua: password123
// ============================================================
DB::table('users')->truncate();
DB::table('users')->insert([

    // ---- ADMIN ----
    [
        'id'         => 1,
        'name'       => 'Aprilianza Muhammad Yusup',
        'email'      => 'aprilianza@caritalent.id',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560001',
        'role'       => 'admin',
        'created_at' => '2026-01-01 08:00:00',
        'updated_at' => '2026-01-01 08:00:00',
    ],

    // ---- EO Users ----
    [
        'id'         => 2,
        'name'       => 'Athila Ramdani Saputra',
        'email'      => 'athila@kafebraga.id',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560002',
        'role'       => 'eo',
        'created_at' => '2026-01-05 09:00:00',
        'updated_at' => '2026-01-05 09:00:00',
    ],
    [
        'id'         => 3,
        'name'       => 'Bill Stephen Sembiring',
        'email'      => 'bill@pasarbandoeng.id',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560003',
        'role'       => 'eo',
        'created_at' => '2026-01-07 10:00:00',
        'updated_at' => '2026-01-07 10:00:00',
    ],
    [
        'id'         => 8,
        'name'       => 'Jeany Ferliza Nayla',
        'email'      => 'jeany@bragapermai.id',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560008',
        'role'       => 'eo',
        'created_at' => '2026-01-08 10:30:00',
        'updated_at' => '2026-01-08 10:30:00',
    ],

    // ---- Talent Users ----
    [
        'id'         => 4,
        'name'       => 'Muhammad Irgiansyah',
        'email'      => 'irgi@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560004',
        'role'       => 'talent',
        'created_at' => '2026-01-10 11:00:00',
        'updated_at' => '2026-01-10 11:00:00',
    ],
    [
        'id'         => 5,
        'name'       => 'Arfian Ghifari Mahya',
        'email'      => 'arfian@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560005',
        'role'       => 'talent',
        'created_at' => '2026-01-12 11:30:00',
        'updated_at' => '2026-01-12 11:30:00',
    ],
    // Talent tambahan
    [
        'id'         => 6,
        'name'       => 'Rizky Maulana',
        'email'      => 'rizky.maulana@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560006',
        'role'       => 'talent',
        'created_at' => '2026-01-15 12:00:00',
        'updated_at' => '2026-01-15 12:00:00',
    ],
    [
        'id'         => 7,
        'name'       => 'Siti Nurhaliza Dewi',
        'email'      => 'siti.ndewi@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560007',
        'role'       => 'talent',
        'created_at' => '2026-01-18 13:00:00',
        'updated_at' => '2026-01-18 13:00:00',
    ],
    [
        'id'         => 9,
        'name'       => 'Dendi Prasetyo',
        'email'      => 'dendi.pras@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560009',
        'role'       => 'talent',
        'created_at' => '2026-01-20 09:00:00',
        'updated_at' => '2026-01-20 09:00:00',
    ],
    [
        'id'         => 10,
        'name'       => 'Fauzan Akbar Nugraha',
        'email'      => 'fauzan.akbar@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560010',
        'role'       => 'talent',
        'created_at' => '2026-01-22 10:00:00',
        'updated_at' => '2026-01-22 10:00:00',
    ],
    [
        'id'         => 11,
        'name'       => 'Hendra Wijaya',
        'email'      => 'hendra.wijaya@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560011',
        'role'       => 'eo',
        'created_at' => '2026-01-25 11:00:00',
        'updated_at' => '2026-01-25 11:00:00',
    ],
    [
        'id'         => 12,
        'name'       => 'Nandita Kusuma Wardhani',
        'email'      => 'nandita.kw@gmail.com',
        'password'   => Hash::make('password123'),
        'phone'      => '081234560012',
        'role'       => 'talent',
        'created_at' => '2026-01-28 09:30:00',
        'updated_at' => '2026-01-28 09:30:00',
    ],
]);

// ============================================================
// TALENT PROFILES
// ============================================================
DB::table('talents')->truncate();
DB::table('talents')->insert([

    // Talent 1: Irgiansyah - Band Pop Punk (VERIFIED, banyak review)
    [
        'id'             => 1,
        'user_id'        => 4,
        'stage_name'     => 'The Rotten Bandung',
        'price_min'      => 500000,
        'price_max'      => 2000000,
        'city'           => 'Bandung',
        'bio'            => 'Band pop punk asal Dago Bandung, aktif sejak 2019. Sering manggung di kafe dan venue indie Bandung. Repertoar hits: Peach (The Jansen), Risk It All (Bruno Mars), Kamu Cuma Mau Enaknya Aja (Juicy Luicy), dan Bintang di Surga (Peterpan).',
        'portfolio_link' => 'https://youtube.com/@therottenbandung',
        'verified'       => true,
        'average_rating' => 4.80,
        'total_reviews'  => 5,
        'created_at'     => '2026-01-10 11:30:00',
        'updated_at'     => '2026-03-20 10:00:00',
    ],

    // Talent 2: Arfian - DJ (VERIFIED)
    [
        'id'             => 2,
        'user_id'        => 5,
        'stage_name'     => 'DJ Arfz Bdg',
        'price_min'      => 800000,
        'price_max'      => 3500000,
        'city'           => 'Bandung',
        'bio'            => 'DJ asal Bandung Selatan dengan pengalaman 4 tahun. Spesialis EDM, Hiphop, dan Pop remix. Pernah mengisi di Braga Festival, Dago Culinary Night, dan Summarecon Mal Bandung.',
        'portfolio_link' => 'https://soundcloud.com/djarzfbdg',
        'verified'       => true,
        'average_rating' => 4.50,
        'total_reviews'  => 2,
        'created_at'     => '2026-01-12 12:00:00',
        'updated_at'     => '2026-03-10 09:00:00',
    ],

    // Talent 3: Rizky - Solo Singer (VERIFIED)
    [
        'id'             => 3,
        'user_id'        => 6,
        'stage_name'     => 'Rizky Maulana Acoustic',
        'price_min'      => 300000,
        'price_max'      => 1200000,
        'city'           => 'Bandung',
        'bio'            => 'Penyanyi solo dengan gitar akustik khas Bandung. Membawakan lagu-lagu Dewa 19, Sheila on 7, Juicy Luicy, dan hits OPM. Cocok untuk suasana kafe intimate dan dinner.',
        'portfolio_link' => 'https://youtube.com/@rizkymaulanaacoustic',
        'verified'       => true,
        'average_rating' => 4.67,
        'total_reviews'  => 3,
        'created_at'     => '2026-01-15 12:30:00',
        'updated_at'     => '2026-03-15 11:00:00',
    ],

    // Talent 4: Siti - Jazz Singer (VERIFIED)
    [
        'id'             => 4,
        'user_id'        => 7,
        'stage_name'     => 'Siti ND Jazz',
        'price_min'      => 600000,
        'price_max'      => 2500000,
        'city'           => 'Bandung',
        'bio'            => 'Vokalis jazz dan R&B lulusan ISI Bandung. Membawakan jazz standar, bossa nova, hingga jazz-pop modern. Pernah tampil di Braga City Walk, 23 Paskal, dan berbagai pesta pernikahan mewah Bandung.',
        'portfolio_link' => 'https://instagram.com/sitindjazz',
        'verified'       => true,
        'average_rating' => 4.90,
        'total_reviews'  => 4,
        'created_at'     => '2026-01-18 13:30:00',
        'updated_at'     => '2026-03-18 14:00:00',
    ],

    // Talent 5: Dendi - Band Heavy Metal (UNVERIFIED - baru daftar, belum ada review)
    [
        'id'             => 5,
        'user_id'        => 9,
        'stage_name'     => 'Altar Sunda',
        'price_min'      => 700000,
        'price_max'      => 2800000,
        'city'           => 'Bandung',
        'bio'            => 'Band metal asal Cimahi-Bandung yang membawakan heavy metal dan thrash. Terinspirasi dari Burgerkill, Seringai, dan Metallica. Energi tinggi di atas panggung.',
        'portfolio_link' => 'https://youtube.com/@altarsunda',
        'verified'       => false,
        'average_rating' => 0.00,
        'total_reviews'  => 0,
        'created_at'     => '2026-01-20 09:30:00',
        'updated_at'     => '2026-01-20 09:30:00',
    ],

    // Talent 6: Fauzan - Band Indie Pop (UNVERIFIED - baru daftar)
    [
        'id'             => 6,
        'user_id'        => 10,
        'stage_name'     => 'Langit Sore',
        'price_min'      => 400000,
        'price_max'      => 1500000,
        'city'           => 'Bandung',
        'bio'            => 'Duo indie pop Bandung dengan nuansa dreamy dan lo-fi. Membawakan lagu sendiri dan cover hits The Jansen, Hindia, serta Feast. Cocok untuk suasana sore santai.',
        'portfolio_link' => 'https://spotify.com/artist/langitsore',
        'verified'       => false,
        'average_rating' => 0.00,
        'total_reviews'  => 0,
        'created_at'     => '2026-01-22 10:30:00',
        'updated_at'     => '2026-01-22 10:30:00',
    ],

    // Talent 7: Nandita - Seniman Visual / Street Performer (VERIFIED, 1 review)
    [
        'id'             => 7,
        'user_id'        => 12,
        'stage_name'     => 'Nandita Visual Art',
        'price_min'      => 250000,
        'price_max'      => 1000000,
        'city'           => 'Bandung',
        'bio'            => 'Seniman visual dan live painter asal Bandung. Spesialis live mural, lukis kanvas di depan penonton, dan performance art. Telah tampil di berbagai festival seni Bandung termasuk Bandung Art Month.',
        'portfolio_link' => 'https://instagram.com/nanditavisualart',
        'verified'       => true,
        'average_rating' => 5.00,
        'total_reviews'  => 1,
        'created_at'     => '2026-01-28 10:00:00',
        'updated_at'     => '2026-03-25 15:00:00',
    ],
]);

// ============================================================
// TALENT - GENRE PIVOT
// ============================================================
DB::table('genre_talent')->truncate();
DB::table('genre_talent')->insert([
    // The Rotten Bandung: Pop Punk, Alternative Rock, Hardcore
    ['talent_id' => 1, 'genre_id' => 1],
    ['talent_id' => 1, 'genre_id' => 9],
    ['talent_id' => 1, 'genre_id' => 5],
    // DJ Arfz Bdg: DJ
    ['talent_id' => 2, 'genre_id' => 3],
    // Rizky Maulana: Solo Singer, Acoustic, Indie Pop
    ['talent_id' => 3, 'genre_id' => 4],
    ['talent_id' => 3, 'genre_id' => 12],
    ['talent_id' => 3, 'genre_id' => 10],
    // Siti ND Jazz: Jazz, R&B, Solo Singer
    ['talent_id' => 4, 'genre_id' => 6],
    ['talent_id' => 4, 'genre_id' => 11],
    ['talent_id' => 4, 'genre_id' => 4],
    // Altar Sunda: Heavy Metal, Hardcore
    ['talent_id' => 5, 'genre_id' => 2],
    ['talent_id' => 5, 'genre_id' => 5],
    // Langit Sore: Indie Pop, Acoustic, Alternative Rock
    ['talent_id' => 6, 'genre_id' => 10],
    ['talent_id' => 6, 'genre_id' => 12],
    ['talent_id' => 6, 'genre_id' => 9],
    // Nandita Visual Art: Seniman Visual, Street Performer
    ['talent_id' => 7, 'genre_id' => 7],
    ['talent_id' => 7, 'genre_id' => 8],
]);

// ============================================================
// PORTFOLIO MEDIA
// ============================================================
DB::table('media')->truncate();
DB::table('media')->insert([
    ['id' => 1,  'talent_id' => 1, 'media_url' => 'https://storage.caritalent.id/media/talent1_live_braga.jpg',       'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 2,  'talent_id' => 1, 'media_url' => 'https://storage.caritalent.id/media/talent1_cover_peach.mp4',       'type' => 'video', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 3,  'talent_id' => 1, 'media_url' => 'https://storage.caritalent.id/media/talent1_promo.jpg',             'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 4,  'talent_id' => 2, 'media_url' => 'https://storage.caritalent.id/media/talent2_djset_dago.mp4',        'type' => 'video', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 5,  'talent_id' => 2, 'media_url' => 'https://storage.caritalent.id/media/talent2_booth_setup.jpg',       'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 6,  'talent_id' => 3, 'media_url' => 'https://storage.caritalent.id/media/talent3_acoustic_kafe.jpg',     'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 7,  'talent_id' => 3, 'media_url' => 'https://storage.caritalent.id/media/talent3_cover_kamu.mp3',        'type' => 'audio', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 8,  'talent_id' => 4, 'media_url' => 'https://storage.caritalent.id/media/talent4_jazz_braga.jpg',        'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 9,  'talent_id' => 4, 'media_url' => 'https://storage.caritalent.id/media/talent4_performance_clip.mp4',  'type' => 'video', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 10, 'talent_id' => 5, 'media_url' => 'https://storage.caritalent.id/media/talent5_metal_rehearsal.jpg',   'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 11, 'talent_id' => 6, 'media_url' => 'https://storage.caritalent.id/media/talent6_indiepop_cover.jpg',    'type' => 'image', 'created_at' => now(), 'updated_at' => now()],
    ['id' => 12, 'talent_id' => 7, 'media_url' => 'https://storage.caritalent.id/media/talent7_livepaint_festival.jpg','type' => 'image', 'created_at' => now(), 'updated_at' => now()],
]);

// ============================================================
// EVENTS
// EO 2 = Athila (Kafe Braga Permai), EO 3 = Bill (Pasar Bandoeng), EO 8 = Jeany (Braga Art Space), EO 11 = Hendra (Kopi Selasar)
// ============================================================
DB::table('events')->truncate();
DB::table('events')->insert([

    // --- Event 1: OPEN — cocok untuk apply talent ---
    [
        'id'           => 1,
        'organizer_id'      => 2,
        'title'        => 'Braga Punk Night Vol.5',
        'description'  => 'Malam punk rock bulanan di Kafe Braga Permai. Kami mencari band energetik yang siap mengguncang panggung. Setlist wajib ada cover The Jansen dan Neck Deep.',
        'budget'       => 2000000,
        'event_date'   => '2026-05-10',
        'venue_name'   => 'Kafe Braga Permai',
        'latitude'     => -6.9109,
        'longitude'    => 107.6089,
        'city'         => 'Bandung',
        'status'       => 'open',
        'created_at'   => '2026-04-01 10:00:00',
        'updated_at'   => '2026-04-01 10:00:00',
    ],

    // --- Event 2: OPEN — mencari DJ ---
    [
        'id'           => 2,
        'organizer_id'      => 3,
        'title'        => 'Pasar Bandoeng Weekend Vibes',
        'description'  => 'Event weekend di Pasar Bandoeng Kota Baru Parahyangan. Butuh DJ untuk mengisi suasana dari sore hingga malam dengan lagu-lagu pop, hiphop, dan EDM hype.',
        'budget'       => 3000000,
        'event_date'   => '2026-05-17',
        'venue_name'   => 'Pasar Bandoeng - Kota Baru Parahyangan',
        'latitude'     => -6.8380,
        'longitude'    => 107.5361,
        'city'         => 'Bandung',
        'status'       => 'open',
        'created_at'   => '2026-04-02 11:00:00',
        'updated_at'   => '2026-04-02 11:00:00',
    ],

    // --- Event 3: OPEN — mencari Jazz Singer ---
    [
        'id'           => 3,
        'organizer_id'      => 8,
        'title'        => 'Braga Jazz Evening',
        'description'  => 'Evening jazz intimate di Braga Art Space. Mencari penyanyi jazz berbakat untuk menemani tamu menikmati dinner dan wine. Repertoar jazz standar dan bossa nova diutamakan.',
        'budget'       => 2500000,
        'event_date'   => '2026-05-24',
        'venue_name'   => 'Braga Art Space',
        'latitude'     => -6.9116,
        'longitude'    => 107.6095,
        'city'         => 'Bandung',
        'status'       => 'open',
        'created_at'   => '2026-04-03 09:00:00',
        'updated_at'   => '2026-04-03 09:00:00',
    ],

    // --- Event 4: OPEN — mencari Acoustic / Solo Singer ---
    [
        'id'           => 4,
        'organizer_id'      => 11,
        'title'        => 'Kopi Selasar Acoustic Sunday',
        'description'  => 'Sesi acoustic rutin tiap minggu di Kopi Selasar Sunaryo. Suasana santai, butuh musisi atau penyanyi dengan gitar akustik. Lagu-lagu hits seperti Dewa 19, Juicy Luicy, dan Bruno Mars sangat cocok.',
        'budget'       => 800000,
        'event_date'   => '2026-05-03',
        'venue_name'   => 'Kopi Selasar Sunaryo Art Space',
        'latitude'     => -6.8733,
        'longitude'    => 107.6218,
        'city'         => 'Bandung',
        'status'       => 'open',
        'created_at'   => '2026-04-04 08:00:00',
        'updated_at'   => '2026-04-04 08:00:00',
    ],

    // --- Event 5: DRAFT — belum dipublish EO ---
    [
        'id'           => 5,
        'organizer_id'      => 2,
        'title'        => 'Braga Indie Fest 2026',
        'description'  => 'Festival indie tahunan Braga. Masih dalam tahap perencanaan, butuh beberapa band indie pop dan alternative untuk lineup.',
        'budget'       => 5000000,
        'event_date'   => '2026-06-20',
        'venue_name'   => 'Lapangan Kafe Braga Permai',
        'latitude'     => -6.9109,
        'longitude'    => 107.6089,
        'city'         => 'Bandung',
        'status'       => 'draft',
        'created_at'   => '2026-04-05 14:00:00',
        'updated_at'   => '2026-04-05 14:00:00',
    ],

    // --- Event 6: CLOSED — sudah ditutup penerimaannya ---
    [
        'id'           => 6,
        'organizer_id'      => 3,
        'title'        => 'Pasar Bandoeng Metal Malam',
        'description'  => 'Malam heavy metal khusus untuk komunitas underground Bandung. Sudah menemukan band yang cocok.',
        'budget'       => 2500000,
        'event_date'   => '2026-05-01',
        'venue_name'   => 'Pasar Bandoeng - Kota Baru Parahyangan',
        'latitude'     => -6.8380,
        'longitude'    => 107.5361,
        'city'         => 'Bandung',
        'status'       => 'closed',
        'created_at'   => '2026-03-15 10:00:00',
        'updated_at'   => '2026-03-28 16:00:00',
    ],

    // --- Event 7: COMPLETED — sudah selesai, ada review ---
    [
        'id'           => 7,
        'organizer_id'      => 2,
        'title'        => 'Braga Punk Night Vol.4',
        'description'  => 'Edisi keempat punk night Braga. Sudah berlangsung.',
        'budget'       => 1800000,
        'event_date'   => '2026-03-15',
        'venue_name'   => 'Kafe Braga Permai',
        'latitude'     => -6.9109,
        'longitude'    => 107.6089,
        'city'         => 'Bandung',
        'status'       => 'completed',
        'created_at'   => '2026-02-20 10:00:00',
        'updated_at'   => '2026-03-16 09:00:00',
    ],

    // --- Event 8: COMPLETED — sudah selesai ---
    [
        'id'           => 8,
        'organizer_id'      => 8,
        'title'        => 'Braga Jazz Evening Maret',
        'description'  => 'Sesi jazz maret di Braga Art Space. Sudah selesai.',
        'budget'       => 2500000,
        'event_date'   => '2026-03-22',
        'venue_name'   => 'Braga Art Space',
        'latitude'     => -6.9116,
        'longitude'    => 107.6095,
        'city'         => 'Bandung',
        'status'       => 'completed',
        'created_at'   => '2026-02-25 09:00:00',
        'updated_at'   => '2026-03-23 10:00:00',
    ],

    // --- Event 9: COMPLETED — sudah selesai ---
    [
        'id'           => 9,
        'organizer_id'      => 11,
        'title'        => 'Kopi Selasar Acoustic Maret',
        'description'  => 'Sesi acoustic bulan maret di Selasar. Sudah selesai.',
        'budget'       => 700000,
        'event_date'   => '2026-03-09',
        'venue_name'   => 'Kopi Selasar Sunaryo Art Space',
        'latitude'     => -6.8733,
        'longitude'    => 107.6218,
        'city'         => 'Bandung',
        'status'       => 'completed',
        'created_at'   => '2026-02-10 08:00:00',
        'updated_at'   => '2026-03-10 08:00:00',
    ],

    // --- Event 10: COMPLETED ---
    [
        'id'           => 10,
        'organizer_id'      => 3,
        'title'        => 'Pasar Bandoeng DJ Night Februari',
        'description'  => 'DJ Night Februari di Pasar Bandoeng. Sudah selesai.',
        'budget'       => 3000000,
        'event_date'   => '2026-02-22',
        'venue_name'   => 'Pasar Bandoeng - Kota Baru Parahyangan',
        'latitude'     => -6.8380,
        'longitude'    => 107.5361,
        'city'         => 'Bandung',
        'status'       => 'completed',
        'created_at'   => '2026-01-25 10:00:00',
        'updated_at'   => '2026-02-23 09:00:00',
    ],

    // --- Event 11: CANCELLED ---
    [
        'id'           => 11,
        'organizer_id'      => 8,
        'title'        => 'Braga Art Night - Dibatalkan',
        'description'  => 'Event seni visual malam yang terpaksa dibatalkan karena perubahan jadwal venue.',
        'budget'       => 1500000,
        'event_date'   => '2026-04-05',
        'venue_name'   => 'Braga Art Space',
        'latitude'     => -6.9116,
        'longitude'    => 107.6095,
        'city'         => 'Bandung',
        'status'       => 'cancelled',
        'created_at'   => '2026-03-01 09:00:00',
        'updated_at'   => '2026-03-20 11:00:00',
    ],
]);

// ============================================================
// EVENT - GENRE PIVOT
// ============================================================
DB::table('event_genre')->truncate();
DB::table('event_genre')->insert([
    // Event 1 - Punk Night: Pop Punk, Hardcore, Alternative Rock
    ['event_id' => 1, 'genre_id' => 1],
    ['event_id' => 1, 'genre_id' => 5],
    ['event_id' => 1, 'genre_id' => 9],
    // Event 2 - Weekend Vibes: DJ
    ['event_id' => 2, 'genre_id' => 3],
    // Event 3 - Jazz Evening: Jazz, R&B
    ['event_id' => 3, 'genre_id' => 6],
    ['event_id' => 3, 'genre_id' => 11],
    // Event 4 - Acoustic Sunday: Solo Singer, Acoustic, Indie Pop
    ['event_id' => 4, 'genre_id' => 4],
    ['event_id' => 4, 'genre_id' => 12],
    ['event_id' => 4, 'genre_id' => 10],
    // Event 5 - Indie Fest: Indie Pop, Alternative Rock
    ['event_id' => 5, 'genre_id' => 10],
    ['event_id' => 5, 'genre_id' => 9],
    // Event 6 - Metal Night: Heavy Metal, Hardcore
    ['event_id' => 6, 'genre_id' => 2],
    ['event_id' => 6, 'genre_id' => 5],
    // Event 7 - Punk Vol.4: Pop Punk, Alternative Rock
    ['event_id' => 7, 'genre_id' => 1],
    ['event_id' => 7, 'genre_id' => 9],
    // Event 8 - Jazz Maret: Jazz, R&B
    ['event_id' => 8, 'genre_id' => 6],
    ['event_id' => 8, 'genre_id' => 11],
    // Event 9 - Acoustic Maret: Acoustic, Solo Singer
    ['event_id' => 9, 'genre_id' => 12],
    ['event_id' => 9, 'genre_id' => 4],
    // Event 10 - DJ Night: DJ
    ['event_id' => 10, 'genre_id' => 3],
    // Event 11 - Art Night: Seniman Visual, Street Performer
    ['event_id' => 11, 'genre_id' => 7],
    ['event_id' => 11, 'genre_id' => 8],
]);

// ============================================================
// APPLICATIONS
// Mencakup berbagai kondisi: pending, accepted, rejected, cancelled
// source: apply / invitation
// ============================================================
DB::table('applications')->truncate();
DB::table('applications')->insert([

    // == KONDISI 1: Apply biasa, masih PENDING (Event 1 - Punk Night) ==
    // The Rotten Bandung apply ke Braga Punk Night Vol.5
    [
        'id'             => 1,
        'event_id'       => 1,
        'talent_id'      => 4,
        'source'         => 'apply',
        'message'        => 'Halo. Kami The Rotten Bandung, band pop punk dengan pengalaman 5 tahun. Siap tampil maksimal di Braga Punk Night. Setlist kami ada cover Peach (The Jansen), Risk It All (Bruno Mars versi punk), dan beberapa lagu original.',
        'proposed_price' => 1500000,
        'status'         => 'pending',
        'offered_price'   => null,
        'created_at'     => '2026-04-02 13:00:00',
        'updated_at'     => '2026-04-02 13:00:00',
    ],
    // Langit Sore juga apply ke Braga Punk Night Vol.5 (kurang cocok genre tapi tetap coba)
    [
        'id'             => 2,
        'event_id'       => 1,
        'talent_id'      => 10,
        'source'         => 'apply',
        'message'        => 'Halo, kami Langit Sore, duo indie dengan nuansa alternative. Kami bisa membawakan set yang energetik untuk Punk Night, dengan sentuhan alternative yang fresh.',
        'proposed_price' => 1200000,
        'status'         => 'pending',
        'offered_price'   => null,
        'created_at'     => '2026-04-03 10:00:00',
        'updated_at'     => '2026-04-03 10:00:00',
    ],

    // == KONDISI 2: Apply REJECTED (Event 2 - DJ Night, talent yang apply bukan DJ) ==
    // The Rotten Bandung apply ke DJ night, di-reject karena genre tidak cocok
    [
        'id'             => 3,
        'event_id'       => 2,
        'talent_id'      => 4,
        'source'         => 'apply',
        'message'        => 'Kami bisa membawakan suasana seru dengan band, meski ini event DJ night.',
        'proposed_price' => 1000000,
        'status'         => 'rejected',
        'offered_price'   => null,
        'created_at'     => '2026-04-02 14:00:00',
        'updated_at'     => '2026-04-03 09:00:00',
    ],
    // DJ Arfz apply ke Event 2 - ACCEPTED (akan membuat booking)
    [
        'id'             => 4,
        'event_id'       => 2,
        'talent_id'      => 5,
        'source'         => 'apply',
        'message'        => 'Saya DJ Arfz dari Bandung Selatan, spesialis EDM dan Pop remix. Siap mengisi Weekend Vibes Pasar Bandoeng dari jam 16.00 sampai selesai.',
        'proposed_price' => 2500000,
        'status'         => 'accepted',
        'offered_price'   => 2500000,
        'created_at'     => '2026-04-02 15:00:00',
        'updated_at'     => '2026-04-03 10:00:00',
    ],

    // == KONDISI 3: Apply ke Event Jazz, lanjut ke booking ==
    // Siti ND Jazz apply ke Braga Jazz Evening - ACCEPTED
    [
        'id'             => 5,
        'event_id'       => 3,
        'talent_id'      => 7,
        'source'         => 'apply',
        'message'        => 'Selamat siang. Saya Siti ND, vokalis jazz lulusan ISI Bandung. Sangat tertarik untuk tampil di Braga Jazz Evening. Repertoar saya mencakup jazz standar, bossa nova, dan jazz-pop. Bisa menyesuaikan suasana dinner Anda.',
        'proposed_price' => 2000000,
        'status'         => 'accepted',
        'offered_price'   => 2000000,
        'created_at'     => '2026-04-03 11:00:00',
        'updated_at'     => '2026-04-04 09:00:00',
    ],
    // Rizky juga apply ke Jazz Evening - REJECTED (sudah ada yang accepted)
    [
        'id'             => 6,
        'event_id'       => 3,
        'talent_id'      => 6,
        'source'         => 'apply',
        'message'        => 'Saya Rizky, penyanyi acoustic. Bisa juga membawakan jazz-pop ringan untuk dinner.',
        'proposed_price' => 900000,
        'status'         => 'rejected',
        'offered_price'   => null,
        'created_at'     => '2026-04-03 12:00:00',
        'updated_at'     => '2026-04-04 09:30:00',
    ],

    // == KONDISI 4: Invitation dari EO ke Talent (Event 4 - Acoustic Sunday) ==
    // EO Hendra invites Rizky Maulana lewat invitation
    [
        'id'             => 7,
        'event_id'       => 4,
        'talent_id'      => 6,
        'source'         => 'invitation',
        'message'        => 'Kami mengundang Anda untuk tampil di Kopi Selasar Acoustic Sunday. Kami sudah menonton performa Anda dan yakin cocok dengan suasana kami.',
        'proposed_price' => 700000,
        'status'         => 'accepted',
        'offered_price'   => 700000,
        'created_at'     => '2026-04-04 10:00:00',
        'updated_at'     => '2026-04-04 14:00:00',
    ],

    // == KONDISI 5: Invitation pending, belum direspons talent ==
    // EO Athila invites Langit Sore untuk Event 5 Indie Fest (masih draft, tapi invite duluan)
    [
        'id'             => 8,
        'event_id'       => 5,
        'talent_id'      => 10,
        'source'         => 'invitation',
        'message'        => 'Halo Langit Sore. Kami sedang mempersiapkan Braga Indie Fest 2026 dan sangat tertarik mengundang kalian sebagai salah satu lineup. Harga yang kami tawarkan 1.2jt.',
        'proposed_price' => 1200000,
        'status'         => 'pending',
        'offered_price'   => null,
        'created_at'     => '2026-04-06 09:00:00',
        'updated_at'     => '2026-04-06 09:00:00',
    ],

    // == KONDISI 6: Invitation REJECTED oleh talent ==
    // EO Bill invites Altar Sunda ke Event 6 Metal Night - talent reject
    [
        'id'             => 9,
        'event_id'       => 6,
        'talent_id'      => 9,
        'source'         => 'invitation',
        'message'        => 'Halo Altar Sunda. Kami butuh band metal untuk Pasar Bandoeng Metal Malam. Tertarik?',
        'proposed_price' => 2000000,
        'status'         => 'rejected',
        'offered_price'   => null,
        'created_at'     => '2026-03-16 10:00:00',
        'updated_at'     => '2026-03-17 09:00:00',
    ],

    // == KONDISI 7: Apply yang kemudian di-CANCEL oleh talent (masih pending) ==
    // Altar Sunda apply ke Braga Punk Night Vol.5, lalu cancel
    [
        'id'             => 10,
        'event_id'       => 1,
        'talent_id'      => 9,
        'source'         => 'apply',
        'message'        => 'Kami Altar Sunda, metal dari Bandung, bisa membawakan energi hardcore untuk Punk Night.',
        'proposed_price' => 1800000,
        'status'         => 'rejected',
        'offered_price'   => null,
        'created_at'     => '2026-04-02 16:00:00',
        'updated_at'     => '2026-04-03 08:00:00',
    ],

    // == KONDISI COMPLETED: Applications untuk event yang sudah selesai ==
    // Event 7 (Punk Vol.4) - The Rotten Bandung tampil, booking completed
    [
        'id'             => 11,
        'event_id'       => 7,
        'talent_id'      => 4,
        'source'         => 'apply',
        'message'        => 'The Rotten Bandung siap untuk Punk Night Vol.4.',
        'proposed_price' => 1500000,
        'status'         => 'accepted',
        'offered_price'   => 1500000,
        'created_at'     => '2026-02-21 10:00:00',
        'updated_at'     => '2026-02-22 09:00:00',
    ],
    // Event 8 (Jazz Maret) - Siti ND, booking completed
    [
        'id'             => 12,
        'event_id'       => 8,
        'talent_id'      => 7,
        'source'         => 'invitation',
        'message'        => 'Terima kasih atas undangannya, saya sangat senang bisa tampil di Braga Art Space.',
        'proposed_price' => 2000000,
        'status'         => 'accepted',
        'offered_price'   => 2000000,
        'created_at'     => '2026-02-26 09:00:00',
        'updated_at'     => '2026-02-27 10:00:00',
    ],
    // Event 9 (Acoustic Maret) - Rizky Maulana, booking completed
    [
        'id'             => 13,
        'event_id'       => 9,
        'talent_id'      => 6,
        'source'         => 'apply',
        'message'        => 'Siap tampil di Kopi Selasar.',
        'proposed_price' => 600000,
        'status'         => 'accepted',
        'offered_price'   => 600000,
        'created_at'     => '2026-02-11 09:00:00',
        'updated_at'     => '2026-02-12 10:00:00',
    ],
    // Event 10 (DJ Night Feb) - DJ Arfz, booking completed
    [
        'id'             => 14,
        'event_id'       => 10,
        'talent_id'      => 5,
        'source'         => 'apply',
        'message'        => 'DJ Arfz siap guncang Pasar Bandoeng.',
        'proposed_price' => 2500000,
        'status'         => 'accepted',
        'offered_price'   => 2500000,
        'created_at'     => '2026-01-26 10:00:00',
        'updated_at'     => '2026-01-27 09:00:00',
    ],
    // Event 7 (Punk Vol.4) - Langit Sore juga apply tapi rejected
    [
        'id'             => 15,
        'event_id'       => 7,
        'talent_id'      => 10,
        'source'         => 'apply',
        'message'        => 'Langit Sore ingin ikut Punk Vol.4.',
        'proposed_price' => 1000000,
        'status'         => 'rejected',
        'offered_price'   => null,
        'created_at'     => '2026-02-21 11:00:00',
        'updated_at'     => '2026-02-22 09:30:00',
    ],
    // Event 11 (Art Night - CANCELLED) - Nandita Visual Art sudah apply sebelum cancel
    [
        'id'             => 16,
        'event_id'       => 11,
        'talent_id'      => 12,
        'source'         => 'apply',
        'message'        => 'Nandita Visual Art siap untuk live painting di Braga Art Night.',
        'proposed_price' => 800000,
        'status'         => 'pending',
        'offered_price'   => null,
        'created_at'     => '2026-03-02 10:00:00',
        'updated_at'     => '2026-03-02 10:00:00',
    ],
]);

// ============================================================
// BOOKINGS
// Dibuat otomatis saat application accepted
// ============================================================
DB::table('bookings')->truncate();
DB::table('bookings')->insert([

    // Booking 1: Event 2 (DJ Night) - DJ Arfz - CONFIRMED (belum terlaksana)
    [
        'id'             => 1,
        'application_id' => 4,
        'agreed_price'   => 2500000,
        'status'         => 'confirmed',
        'created_at'     => '2026-04-03 10:05:00',
        'updated_at'     => '2026-04-03 10:05:00',
    ],
    // Booking 2: Event 3 (Jazz Evening) - Siti ND - CONFIRMED (belum terlaksana)
    [
        'id'             => 2,
        'application_id' => 5,
        'agreed_price'   => 2000000,
        'status'         => 'confirmed',
        'created_at'     => '2026-04-04 09:05:00',
        'updated_at'     => '2026-04-04 09:05:00',
    ],
    // Booking 3: Event 4 (Acoustic Sunday) - Rizky - CONFIRMED (belum terlaksana)
    [
        'id'             => 3,
        'application_id' => 7,
        'agreed_price'   => 700000,
        'status'         => 'confirmed',
        'created_at'     => '2026-04-04 14:05:00',
        'updated_at'     => '2026-04-04 14:05:00',
    ],

    // Booking 4: Event 7 (Punk Vol.4) - The Rotten Bandung - COMPLETED + ada review
    [
        'id'             => 4,
        'application_id' => 11,
        'agreed_price'   => 1500000,
        'status'         => 'completed',
        'created_at'     => '2026-02-22 09:05:00',
        'updated_at'     => '2026-03-16 09:00:00',
    ],
    // Booking 5: Event 8 (Jazz Maret) - Siti ND - COMPLETED + ada review
    [
        'id'             => 5,
        'application_id' => 12,
        'agreed_price'   => 2000000,
        'status'         => 'completed',
        'created_at'     => '2026-02-27 10:05:00',
        'updated_at'     => '2026-03-23 10:00:00',
    ],
    // Booking 6: Event 9 (Acoustic Maret) - Rizky - COMPLETED + ada review
    [
        'id'             => 6,
        'application_id' => 13,
        'agreed_price'   => 600000,
        'status'         => 'completed',
        'created_at'     => '2026-02-12 10:05:00',
        'updated_at'     => '2026-03-10 08:00:00',
    ],
    // Booking 7: Event 10 (DJ Night Feb) - DJ Arfz - COMPLETED + ada review
    [
        'id'             => 7,
        'application_id' => 14,
        'agreed_price'   => 2500000,
        'status'         => 'completed',
        'created_at'     => '2026-01-27 09:05:00',
        'updated_at'     => '2026-02-23 09:00:00',
    ],
]);

// ============================================================
// REVIEWS
// Hanya setelah booking completed
// ============================================================
DB::table('reviews')->truncate();
DB::table('reviews')->insert([

    // Review untuk The Rotten Bandung dari Braga Punk Night Vol.4
    [
        'id'         => 1,
        'booking_id' => 4,
        'rating'     => 5,
        'comment'    => 'The Rotten Bandung luar biasa. Energi di panggung sangat tinggi, penonton langsung hype dari lagu pertama. Cover Peach dari The Jansen dibawakan dengan sempurna. Pasti kami undang lagi.',
        'created_at' => '2026-03-16 20:00:00',
        'updated_at' => '2026-03-16 20:00:00',
    ],
    // Review untuk Siti ND dari Jazz Maret
    [
        'id'         => 2,
        'booking_id' => 5,
        'rating'     => 5,
        'comment'    => 'Siti ND sungguh memukau. Suaranya sangat cocok untuk suasana dinner jazz yang kami inginkan. Tamu-tamu sangat terkesan, beberapa bahkan meminta kartu kontaknya langsung. Profesional dan tepat waktu.',
        'created_at' => '2026-03-23 21:00:00',
        'updated_at' => '2026-03-23 21:00:00',
    ],
    // Review untuk Rizky dari Acoustic Maret
    [
        'id'         => 3,
        'booking_id' => 6,
        'rating'     => 4,
        'comment'    => 'Rizky tampil bagus dan bikin suasana Kopi Selasar makin nyaman. Pilihan lagunya pas banget, ada Dewa 19 dan Juicy Luicy. Cukup memuaskan, meski sound system sedikit kurang optimal dari sisinya.',
        'created_at' => '2026-03-10 20:00:00',
        'updated_at' => '2026-03-10 20:00:00',
    ],
    // Review untuk DJ Arfz dari DJ Night Feb
    [
        'id'         => 4,
        'booking_id' => 7,
        'rating'     => 4,
        'comment'    => 'DJ Arfz berhasil bikin Pasar Bandoeng malam itu hidup banget. Set EDM-nya bagus dan crowd terus antusias. Satu catatan kecil: transisi antar lagu di awal agak terburu-buru, tapi keseluruhan memuaskan.',
        'created_at' => '2026-02-23 22:00:00',
        'updated_at' => '2026-02-23 22:00:00',
    ],
]);

// ============================================================
// NOTIFICATIONS
// ============================================================
DB::table('notifications')->truncate();
DB::table('notifications')->insert([

    // Notif untuk Talent 1 (Irgi/The Rotten Bandung)
    ['id' => 1,  'user_id' => 4, 'title' => 'Lamaran Diterima.',            'body' => 'Selamat. Lamaran Anda ke Braga Punk Night Vol.4 telah diterima oleh Kafe Braga Permai.',                             'type' => 'application', 'reference_id' => 11, 'is_read' => true,  'created_at' => '2026-02-22 09:05:00', 'updated_at' => '2026-02-22 09:30:00'],
    ['id' => 2,  'user_id' => 4, 'title' => 'Review Baru Masuk',             'body' => 'Kafe Braga Permai memberikan review bintang 5 untuk penampilan Anda di Braga Punk Night Vol.4.',                    'type' => 'review',       'reference_id' => 1,  'is_read' => true,  'created_at' => '2026-03-16 20:00:00', 'updated_at' => '2026-03-17 08:00:00'],
    ['id' => 3,  'user_id' => 4, 'title' => 'Lamaran Ditolak',               'body' => 'Mohon maaf, lamaran Anda ke Pasar Bandoeng DJ Night ditolak karena genre tidak sesuai.',                             'type' => 'application', 'reference_id' => 3,  'is_read' => false, 'created_at' => '2026-04-03 09:00:00', 'updated_at' => '2026-04-03 09:00:00'],

    // Notif untuk Talent 2 (Arfian/DJ Arfz)
    ['id' => 4,  'user_id' => 5, 'title' => 'Lamaran Diterima.',            'body' => 'Selamat. Lamaran Anda ke Pasar Bandoeng Weekend Vibes telah diterima.',                                                'type' => 'application', 'reference_id' => 4,  'is_read' => true,  'created_at' => '2026-04-03 10:05:00', 'updated_at' => '2026-04-03 10:30:00'],
    ['id' => 5,  'user_id' => 5, 'title' => 'Booking Dikonfirmasi',          'body' => 'Booking Anda untuk Pasar Bandoeng Weekend Vibes tanggal 17 Mei 2026 sudah dikonfirmasi.',                              'type' => 'booking',     'reference_id' => 1,  'is_read' => false, 'created_at' => '2026-04-03 10:05:00', 'updated_at' => '2026-04-03 10:05:00'],
    ['id' => 6,  'user_id' => 5, 'title' => 'Review Baru Masuk',             'body' => 'Pasar Bandoeng memberikan review bintang 4 untuk penampilan DJ Anda di DJ Night Februari.',                           'type' => 'review',       'reference_id' => 4,  'is_read' => true,  'created_at' => '2026-02-23 22:00:00', 'updated_at' => '2026-02-24 08:00:00'],

    // Notif untuk Talent 3 (Rizky)
    ['id' => 7,  'user_id' => 6, 'title' => 'Undangan Manggung Baru.',      'body' => 'Kopi Selasar Sunaryo mengundang Anda untuk tampil di Acoustic Sunday 3 Mei 2026. Cek detailnya.',                      'type' => 'invitation',  'reference_id' => 7,  'is_read' => true,  'created_at' => '2026-04-04 10:00:00', 'updated_at' => '2026-04-04 10:15:00'],
    ['id' => 8,  'user_id' => 6, 'title' => 'Undangan Diterima - Booking.', 'body' => 'Anda menerima undangan Kopi Selasar. Booking Anda untuk 3 Mei 2026 telah dikonfirmasi.',                               'type' => 'booking',     'reference_id' => 3,  'is_read' => true,  'created_at' => '2026-04-04 14:05:00', 'updated_at' => '2026-04-04 14:20:00'],
    ['id' => 9,  'user_id' => 6, 'title' => 'Lamaran Ditolak',               'body' => 'Lamaran Anda ke Braga Jazz Evening ditolak. Jangan menyerah, terus cari event lain.',                                  'type' => 'application', 'reference_id' => 6,  'is_read' => false, 'created_at' => '2026-04-04 09:30:00', 'updated_at' => '2026-04-04 09:30:00'],

    // Notif untuk Talent 4 (Siti ND)
    ['id' => 10, 'user_id' => 7, 'title' => 'Lamaran Diterima.',            'body' => 'Selamat. Lamaran Anda ke Braga Jazz Evening 24 Mei 2026 diterima oleh Braga Art Space.',                               'type' => 'application', 'reference_id' => 5,  'is_read' => true,  'created_at' => '2026-04-04 09:05:00', 'updated_at' => '2026-04-04 09:20:00'],
    ['id' => 11, 'user_id' => 7, 'title' => 'Review Baru - Bintang 5.',     'body' => 'Braga Art Space memberikan review bintang 5 untuk penampilan Anda di Jazz Maret. Luar biasa.',                         'type' => 'review',       'reference_id' => 2,  'is_read' => false, 'created_at' => '2026-03-23 21:00:00', 'updated_at' => '2026-03-23 21:00:00'],

    // Notif untuk Talent 5 (Altar Sunda)
    ['id' => 12, 'user_id' => 9, 'title' => 'Undangan Ditolak',              'body' => 'Anda menolak undangan dari Pasar Bandoeng untuk Metal Malam.',                                                         'type' => 'invitation',  'reference_id' => 9,  'is_read' => true,  'created_at' => '2026-03-17 09:00:00', 'updated_at' => '2026-03-17 09:30:00'],

    // Notif untuk Talent 6 (Langit Sore)
    ['id' => 13, 'user_id' => 10, 'title' => 'Undangan Manggung Baru.',     'body' => 'Kafe Braga Permai mengundang Langit Sore untuk tampil di Braga Indie Fest 2026. Cek detailnya.',                        'type' => 'invitation',  'reference_id' => 8,  'is_read' => false, 'created_at' => '2026-04-06 09:00:00', 'updated_at' => '2026-04-06 09:00:00'],

    // Notif untuk EO 2 (Athila - Kafe Braga Permai)
    ['id' => 14, 'user_id' => 2, 'title' => 'Lamaran Baru Masuk.',           'body' => 'The Rotten Bandung melamar untuk Braga Punk Night Vol.5. Segera review lamarannya.',                                   'type' => 'application', 'reference_id' => 1,  'is_read' => true,  'created_at' => '2026-04-02 13:00:00', 'updated_at' => '2026-04-02 13:30:00'],
    ['id' => 15, 'user_id' => 2, 'title' => 'Lamaran Baru Masuk.',           'body' => 'Langit Sore melamar untuk Braga Punk Night Vol.5.',                                                                    'type' => 'application', 'reference_id' => 2,  'is_read' => false, 'created_at' => '2026-04-03 10:00:00', 'updated_at' => '2026-04-03 10:00:00'],

    // Notif untuk EO 3 (Bill - Pasar Bandoeng)
    ['id' => 16, 'user_id' => 3, 'title' => 'DJ Arfz Terima Booking.',       'body' => 'DJ Arfz menerima booking untuk Pasar Bandoeng Weekend Vibes. Event Anda siap.',                                        'type' => 'booking',     'reference_id' => 1,  'is_read' => true,  'created_at' => '2026-04-03 10:10:00', 'updated_at' => '2026-04-03 10:30:00'],

    // Notif untuk EO 8 (Jeany - Braga Art Space)
    ['id' => 17, 'user_id' => 8, 'title' => 'Siti ND Terima Booking.',       'body' => 'Siti ND Jazz menerima booking untuk Braga Jazz Evening 24 Mei. Event Anda siap.',                                      'type' => 'booking',     'reference_id' => 2,  'is_read' => false, 'created_at' => '2026-04-04 09:10:00', 'updated_at' => '2026-04-04 09:10:00'],
]);

DB::statement("SET session_replication_role = 'origin';");

echo "=== SEEDER SELESAI ===\n";
    }
}
