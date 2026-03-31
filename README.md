# CariTalent

CariTalent adalah platform "Direktori & Booking Talent Ekonomi Kreatif" yang menghubungkan Event Organizer (EO) dengan berbagai talenta lokal (musisi indie, band, seniman visual, dan lain-lain). Platform ini memudahkan EO dalam menemukan *talent* yang cocok melalui sistem *matchmaking* pintar yang mempertimbangkan genre, *budget*, dan lokasi. 

Proyek ini merupakan **Backend Web Service API** yang dibangun menggunakan **Laravel 11**, yang dirancang dengan sistem autentikasi, manajemen profil talenta dan *event*, hingga pelaporan serta sistem transaksi pemesanan (Booking).

## Tim Pengembang (Backend Developers)

Pengembangan sistem backend API *CariTalent* dikerjakan oleh tiga orang pengembang dengan rincian tugas masing-masing:

### 1. Athila Ramdani Saputra (Ketua Tim / Core Engineer)
*  **Setup Arsitektur & Standar API**: Merancang struktur pondasi awal Laravel, *standardized JSON envelope*, error handling, dan dokumentasi interaktif menggunakan Swagger.
*  **Admin Module**: Memantau statistik platform, manajemen pengguna serta kewenangan moderasi *event* maupun verifikasi *talent*.
*  **Intelligent Matchmaking Engine**: Mengembangkan algoritma perekomendasi talenta pintar (skoring berbasis kecocokan *Genre*, *Budget*, dan Jarak Lokasi).

### 2. Muhammad Irgiansyah (Backend Engineer)
*  **Authentication & User Profile**: Pengelolaan autentikasi sistem dengan Laravel Sanctum (Register, Login, Role access) dan penanganan profil pengguna.
*  **Talent Master & Portfolio**: Mengelola referensi master seperti *Genre* dan struktur profil *Talent* beserta integrasi portfolio media multimedia.
*  **Application Flow**: Menangani siklus *apply* dari Talent menuju *Event* secara proaktif beserta manajemen status lamarannya.

### 3. Arfian Ghifari (Backend Engineer)
*  **Event Management**: Fitur pembuatan dan pengelolaan *Event* yang dibuat oleh EO hingga proses publikasi dan pembatalan acara.
*  **Invitation & Booking Process**: Pengembangan siklus *Invitation* langsung dari EO ke *Talent*, serta penyelesaian siklus akhir di tahapan *Booking* (*deal* harga) dan penyajian kontrak transaksi.
*  **Review & Notifications**: Membangun sistem ulasan lintas pengguna dan *trigger* layanan antrean notifikasi pintar.

## Fitur Utama (*API Specifications*)
- Role-based Authentication (Admin, Event Organizer, Talent)
- CRUD Profil Talenta lengkap dengan Media Upload
- CRUD *Event* dengan pencarian berbasis Lokasi dan Genre
- Alur *Matchmaking* Cerdas dengan skor relevansi spesifik EO
- Siklus terpadu *Application* (Lamaran) & *Invitation* (Undangan)
- Notifikasi terintegrasi setiap perubahan sesi transaksi
- Dukungan Swagger Interactive API Docs.

## Setup & Instalasi Lokal

1. Salin repositori ini.
2. Buka terminal di direktori utama, lalu jalankan instalasi *vendor*:
   ```bash
   composer install
   ```
3. Sesuaikan konfigurasi `.env`.
4. Lakukan Migrasi dan *Seeding*:
   ```bash
   php artisan migrate --seed
   ```
5. Akses dokumentasi interaktif API (*Swagger*):
   ```bash
   php artisan serve
   ```
   Buka URL: `http://localhost:8000/api/documentation`
