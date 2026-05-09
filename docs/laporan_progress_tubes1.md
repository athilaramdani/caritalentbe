# Laporan Progress Tubes 1 — Web Backend
## Proyek: CariTalent — Sistem Direktori & Booking Talent Ekonomi Kreatif

> **Catatan:** Dokumen ini merupakan laporan progress bagian **Backend**. Laporan final akan digabung dengan laporan Frontend, di mana Frontend diletakkan di bagian atas dan Backend di bagian bawah.

---

## Ringkasan Progress

### Frontend *(Referensi — dari Repository Terpisah)*

| Nama | Bagian yang Dikerjakan | Status Saat Ini | Next To-Do |
|------|------------------------|-----------------|------------|
| *(Anggota FE 1)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* |
| *(Anggota FE 2)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* |
| *(Anggota FE 3)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* | *(Diisi oleh tim Frontend)* |

---

### Backend

Pembagian tugas mengacu pada dokumen `panduanpengerjaan.md`. Berikut adalah ringkasan progress yang dicocokkan dengan implementasi aktual di repository (`athilaramdani/caritalentbe`):

| Nama | Bagian yang Dikerjakan | Status Saat Ini | Next To-Do |
|------|------------------------|-----------------|------------|
| **Athila Ramdani Saputra** | • Base Architecture & JSON Response Envelope (`ApiResponse` trait) <br> • Autentikasi: `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` <br> • User & Profile: `PUT /users/profile`, `PUT /users/password` <br> • Talent Profile: `GET /talents`, `GET /talents/{id}`, `POST /talents`, `PUT /talents/{id}`, `DELETE /talents/{id}`, `POST /talents/{id}/media`, `DELETE /talents/{talent_id}/media/{media_id}` <br> • Genre: `GET /genres` <br> • Swagger: Inisiasi anotasi OpenAPI pada seluruh controller bagiannya | ✅ **Selesai** — Semua 14 endpoint terimplementasi + Swagger teranotasi penuh | Menangani upload file aktual ke storage (saat ini URL di-mock); integrasi `GET /genres` ke route |
| **Muhammad Irgiansyah** | • Event Management: `GET /events`, `GET /events/{id}`, `POST /events`, `PUT /events/{id}`, `DELETE /events/{id}`, `GET /events/my` <br> • Application: `POST /applications`, `GET /applications/my`, `DELETE /applications/{id}`, `GET /events/{event_id}/applications`, `PUT /applications/{id}/status` <br> • Invitation: `POST /invitations`, `GET /invitations/my`, `PUT /invitations/{id}/respond` | ✅ **Selesai** — Semua 13 endpoint terimplementasi; booking otomatis terbuat saat application diterima; Request classes terpisah tersedia (`StoreEventRequest`, `UpdateEventRequest`, dll.) | Menambahkan filter `genre` dan `date_from`/`date_to` pada `GET /events` (saat ini filter city, status, budget, search sudah ada); mendaftarkan route Booking, Review, Notification, dan Admin ke `api.php` |
| **Arfian Ghifari** | • Booking: `GET /bookings/{id}`, `GET /bookings/my`, `PUT /bookings/{id}/complete`, `PUT /bookings/{id}/cancel` <br> • Review: `POST /reviews`, `GET /talents/{id}/reviews` (dengan update `average_rating` otomatis) <br> • Notification: `GET /notifications`, `PUT /notifications/{id}/read`, `PUT /notifications/read-all` <br> • Admin: `GET /admin/users`, `DELETE /admin/users/{id}`, `PUT /admin/talents/{id}/verify`, `PUT /admin/events/{id}/moderate` <br> • Matchmaking: `GET /events/{id}/recommendations` (algoritma scoring berdasarkan Genre +50, Budget +30, Lokasi +20; top 5 hasil) | ✅ **Selesai** — Semua 14 endpoint terimplementasi dengan Swagger teranotasi | Mendaftarkan route-route miliknya ke `routes/api.php` (saat ini belum terdaftar); validasi tambahan pada Matchmaking untuk edge case |

---

## Catatan Teknis Keseluruhan

| Komponen | Status |
|----------|--------|
| Database Migrations (13 tabel) | ✅ Selesai |
| Models & Relasi Eloquent | ✅ Selesai (User, Talent, Event, Application, Booking, Review, Notification, Genre, Media) |
| Laravel Sanctum (Bearer Token Auth) | ✅ Selesai |
| Role Middleware (`role:eo`, `role:talent`, `role:admin`) | ✅ Selesai |
| JSON Response Envelope Standar | ✅ Selesai |
| Swagger / OpenAPI Annotations | ✅ Teranotasi di semua controller |
| Pendaftaran Route di `api.php` | ⚠️ Parsial — hanya Auth, Event, Application, Invitation yang terdaftar; Booking, Review, Notification, Admin, Matchmaking belum didaftarkan |
| File Upload Aktual (Storage) | ⚠️ Mock URL — belum menggunakan `Storage::put` |

---

## Screenshot Progress

> **Petunjuk Pengisian:** Setiap anggota menambahkan screenshot di bagian masing-masing di bawah ini. Format file yang disarankan: PNG atau JPEG. Beri nama file yang deskriptif sebelum menyisipkan ke dalam dokumen.

---

### Frontend *(Referensi — dari Repository Terpisah)*

Berikut daftar halaman yang perlu disertakan screenshot-nya oleh tim Frontend:

- Landing Page
- Halaman Login
- Halaman Register
- Dashboard Talent
- Dashboard Admin
- Dashboard Event Organizer

*(Screenshot diisi oleh tim Frontend)*

---

### Backend — Athila Ramdani Saputra
**Fokus Area: Base Setup, Autentikasi, User & Profile, Talent Profile, Genre**

#### 1. Endpoint Autentikasi (Postman / Swagger)

> Sertakan screenshot Postman atau Swagger UI yang menampilkan:
> - Request & Response `POST /api/v1/auth/register`
> - Request & Response `POST /api/v1/auth/login`
> - Request & Response `POST /api/v1/auth/logout`
> - Request & Response `GET /api/v1/auth/me`

*(Screenshot diisi oleh Athila)*

#### 2. Endpoint User & Profile

> Sertakan screenshot:
> - Request & Response `PUT /api/v1/users/profile`
> - Request & Response `PUT /api/v1/users/password`

*(Screenshot diisi oleh Athila)*

#### 3. Endpoint Talent Profile & Media Portfolio

> Sertakan screenshot:
> - `GET /api/v1/talents` (dengan query parameter filter)
> - `GET /api/v1/talents/{id}`
> - `POST /api/v1/talents` (membuat profil talent baru)
> - `PUT /api/v1/talents/{id}` (update profil)
> - `DELETE /api/v1/talents/{id}` (hapus profil — akses admin)
> - `POST /api/v1/talents/{id}/media` (upload media)
> - `DELETE /api/v1/talents/{talent_id}/media/{media_id}`

*(Screenshot diisi oleh Athila)*

#### 4. Struktur Database — Tabel Terkait

> Sertakan screenshot relasi tabel: `users`, `talents`, `genres`, `genre_talent`, `media`
> (Bisa dari DB Browser for SQLite, TablePlus, atau hasil `php artisan migrate --pretend`)

*(Screenshot diisi oleh Athila)*

#### 5. Swagger UI — Kelompok Endpoint Auth & Talent

> Sertakan screenshot tampilan Swagger UI yang memperlihatkan endpoint-endpoint Authentication, User & Profile, dan Talent Profile.

*(Screenshot diisi oleh Athila)*

---

### Backend — Muhammad Irgiansyah
**Fokus Area: Event Management, Application, Invitation**

#### 1. Endpoint Event Management (Postman / Swagger)

> Sertakan screenshot:
> - `GET /api/v1/events` (daftar event dengan filter)
> - `GET /api/v1/events/{id}` (detail event)
> - `POST /api/v1/events` (buat event — akses EO)
> - `PUT /api/v1/events/{id}` (update event)
> - `DELETE /api/v1/events/{id}` (batalkan event)
> - `GET /api/v1/events/my` (event milik EO yang login)

*(Screenshot diisi oleh Irgi)*

#### 2. Endpoint Application (Talent Apply & EO Manage)

> Sertakan screenshot:
> - `POST /api/v1/applications` (talent melamar event)
> - `GET /api/v1/applications/my` (talent melihat lamarannya)
> - `DELETE /api/v1/applications/{id}` (talent membatalkan lamaran)
> - `GET /api/v1/events/{event_id}/applications` (EO melihat pelamar)
> - `PUT /api/v1/applications/{id}/status` (EO terima/tolak lamaran + booking otomatis terbuat)

*(Screenshot diisi oleh Irgi)*

#### 3. Endpoint Invitation (EO Undang Talent)

> Sertakan screenshot:
> - `POST /api/v1/invitations` (EO mengirim undangan ke talent)
> - `GET /api/v1/invitations/my` (talent melihat undangan masuk)
> - `PUT /api/v1/invitations/{id}/respond` (talent setuju/tolak undangan)

*(Screenshot diisi oleh Irgi)*

#### 4. Struktur Database — Tabel Terkait

> Sertakan screenshot relasi tabel: `events`, `applications`, `invitations` (beserta relasi ke `users` dan `bookings`)

*(Screenshot diisi oleh Irgi)*

#### 5. Fitur Utama: Booking Otomatis

> Sertakan screenshot yang memperlihatkan bahwa ketika EO menerima sebuah lamaran (`PUT /applications/{id}/status` dengan `status: accepted`), sistem secara otomatis membuat record baru di tabel `bookings`.

*(Screenshot diisi oleh Irgi)*

---

### Backend — Arfian Ghifari
**Fokus Area: Booking, Review, Notification, Admin, Matchmaking**

#### 1. Endpoint Booking (Postman / Swagger)

> Sertakan screenshot:
> - `GET /api/v1/bookings/{id}` (detail booking)
> - `GET /api/v1/bookings/my` (semua booking milik user)
> - `PUT /api/v1/bookings/{id}/complete` (EO tandai booking selesai)
> - `PUT /api/v1/bookings/{id}/cancel` (batalkan booking)

*(Screenshot diisi oleh Arfian)*

#### 2. Endpoint Review

> Sertakan screenshot:
> - `POST /api/v1/reviews` (EO memberikan ulasan setelah event selesai)
> - `GET /api/v1/talents/{id}/reviews` (melihat ulasan untuk talent tertentu)

*(Screenshot diisi oleh Arfian)*

#### 3. Endpoint Notification

> Sertakan screenshot:
> - `GET /api/v1/notifications` (daftar notifikasi user)
> - `PUT /api/v1/notifications/{id}/read` (tandai satu notifikasi terbaca)
> - `PUT /api/v1/notifications/read-all` (tandai semua notifikasi terbaca)

*(Screenshot diisi oleh Arfian)*

#### 4. Endpoint Admin

> Sertakan screenshot:
> - `GET /api/v1/admin/users` (admin melihat semua user)
> - `DELETE /api/v1/admin/users/{id}` (admin hapus user)
> - `PUT /api/v1/admin/talents/{id}/verify` (admin verifikasi talent)
> - `PUT /api/v1/admin/events/{id}/moderate` (admin moderasi event)

*(Screenshot diisi oleh Arfian)*

#### 5. Fitur Utama: Algoritma Matchmaking

> Sertakan screenshot response JSON dari `GET /api/v1/events/{id}/recommendations` yang memperlihatkan:
> - List rekomendasi talent yang diurutkan berdasarkan skor
> - Breakdown skor: `genre_score`, `budget_score`, `location_score`
> - Ranking top 5 talent yang paling cocok dengan event

*(Screenshot diisi oleh Arfian)*

#### 6. Struktur Database — Tabel Terkait

> Sertakan screenshot relasi tabel: `bookings`, `reviews`, `notifications`

*(Screenshot diisi oleh Arfian)*

---

## Catatan

- Laporan frontend dan backend akan digabung dalam satu dokumen akhir untuk pengumpulan.
- Frontend diletakkan di bagian atas, backend di bagian bawah.
- Backend hanya berfokus pada repository ini (`athilaramdani/caritalentbe`).
- Format pengumpulan akhir: `.zip` / `.rar`
- Gunakan bahasa formal dan ringkas dalam seluruh bagian laporan.
- Pastikan setiap screenshot memperlihatkan response JSON yang valid sesuai standar envelope yang disepakati (`success`, `message`, `data`).
