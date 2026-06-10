# CariTalent — Backend API

CariTalent adalah platform direktori dan booking talenta ekonomi kreatif yang menghubungkan Event Organizer (EO) dengan talenta lokal seperti musisi indie, band, dan seniman visual. EO dapat menemukan talenta yang sesuai melalui sistem matchmaking yang mempertimbangkan genre, budget, dan lokasi.

Repositori ini berisi **Backend Web Service API** yang dibangun dengan **Laravel 11** dan **PostgreSQL**, mencakup autentikasi berbasis peran, manajemen profil talenta, alur booking, hingga sistem notifikasi dan ulasan.

---

## Tim Pengembang (Backend)

### 1. Athila Ramdani Saputra — Ketua Tim / Core Engineer
- Setup arsitektur Laravel, standar JSON response, dan error handling global
- Admin Module: statistik platform, manajemen pengguna, moderasi event, dan verifikasi talent
- Intelligent Matchmaking Engine: algoritma skoring berbasis kecocokan genre, budget, dan jarak lokasi

### 2. Muhammad Irgiansyah — Backend Engineer
- Authentication & User Profile: registrasi, login, dan role-based access menggunakan Laravel Sanctum
- Talent Master & Portfolio: pengelolaan genre, profil talent, dan integrasi media portfolio
- Application Flow: siklus apply dari talent ke event beserta manajemen status lamaran

### 3. Arfian Ghifari — Backend Engineer
- Event Management: pembuatan, pengelolaan, publikasi, dan pembatalan event oleh EO
- Invitation & Booking: siklus undangan langsung dari EO ke talent, negosiasi harga, dan kontrak booking
- Review & Notifications: sistem ulasan lintas pengguna dan trigger notifikasi otomatis

---

## Fitur Utama

- Role-based authentication (Admin, Event Organizer, Talent)
- CRUD profil talent dengan upload media
- CRUD event dengan pencarian berbasis lokasi dan genre
- Matchmaking cerdas dengan skor relevansi per-EO
- Alur Application (lamaran dari talent) dan Invitation (undangan dari EO)
- Notifikasi terintegrasi di setiap perubahan status transaksi
- Dokumentasi API interaktif via Swagger UI

---

## Arsitektur Backend

API ini mengikuti pola **MVC (Model-View-Controller)** standar Laravel, dengan beberapa lapisan tambahan untuk menjaga konsistensi response dan validasi input.

```
Client (Postman / Frontend)
       |
       v
  routes/api.php          <- Daftar seluruh endpoint API
       |
       v
  Middleware              <- Autentikasi (Sanctum), Role Guard
       |
       v
  Controllers             <- Logika bisnis per modul
       |
    /     \
Models   Requests         <- ORM Eloquent + Form Validation
   |
   v
PostgreSQL (via Eloquent ORM)
```

**Pola response** seluruh API menggunakan JSON envelope yang konsisten melalui trait `ApiResponse`:

```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

Sistem notifikasi berjalan menggunakan **Laravel Queue** (database driver) yang di-trigger otomatis setiap kali status booking, invitation, atau application berubah.

---

## Struktur Folder

```
caritalentbe/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Semua controller per modul (Auth, Event, Talent, Booking, dll.)
│   │   ├── Middleware/         # Middleware autentikasi dan role access
│   │   └── Requests/           # Form request untuk validasi input API
│   ├── Models/                 # Model Eloquent (User, Talent, Event, Booking, dll.)
│   ├── Providers/              # Service provider Laravel
│   └── Traits/
│       └── ApiResponse.php     # Trait untuk standarisasi format JSON response
│
├── config/                     # Konfigurasi Laravel (database, auth, queue, dll.)
├── database/
│   ├── migrations/             # File migrasi tabel database
│   ├── seeders/                # Data awal / dummy data untuk development
│   └── factories/              # Factory untuk generate data testing
│
├── docs/                       # File dokumentasi tambahan (Swagger YAML/JSON)
├── routes/
│   └── api.php                 # Definisi semua endpoint REST API
│
├── storage/                    # File upload, log, dan cache framework
├── docker/                     # Konfigurasi Docker untuk deployment
├── initialize.bat              # Script setup awal — Windows
├── initialize.sh               # Script setup awal — Mac/Linux
├── running.bat                 # Script jalankan server — Windows
├── running.sh                  # Script jalankan server — Mac/Linux
└── .env.example                # Template environment variable
```

### Penjelasan Folder Utama

| Folder / File | Keterangan |
|---|---|
| `app/Http/Controllers/` | Berisi controller untuk setiap modul: `AuthController`, `EventController`, `TalentController`, `BookingController`, `InvitationController`, `ApplicationController`, `ReviewController`, `MatchmakingController`, `NotificationController`, dan `AdminController` |
| `app/Http/Requests/` | Kelas validasi input per endpoint. Memisahkan logika validasi dari controller agar tetap bersih |
| `app/Http/Middleware/` | Middleware untuk cek autentikasi Sanctum dan verifikasi role pengguna |
| `app/Models/` | Model Eloquent yang merepresentasikan tabel di database: `User`, `Talent`, `Event`, `Application`, `Booking`, `Invitation` (via polymorphic), `Review`, `Notification`, `Genre` |
| `app/Traits/ApiResponse.php` | Trait reusable untuk memformat seluruh JSON response secara konsisten |
| `database/migrations/` | File migrasi yang mendefinisikan struktur tabel. Urutan file menentukan urutan pembuatan tabel |
| `database/seeders/` | Seeder untuk mengisi data awal (genre, user admin, dll.) |
| `routes/api.php` | Titik masuk seluruh endpoint API. Dikelompokkan berdasarkan peran dan middleware |
| `docs/` | Berisi file konfigurasi Swagger untuk dokumentasi API interaktif |
| `storage/` | Menyimpan file yang diupload talent (foto, portfolio), log aplikasi, dan cache |
| `docker/` | Berisi konfigurasi Nginx dan pengaturan Docker untuk kebutuhan deployment |

---

## Setup & Instalasi Lokal

### Persyaratan

Pastikan sudah terinstall:
- PHP 8.2+
- Composer
- Node.js & NPM
- PostgreSQL 16

---

### Mac / Linux

**Setup awal (hanya sekali):**

```bash
chmod +x initialize.sh
./initialize.sh
```

Script ini akan menjalankan `composer install`, membuat file `.env`, generate app key, membuat database `caritalent_db`, dan menjalankan migrasi.

> Pastikan PostgreSQL sudah berjalan sebelum menjalankan script ini. Jika menggunakan Postgres.app, buka aplikasinya terlebih dahulu. Jika menggunakan Homebrew, jalankan `brew services start postgresql@16`.

**Jalankan server setiap hari:**

```bash
./running.sh
```

Script ini akan memastikan PostgreSQL aktif, lalu menjalankan `php artisan serve`. Browser akan otomatis membuka halaman dokumentasi API.

---

### Windows

**Setup awal (hanya sekali):**

Klik kanan `initialize.bat` lalu pilih **Run as Administrator**.

Script ini akan mengaktifkan driver PHP, menginstall PostgreSQL 16 (jika belum ada), membuat database `caritalent_db`, menjalankan `composer install`, dan migrasi tabel.

**Jalankan server setiap hari:**

Klik kanan `running.bat` lalu pilih **Run as Administrator**.

---

### Akses Setelah Server Berjalan

| Layanan | URL |
|---|---|
| Server lokal | `http://127.0.0.1:8000` |
| Swagger API Docs | `http://127.0.0.1:8000/api/documentation` |

---

## Konfigurasi Database

Edit file `.env` dan sesuaikan dengan pengaturan lokal:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=caritalent_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Setelah ada perubahan skema dari anggota tim, cukup jalankan:

```bash
php artisan migrate
```

---

## Catatan Tim

- Jika ada konflik di `routes/api.php`, koordinasikan dengan Ketua BE (Athila) sebelum resolve.
- Untuk menjalankan ulang seeder: `php artisan db:seed`
- Pastikan `.env` tidak pernah di-push ke repositori (sudah ada di `.gitignore`).
