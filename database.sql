-- ============================================================
-- CariTalent Database Dump
-- Platform: Direktori & Booking Talent Ekonomi Kreatif
-- Database: caritalent_db (PostgreSQL)
-- Generated from: Laravel Migrations + DummyDataSeeder
-- ============================================================

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;
SET session_replication_role = 'replica';

-- ============================================================
-- DROP TABLES (urutan terbalik untuk menghindari FK constraint)
-- ============================================================
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS applications CASCADE;
DROP TABLE IF EXISTS event_genre CASCADE;
DROP TABLE IF EXISTS genre_talent CASCADE;
DROP TABLE IF EXISTS talents CASCADE;
DROP TABLE IF EXISTS events CASCADE;
DROP TABLE IF EXISTS genres CASCADE;
DROP TABLE IF EXISTS personal_access_tokens CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS jobs CASCADE;
DROP TABLE IF EXISTS failed_jobs CASCADE;
DROP TABLE IF EXISTS job_batches CASCADE;
DROP TABLE IF EXISTS cache CASCADE;
DROP TABLE IF EXISTS cache_locks CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS migrations CASCADE;

-- ============================================================
-- TABLE: migrations
-- ============================================================
CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) DEFAULT NULL,
    role VARCHAR(255) NOT NULL DEFAULT 'talent' CHECK (role IN ('admin', 'eo', 'talent')),
    email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    fcm_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

-- ============================================================
-- TABLE: password_reset_tokens
-- ============================================================
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

-- ============================================================
-- TABLE: sessions
-- ============================================================
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

-- ============================================================
-- TABLE: cache
-- ============================================================
CREATE TABLE cache (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

CREATE TABLE cache_locks (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- ============================================================
-- TABLE: jobs
-- ============================================================
CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER DEFAULT NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX jobs_queue_index ON jobs (queue);

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT DEFAULT NULL,
    cancelled_at INTEGER DEFAULT NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER DEFAULT NULL
);

CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: personal_access_tokens (Laravel Sanctum)
-- ============================================================
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT DEFAULT NULL,
    last_used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

-- ============================================================
-- TABLE: genres
-- ============================================================
CREATE TABLE genres (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
);

-- ============================================================
-- TABLE: talents
-- ============================================================
CREATE TABLE talents (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    stage_name VARCHAR(255) NOT NULL,
    price_min NUMERIC(15, 2) DEFAULT NULL,
    price_max NUMERIC(15, 2) DEFAULT NULL,
    city VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    portfolio_link VARCHAR(255) DEFAULT NULL,
    verified BOOLEAN NOT NULL DEFAULT FALSE,
    average_rating FLOAT NOT NULL DEFAULT 0,
    total_reviews INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT talents_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: genre_talent (pivot talent - genre)
-- ============================================================
CREATE TABLE genre_talent (
    id BIGSERIAL PRIMARY KEY,
    talent_id BIGINT NOT NULL,
    genre_id BIGINT NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT genre_talent_talent_id_foreign FOREIGN KEY (talent_id) REFERENCES talents(id) ON DELETE CASCADE,
    CONSTRAINT genre_talent_genre_id_foreign FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    CONSTRAINT genre_talent_talent_id_genre_id_unique UNIQUE (talent_id, genre_id)
);

-- ============================================================
-- TABLE: events
-- ============================================================
CREATE TABLE events (
    id BIGSERIAL PRIMARY KEY,
    organizer_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    budget NUMERIC(15, 2) NOT NULL,
    event_date DATE NOT NULL,
    venue_name VARCHAR(255) NOT NULL,
    full_address TEXT DEFAULT NULL,
    latitude NUMERIC(10, 8) DEFAULT NULL,
    longitude NUMERIC(11, 8) DEFAULT NULL,
    city VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'dibuka',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT events_organizer_id_foreign FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT events_status_check CHECK (status IN ('dibuka', 'ditutup', 'selesai', 'dibatalkan'))
);

-- ============================================================
-- TABLE: event_genre (pivot event - genre)
-- ============================================================
CREATE TABLE event_genre (
    id BIGSERIAL PRIMARY KEY,
    event_id BIGINT NOT NULL,
    genre_id BIGINT NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT event_genre_event_id_foreign FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT event_genre_event_id_genre_id_unique UNIQUE (event_id, genre_id)
);

-- ============================================================
-- TABLE: applications
-- ============================================================
CREATE TABLE applications (
    id BIGSERIAL PRIMARY KEY,
    event_id BIGINT NOT NULL,
    talent_id BIGINT NOT NULL,
    source VARCHAR(255) NOT NULL CHECK (source IN ('apply', 'invitation')),
    message TEXT DEFAULT NULL,
    proposed_price NUMERIC(15, 2) DEFAULT NULL,
    offered_price NUMERIC(15, 2) DEFAULT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'rejected')),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT applications_event_id_foreign FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT applications_talent_id_foreign FOREIGN KEY (talent_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT applications_event_id_talent_id_source_unique UNIQUE (event_id, talent_id, source)
);

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE bookings (
    id BIGSERIAL PRIMARY KEY,
    application_id BIGINT NOT NULL,
    agreed_price NUMERIC(15, 2) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'confirmed' CHECK (status IN ('confirmed', 'completed', 'cancelled')),
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT bookings_application_id_foreign FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE reviews (
    id BIGSERIAL PRIMARY KEY,
    booking_id BIGINT NOT NULL UNIQUE,
    rating INTEGER NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT reviews_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    type VARCHAR(255) NOT NULL CHECK (type IN ('application', 'booking', 'invitation', 'review', 'event', 'talent')),
    action VARCHAR(255) DEFAULT NULL,
    reference_type VARCHAR(255) DEFAULT NULL,
    reference_id BIGINT DEFAULT NULL,
    data JSONB DEFAULT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- DATA: migrations
-- ============================================================
INSERT INTO migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_03_31_141235_create_personal_access_tokens_table', 1),
    ('2026_04_02_040524_create_events_table', 1),
    ('2026_04_02_040525_create_applications_table', 1),
    ('2026_04_05_052110_create_genres_table', 1),
    ('2026_04_05_052111_create_talent_table', 1),
    ('2026_04_05_052113_create_genre_talent_table', 1),
    ('2026_04_05_115032_create_bookings_table', 1),
    ('2026_04_05_115032_create_notifications_table', 1),
    ('2026_04_05_115032_create_reviews_table', 1),
    ('2026_05_08_000000_update_talents_user_id_and_city', 2),
    ('2026_05_19_155507_add_fcm_token_to_users_table', 2),
    ('2026_05_21_025428_add_full_address_to_events_table', 2),
    ('2026_05_24_114404_update_events_status_check_constraint', 2),
    ('2026_05_24_140730_add_missing_columns_to_notifications_table', 2),
    ('2026_05_25_200000_update_notifications_type_check_constraint', 2);

-- ============================================================
-- DATA: genres
-- ============================================================
INSERT INTO genres (id, name, created_at, updated_at) VALUES
    (1,  'Pop Punk',         '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (2,  'Heavy Metal',      '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (3,  'DJ',               '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (4,  'Solo Singer',      '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (5,  'Hardcore',         '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (6,  'Jazz',             '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (7,  'Seniman Visual',   '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (8,  'Street Performer', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (9,  'Alternative Rock', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (10, 'Indie Pop',        '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (11, 'R&B',              '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (12, 'Acoustic',         '2026-01-01 08:00:00', '2026-01-01 08:00:00');

-- ============================================================
-- DATA: users
-- password semua: password123 (bcrypt hash)
-- ============================================================
INSERT INTO users (id, name, email, phone, role, email_verified_at, password, remember_token, fcm_token, created_at, updated_at) VALUES
    (1,  'Aprilianza Muhammad Yusup', 'aprilianza@caritalent.id',   '081234560001', 'admin',  '2026-01-01 08:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_admin_april_dummy_token_000',   '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
    (2,  'Athila Ramdani Saputra',    'athila@kafebraga.id',        '081234560002', 'eo',     '2026-01-05 09:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_eo_athila_dummy_token_001',     '2026-01-05 09:00:00', '2026-01-05 09:00:00'),
    (3,  'Bill Stephen Sembiring',    'bill@pasarbandoeng.id',      '081234560003', 'eo',     '2026-01-07 10:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_eo_bill_dummy_token_002',       '2026-01-07 10:00:00', '2026-01-07 10:00:00'),
    (4,  'Muhammad Irgiansyah',       'irgi@gmail.com',             '081234560004', 'talent', '2026-01-10 11:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_irgi_dummy_token_004',   '2026-01-10 11:00:00', '2026-01-10 11:00:00'),
    (5,  'Arfian Ghifari Mahya',      'arfian@gmail.com',           '081234560005', 'talent', '2026-01-12 11:30:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_arfian_dummy_token_005', '2026-01-12 11:30:00', '2026-01-12 11:30:00'),
    (6,  'Rizky Maulana',             'rizky.maulana@gmail.com',    '081234560006', 'talent', '2026-01-15 12:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_rizky_dummy_token_006',  '2026-01-15 12:00:00', '2026-01-15 12:00:00'),
    (7,  'Siti Nurhaliza Dewi',       'siti.ndewi@gmail.com',       '081234560007', 'talent', '2026-01-18 13:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_siti_dummy_token_007',   '2026-01-18 13:00:00', '2026-01-18 13:00:00'),
    (8,  'Jeany Ferliza Nayla',       'jeany@bragapermai.id',       '081234560008', 'eo',     '2026-01-08 10:30:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_eo_jeany_dummy_token_003',      '2026-01-08 10:30:00', '2026-01-08 10:30:00'),
    (9,  'Dendi Prasetyo',            'dendi.pras@gmail.com',       '081234560009', 'talent', '2026-01-20 09:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_dendi_dummy_token_009',  '2026-01-20 09:00:00', '2026-01-20 09:00:00'),
    (10, 'Fauzan Akbar Nugraha',      'fauzan.akbar@gmail.com',     '081234560010', 'talent', '2026-01-22 10:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_fauzan_dummy_token_010', '2026-01-22 10:00:00', '2026-01-22 10:00:00'),
    (11, 'Hendra Wijaya',             'hendra.wijaya@gmail.com',    '081234560011', 'eo',     '2026-01-25 11:00:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_eo_hendra_dummy_token_008',     '2026-01-25 11:00:00', '2026-01-25 11:00:00'),
    (12, 'Nandita Kusuma Wardhani',   'nandita.kw@gmail.com',       '081234560012', 'talent', '2026-01-28 09:30:00', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'fcm_talent_nandita_dummy_token_012','2026-01-28 09:30:00', '2026-01-28 09:30:00');

-- ============================================================
-- DATA: talents
-- ============================================================
INSERT INTO talents (id, user_id, stage_name, price_min, price_max, city, bio, portfolio_link, verified, average_rating, total_reviews, created_at, updated_at) VALUES
    (4,  4,  'The Rotten Bandung',     500000,  2000000, 'Bandung', 'Band pop punk asal Dago Bandung, aktif sejak 2019. Sering manggung di kafe dan venue indie Bandung. Repertoar hits: Peach (The Jansen), Risk It All (Bruno Mars), Kamu Cuma Mau Enaknya Aja (Juicy Luicy), dan Bintang di Surga (Peterpan).', 'https://youtube.com/@therottenbandung',  TRUE,  4.80, 5, '2026-01-10 11:30:00', '2026-03-20 10:00:00'),
    (5,  5,  'DJ Arfz Bdg',            800000,  3500000, 'Bandung', 'DJ asal Bandung Selatan dengan pengalaman 4 tahun. Spesialis EDM, Hiphop, dan Pop remix. Pernah mengisi di Braga Festival, Dago Culinary Night, dan Summarecon Mal Bandung.',                                                              'https://soundcloud.com/djarzfbdg',       TRUE,  4.50, 2, '2026-01-12 12:00:00', '2026-03-10 09:00:00'),
    (6,  6,  'Rizky Maulana Acoustic', 300000,  1200000, 'Bandung', 'Penyanyi solo dengan gitar akustik khas Bandung. Membawakan lagu-lagu Dewa 19, Sheila on 7, Juicy Luicy, dan hits OPM. Cocok untuk suasana kafe intimate dan dinner.',                                                                      'https://youtube.com/@rizkymaulanaacoustic', TRUE, 4.67, 3, '2026-01-15 12:30:00', '2026-03-15 11:00:00'),
    (7,  7,  'Siti ND Jazz',           600000,  2500000, 'Bandung', 'Vokalis jazz dan R&B lulusan ISI Bandung. Membawakan jazz standar, bossa nova, hingga jazz-pop modern. Pernah tampil di Braga City Walk, 23 Paskal, dan berbagai pesta pernikahan mewah Bandung.',                                           'https://instagram.com/sitindjazz',       TRUE,  4.90, 4, '2026-01-18 13:30:00', '2026-03-18 14:00:00'),
    (9,  9,  'Altar Sunda',            700000,  2800000, 'Bandung', 'Band metal asal Cimahi-Bandung yang membawakan heavy metal dan thrash. Terinspirasi dari Burgerkill, Seringai, dan Metallica. Energi tinggi di atas panggung.',                                                                               'https://youtube.com/@altarsunda',        FALSE, 0.00, 0, '2026-01-20 09:30:00', '2026-01-20 09:30:00'),
    (10, 10, 'Langit Sore',            400000,  1500000, 'Bandung', 'Duo indie pop Bandung dengan nuansa dreamy dan lo-fi. Membawakan lagu sendiri dan cover hits The Jansen, Hindia, serta Feast. Cocok untuk suasana sore santai.',                                                                              'https://spotify.com/artist/langitsore',  FALSE, 0.00, 0, '2026-01-22 10:30:00', '2026-01-22 10:30:00'),
    (12, 12, 'Nandita Visual Art',     250000,  1000000, 'Bandung', 'Seniman visual dan live painter asal Bandung. Spesialis live mural, lukis kanvas di depan penonton, dan performance art. Telah tampil di berbagai festival seni Bandung termasuk Bandung Art Month.',                                         'https://instagram.com/nanditavisualart', TRUE,  5.00, 1, '2026-01-28 10:00:00', '2026-03-25 15:00:00');

-- ============================================================
-- DATA: genre_talent
-- ============================================================
INSERT INTO genre_talent (talent_id, genre_id, created_at, updated_at) VALUES
    -- The Rotten Bandung: Pop Punk, Alternative Rock, Hardcore
    (4,  1,  NOW(), NOW()),
    (4,  9,  NOW(), NOW()),
    (4,  5,  NOW(), NOW()),
    -- DJ Arfz Bdg: DJ
    (5,  3,  NOW(), NOW()),
    -- Rizky Maulana: Solo Singer, Acoustic, Indie Pop
    (6,  4,  NOW(), NOW()),
    (6,  12, NOW(), NOW()),
    (6,  10, NOW(), NOW()),
    -- Siti ND Jazz: Jazz, R&B, Solo Singer
    (7,  6,  NOW(), NOW()),
    (7,  11, NOW(), NOW()),
    (7,  4,  NOW(), NOW()),
    -- Altar Sunda: Heavy Metal, Hardcore
    (9,  2,  NOW(), NOW()),
    (9,  5,  NOW(), NOW()),
    -- Langit Sore: Indie Pop, Acoustic, Alternative Rock
    (10, 10, NOW(), NOW()),
    (10, 12, NOW(), NOW()),
    (10, 9,  NOW(), NOW()),
    -- Nandita Visual Art: Seniman Visual, Street Performer
    (12, 7,  NOW(), NOW()),
    (12, 8,  NOW(), NOW());

-- ============================================================
-- DATA: events
-- ============================================================
INSERT INTO events (id, organizer_id, title, description, budget, event_date, venue_name, full_address, latitude, longitude, city, status, created_at, updated_at) VALUES
    (1,  2,  'Braga Punk Night Vol.5',         'Malam punk rock bulanan di Kafe Braga Permai. Kami mencari band energetik yang siap mengguncang panggung. Setlist wajib ada cover The Jansen dan Neck Deep.',                                                                2000000, '2026-05-10', 'Kafe Braga Permai',                     'Jl. Braga No.99, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9109, 107.6089, 'Bandung', 'dibuka',     '2026-04-01 10:00:00', '2026-04-01 10:00:00'),
    (2,  3,  'Pasar Bandoeng Weekend Vibes',   'Event weekend di Pasar Bandoeng Kota Baru Parahyangan. Butuh DJ untuk mengisi suasana dari sore hingga malam dengan lagu-lagu pop, hiphop, dan EDM hype.',                                                                  3000000, '2026-05-17', 'Pasar Bandoeng - Kota Baru Parahyangan','Jl. Parahyangan Kav. 8 Blok B1, Kota Baru Parahyangan, Padalarang, Kab. Bandung Barat, Jawa Barat 40553',                           -6.8380, 107.5361, 'Bandung', 'dibuka',     '2026-04-02 11:00:00', '2026-04-02 11:00:00'),
    (3,  8,  'Braga Jazz Evening',             'Evening jazz intimate di Braga Art Space. Mencari penyanyi jazz berbakat untuk menemani tamu menikmati dinner dan wine. Repertoar jazz standar dan bossa nova diutamakan.',                                                  2500000, '2026-05-24', 'Braga Art Space',                       'Jl. Braga No.49, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9116, 107.6095, 'Bandung', 'dibuka',     '2026-04-03 09:00:00', '2026-04-03 09:00:00'),
    (4,  11, 'Kopi Selasar Acoustic Sunday',   'Sesi acoustic rutin tiap minggu di Kopi Selasar Sunaryo. Suasana santai, butuh musisi atau penyanyi dengan gitar akustik. Lagu-lagu hits seperti Dewa 19, Juicy Luicy, dan Bruno Mars sangat cocok.',                       800000, '2026-05-03', 'Kopi Selasar Sunaryo Art Space',        'Jl. Bukit Pakar Timur No.100, Ciburial, Kec. Cimenyan, Kab. Bandung, Jawa Barat 40198',                                            -6.8733, 107.6218, 'Bandung', 'dibuka',     '2026-04-04 08:00:00', '2026-04-04 08:00:00'),
    (5,  2,  'Braga Indie Fest 2026',          'Festival indie tahunan Braga. Masih dalam tahap perencanaan, butuh beberapa band indie pop dan alternative untuk lineup.',                                                                                                  5000000, '2026-06-20', 'Lapangan Kafe Braga Permai',            'Jl. Braga No.99, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9109, 107.6089, 'Bandung', 'ditutup',    '2026-04-05 14:00:00', '2026-04-05 14:00:00'),
    (6,  3,  'Pasar Bandoeng Metal Malam',     'Malam heavy metal khusus untuk komunitas underground Bandung. Sudah menemukan band yang cocok.',                                                                                                                            2500000, '2026-05-01', 'Pasar Bandoeng - Kota Baru Parahyangan','Jl. Parahyangan Kav. 8 Blok B1, Kota Baru Parahyangan, Padalarang, Kab. Bandung Barat, Jawa Barat 40553',                           -6.8380, 107.5361, 'Bandung', 'ditutup',    '2026-03-15 10:00:00', '2026-03-28 16:00:00'),
    (7,  2,  'Braga Punk Night Vol.4',         'Edisi keempat punk night Braga. Sudah berlangsung.',                                                                                                                                                                       1800000, '2026-03-15', 'Kafe Braga Permai',                     'Jl. Braga No.99, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9109, 107.6089, 'Bandung', 'selesai',    '2026-02-20 10:00:00', '2026-03-16 09:00:00'),
    (8,  8,  'Braga Jazz Evening Maret',       'Sesi jazz maret di Braga Art Space. Sudah selesai.',                                                                                                                                                                       2500000, '2026-03-22', 'Braga Art Space',                       'Jl. Braga No.49, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9116, 107.6095, 'Bandung', 'selesai',    '2026-02-25 09:00:00', '2026-03-23 10:00:00'),
    (9,  11, 'Kopi Selasar Acoustic Maret',    'Sesi acoustic bulan maret di Selasar. Sudah selesai.',                                                                                                                                                                      700000, '2026-03-09', 'Kopi Selasar Sunaryo Art Space',        'Jl. Bukit Pakar Timur No.100, Ciburial, Kec. Cimenyan, Kab. Bandung, Jawa Barat 40198',                                            -6.8733, 107.6218, 'Bandung', 'selesai',    '2026-02-10 08:00:00', '2026-03-10 08:00:00'),
    (10, 3,  'Pasar Bandoeng DJ Night Feb',    'DJ Night Februari di Pasar Bandoeng. Sudah selesai.',                                                                                                                                                                      3000000, '2026-02-22', 'Pasar Bandoeng - Kota Baru Parahyangan','Jl. Parahyangan Kav. 8 Blok B1, Kota Baru Parahyangan, Padalarang, Kab. Bandung Barat, Jawa Barat 40553',                           -6.8380, 107.5361, 'Bandung', 'selesai',    '2026-01-25 10:00:00', '2026-02-23 09:00:00'),
    (11, 8,  'Braga Art Night - Dibatalkan',   'Event seni visual malam yang terpaksa dibatalkan karena perubahan jadwal venue.',                                                                                                                                           1500000, '2026-04-05', 'Braga Art Space',                       'Jl. Braga No.49, Braga, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111',                                                        -6.9116, 107.6095, 'Bandung', 'dibatalkan', '2026-03-01 09:00:00', '2026-03-20 11:00:00');

-- ============================================================
-- DATA: event_genre
-- ============================================================
INSERT INTO event_genre (event_id, genre_id, created_at, updated_at) VALUES
    -- Event 1 - Punk Night: Pop Punk, Hardcore, Alternative Rock
    (1,  1,  NOW(), NOW()),
    (1,  5,  NOW(), NOW()),
    (1,  9,  NOW(), NOW()),
    -- Event 2 - Weekend Vibes: DJ
    (2,  3,  NOW(), NOW()),
    -- Event 3 - Jazz Evening: Jazz, R&B
    (3,  6,  NOW(), NOW()),
    (3,  11, NOW(), NOW()),
    -- Event 4 - Acoustic Sunday: Solo Singer, Acoustic, Indie Pop
    (4,  4,  NOW(), NOW()),
    (4,  12, NOW(), NOW()),
    (4,  10, NOW(), NOW()),
    -- Event 5 - Indie Fest: Indie Pop, Alternative Rock
    (5,  10, NOW(), NOW()),
    (5,  9,  NOW(), NOW()),
    -- Event 6 - Metal Night: Heavy Metal, Hardcore
    (6,  2,  NOW(), NOW()),
    (6,  5,  NOW(), NOW()),
    -- Event 7 - Punk Vol.4: Pop Punk, Alternative Rock
    (7,  1,  NOW(), NOW()),
    (7,  9,  NOW(), NOW()),
    -- Event 8 - Jazz Maret: Jazz, R&B
    (8,  6,  NOW(), NOW()),
    (8,  11, NOW(), NOW()),
    -- Event 9 - Acoustic Maret: Acoustic, Solo Singer
    (9,  12, NOW(), NOW()),
    (9,  4,  NOW(), NOW()),
    -- Event 10 - DJ Night: DJ
    (10, 3,  NOW(), NOW()),
    -- Event 11 - Art Night: Seniman Visual, Street Performer
    (11, 7,  NOW(), NOW()),
    (11, 8,  NOW(), NOW());

-- ============================================================
-- DATA: applications
-- ============================================================
INSERT INTO applications (id, event_id, talent_id, source, message, proposed_price, offered_price, status, created_at, updated_at) VALUES
    (1,  1,  4,  'apply',      'Halo. Kami The Rotten Bandung, band pop punk dengan pengalaman 5 tahun. Siap tampil maksimal di Braga Punk Night. Setlist kami ada cover Peach (The Jansen), Risk It All (Bruno Mars versi punk), dan beberapa lagu original.', 1500000, NULL,    'pending',  '2026-04-02 13:00:00', '2026-04-02 13:00:00'),
    (2,  1,  10, 'apply',      'Halo, kami Langit Sore, duo indie dengan nuansa alternative. Kami bisa membawakan set yang energetik untuk Punk Night, dengan sentuhan alternative yang fresh.',                                                                   1200000, NULL,    'pending',  '2026-04-03 10:00:00', '2026-04-03 10:00:00'),
    (3,  2,  4,  'apply',      'Kami bisa membawakan suasana seru dengan band, meski ini event DJ night.',                                                                                                                                                         1000000, NULL,    'rejected', '2026-04-02 14:00:00', '2026-04-03 09:00:00'),
    (4,  2,  5,  'apply',      'Saya DJ Arfz dari Bandung Selatan, spesialis EDM dan Pop remix. Siap mengisi Weekend Vibes Pasar Bandoeng dari jam 16.00 sampai selesai.',                                                                                        2500000, 2500000, 'accepted', '2026-04-02 15:00:00', '2026-04-03 10:00:00'),
    (5,  3,  7,  'apply',      'Selamat siang. Saya Siti ND, vokalis jazz lulusan ISI Bandung. Sangat tertarik untuk tampil di Braga Jazz Evening. Repertoar saya mencakup jazz standar, bossa nova, dan jazz-pop. Bisa menyesuaikan suasana dinner Anda.',       2000000, 2000000, 'accepted', '2026-04-03 11:00:00', '2026-04-04 09:00:00'),
    (6,  3,  6,  'apply',      'Saya Rizky, penyanyi acoustic. Bisa juga membawakan jazz-pop ringan untuk dinner.',                                                                                                                                                 900000, NULL,    'rejected', '2026-04-03 12:00:00', '2026-04-04 09:30:00'),
    (7,  4,  6,  'invitation', 'Kami mengundang Anda untuk tampil di Kopi Selasar Acoustic Sunday. Kami sudah menonton performa Anda dan yakin cocok dengan suasana kami.',                                                                                         700000, 700000,  'accepted', '2026-04-04 10:00:00', '2026-04-04 14:00:00'),
    (8,  5,  10, 'invitation', 'Halo Langit Sore. Kami sedang mempersiapkan Braga Indie Fest 2026 dan sangat tertarik mengundang kalian sebagai salah satu lineup. Harga yang kami tawarkan 1.2jt.',                                                              1200000, 1200000, 'pending',  '2026-04-06 09:00:00', '2026-04-06 09:00:00'),
    (9,  6,  9,  'invitation', 'Halo Altar Sunda. Kami butuh band metal untuk Pasar Bandoeng Metal Malam. Tertarik?',                                                                                                                                              2000000, 2000000, 'rejected', '2026-03-16 10:00:00', '2026-03-17 09:00:00'),
    (10, 1,  9,  'apply',      'Kami Altar Sunda, metal dari Bandung, bisa membawakan energi hardcore untuk Punk Night.',                                                                                                                                           1800000, NULL,    'rejected', '2026-04-02 16:00:00', '2026-04-03 08:00:00'),
    (11, 7,  4,  'apply',      'The Rotten Bandung siap untuk Punk Night Vol.4.',                                                                                                                                                                                  1500000, 1500000, 'accepted', '2026-02-21 10:00:00', '2026-02-22 09:00:00'),
    (12, 8,  7,  'invitation', 'Terima kasih atas undangannya, saya sangat senang bisa tampil di Braga Art Space.',                                                                                                                                               2000000, 2000000, 'accepted', '2026-02-26 09:00:00', '2026-02-27 10:00:00'),
    (13, 9,  6,  'apply',      'Siap tampil di Kopi Selasar.',                                                                                                                                                                                                      600000, 600000,  'accepted', '2026-02-11 09:00:00', '2026-02-12 10:00:00'),
    (14, 10, 5,  'apply',      'DJ Arfz siap guncang Pasar Bandoeng.',                                                                                                                                                                                            2500000, 2500000, 'accepted', '2026-01-26 10:00:00', '2026-01-27 09:00:00'),
    (15, 7,  10, 'apply',      'Langit Sore ingin ikut Punk Vol.4.',                                                                                                                                                                                               1000000, NULL,    'rejected', '2026-02-21 11:00:00', '2026-02-22 09:30:00'),
    (16, 11, 12, 'apply',      'Nandita Visual Art siap untuk live painting di Braga Art Night.',                                                                                                                                                                    800000, 800000,  'accepted', '2026-03-02 10:00:00', '2026-03-02 10:00:00');

-- ============================================================
-- DATA: bookings
-- ============================================================
INSERT INTO bookings (id, application_id, agreed_price, status, created_at, updated_at) VALUES
    (1, 4,  2500000, 'confirmed', '2026-04-03 10:05:00', '2026-04-03 10:05:00'),
    (2, 5,  2000000, 'confirmed', '2026-04-04 09:05:00', '2026-04-04 09:05:00'),
    (3, 7,   700000, 'confirmed', '2026-04-04 14:05:00', '2026-04-04 14:05:00'),
    (4, 11, 1500000, 'completed', '2026-02-22 09:05:00', '2026-03-16 09:00:00'),
    (5, 12, 2000000, 'completed', '2026-02-27 10:05:00', '2026-03-23 10:00:00'),
    (6, 13,  600000, 'completed', '2026-02-12 10:05:00', '2026-03-10 08:00:00'),
    (7, 14, 2500000, 'completed', '2026-01-27 09:05:00', '2026-02-23 09:00:00'),
    (8, 16,  800000, 'cancelled', '2026-03-02 10:05:00', '2026-03-02 10:05:00');

-- ============================================================
-- DATA: reviews
-- ============================================================
INSERT INTO reviews (id, booking_id, rating, comment, created_at, updated_at) VALUES
    (1, 4, 5, 'The Rotten Bandung luar biasa. Energi di panggung sangat tinggi, penonton langsung hype dari lagu pertama. Cover Peach dari The Jansen dibawakan dengan sempurna. Pasti kami undang lagi.', '2026-03-16 20:00:00', '2026-03-16 20:00:00'),
    (2, 5, 5, 'Penampilan Siti ND Jazz di Braga Jazz Evening Maret sangat memukau. Suaranya yang merdu membuat malam itu terasa sangat syahdu. Terima kasih banyak!',                                      '2026-03-23 20:00:00', '2026-03-23 20:00:00'),
    (3, 6, 4, 'Rizky tampil bagus dan bikin suasana Kopi Selasar makin nyaman. Pilihan lagunya pas banget, ada Dewa 19 dan Juicy Luicy. Cukup memuaskan, meski sound system sedikit kurang optimal.',       '2026-03-10 20:00:00', '2026-03-10 20:00:00'),
    (4, 7, 4, 'DJ Arfz berhasil bikin Pasar Bandoeng malam itu hidup banget. Set EDM-nya bagus dan crowd terus antusias. Transisi antar lagu di awal agak terburu-buru, tapi keseluruhan memuaskan.',      '2026-02-23 22:00:00', '2026-02-23 22:00:00');

-- ============================================================
-- DATA: notifications (sample)
-- ============================================================
INSERT INTO notifications (id, user_id, title, body, type, action, reference_type, reference_id, data, is_read, read_at, created_at, updated_at) VALUES
    (1,  4,  'Lamaran Diterima',          'Selamat. Lamaran Anda ke Braga Punk Night Vol.4 telah diterima oleh Kafe Braga Permai.',                             'application', 'application_accepted', 'booking',     4,  '{"application_id":11,"booking_id":4,"event_id":7,"event_title":"Braga Punk Night Vol.4","agreed_price":1500000}',      TRUE,  '2026-02-22 09:30:00', '2026-02-22 09:05:00', '2026-02-22 09:30:00'),
    (2,  4,  'Review Baru Masuk',         'Kafe Braga Permai memberikan review bintang 5 untuk penampilan Anda di Braga Punk Night Vol.4.',                     'review',       'review_created',       'review',      1,  '{"rating":5,"booking_id":4,"event_id":7,"reviewer_name":"Athila Ramdani Saputra"}',                                      TRUE,  '2026-03-17 08:00:00', '2026-03-16 20:00:00', '2026-03-17 08:00:00'),
    (3,  2,  'Booking Terkonfirmasi',     'The Rotten Bandung telah dikonfirmasi untuk Braga Punk Night Vol.4.',                                                'booking',      'booking_confirmed',    'booking',     4,  '{"booking_id":4,"application_id":11,"talent_name":"The Rotten Bandung","agreed_price":1500000}',                          TRUE,  '2026-02-22 09:30:00', '2026-02-22 09:05:00', '2026-02-22 09:30:00'),
    (4,  7,  'Lamaran Diterima',          'Selamat. Lamaran Anda ke Braga Jazz Evening Maret telah diterima.',                                                  'application', 'application_accepted', 'booking',     5,  '{"application_id":12,"booking_id":5,"event_id":8,"event_title":"Braga Jazz Evening Maret","agreed_price":2000000}',     TRUE,  '2026-02-27 10:30:00', '2026-02-27 10:05:00', '2026-02-27 10:30:00'),
    (5,  7,  'Review Baru Masuk',         'Braga Art Space memberikan review bintang 5 untuk penampilan Anda di Braga Jazz Evening Maret.',                     'review',       'review_created',       'review',      2,  '{"rating":5,"booking_id":5,"event_id":8,"reviewer_name":"Jeany Ferliza Nayla"}',                                         TRUE,  '2026-03-24 08:00:00', '2026-03-23 20:00:00', '2026-03-24 08:00:00'),
    (6,  6,  'Undangan Baru dari EO',     'Anda mendapat undangan dari Kopi Selasar Sunaryo untuk tampil di Kopi Selasar Acoustic Sunday.',                     'invitation',   'invitation_received',  'application', 7,  '{"event_id":4,"event_title":"Kopi Selasar Acoustic Sunday","offered_price":700000,"organizer":"Hendra Wijaya"}',          FALSE, NULL,                '2026-04-04 10:00:00', '2026-04-04 10:00:00'),
    (7,  6,  'Lamaran Diterima',          'Selamat. Lamaran Anda ke Kopi Selasar Acoustic Maret telah diterima.',                                               'application', 'application_accepted', 'booking',     6,  '{"application_id":13,"booking_id":6,"event_id":9,"event_title":"Kopi Selasar Acoustic Maret","agreed_price":600000}',    TRUE,  '2026-02-12 10:30:00', '2026-02-12 10:05:00', '2026-02-12 10:30:00'),
    (8,  5,  'Lamaran Diterima',          'Selamat. Lamaran Anda ke Pasar Bandoeng DJ Night Februari telah diterima.',                                          'application', 'application_accepted', 'booking',     7,  '{"application_id":14,"booking_id":7,"event_id":10,"event_title":"Pasar Bandoeng DJ Night Feb","agreed_price":2500000}',  TRUE,  '2026-01-27 09:30:00', '2026-01-27 09:05:00', '2026-01-27 09:30:00'),
    (9,  12, 'Booking Dibatalkan',        'Booking Anda untuk Braga Art Night telah dibatalkan karena event dibatalkan oleh EO.',                               'booking',      'booking_cancelled',    'booking',     8,  '{"booking_id":8,"event_id":11,"event_title":"Braga Art Night - Dibatalkan"}',                                             FALSE, NULL,                '2026-03-20 11:00:00', '2026-03-20 11:00:00'),
    (10, 4,  'Lamaran Ditolak',           'Mohon maaf, lamaran Anda ke Pasar Bandoeng Weekend Vibes tidak diterima.',                                           'application', 'application_rejected', 'application', 3,  '{"application_id":3,"event_id":2,"event_title":"Pasar Bandoeng Weekend Vibes"}',                                           TRUE,  '2026-04-03 10:00:00', '2026-04-03 09:00:00', '2026-04-03 10:00:00');

-- ============================================================
-- RESET SEQUENCES (agar AUTO INCREMENT tidak bentrok)
-- ============================================================
SELECT setval('users_id_seq',              (SELECT MAX(id) FROM users));
SELECT setval('genres_id_seq',             (SELECT MAX(id) FROM genres));
SELECT setval('talents_id_seq',            (SELECT MAX(id) FROM talents));
SELECT setval('genre_talent_id_seq',       (SELECT MAX(id) FROM genre_talent));
SELECT setval('events_id_seq',             (SELECT MAX(id) FROM events));
SELECT setval('event_genre_id_seq',        (SELECT MAX(id) FROM event_genre));
SELECT setval('applications_id_seq',       (SELECT MAX(id) FROM applications));
SELECT setval('bookings_id_seq',           (SELECT MAX(id) FROM bookings));
SELECT setval('reviews_id_seq',            (SELECT MAX(id) FROM reviews));
SELECT setval('notifications_id_seq',      (SELECT MAX(id) FROM notifications));
SELECT setval('migrations_id_seq',         (SELECT MAX(id) FROM migrations));

-- ============================================================
-- RESET FK CHECK
-- ============================================================
SET session_replication_role = 'origin';
