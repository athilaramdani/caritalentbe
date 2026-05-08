# Brainstorming Proyek Backend: CariTalent

## 1. Konteks Proyek
Berdasarkan file `ketentuanprojek.txt`, `keterangan.txt`, dan dokumen **`specAPIcaritalentv1.0.pdf`**, proyek "Sistem Direktori & Booking Talent Ekonomi Kreatif (CariTalent)" akan difokuskan penuh pada pengerjaan sisi **Backend (Web Service API)**.

Syarat dan kesepakatan utama berdasarkan API Spec:
- **Respon API Terstandarisasi (Envelope)**: Wajib menggunakan format JSON dengan `success`, `message`, dan `data`.
- **Autentikasi**: Menggunakan Bearer Token (disarankan *Laravel Sanctum*).
- **Error Codes**: Wajib menghandle format error code khusus.
- **API Documentation**: Menggunakan **Swagger**.

---

## 2. Pembagian Tugas (Berdasarkan Keadilan *Prompting* & *Testing*)
Mengingat pengerjaan kode akan lebih banyak mengandalkan **Prompting AI**, beban kerja sebenarnya terletak pada proses **Testing** masing-masing Endpoint API via Postman/Swagger dan **Debugging**. 

Oleh karena itu, total **~41 endpoint API** dari PDF dibagi rata menjadi masing-masing sekitar **13-14 endpoint per orang** dengan tetap menjaga kesatuan konteks (domain logika) agar *prompt/context window* di AI tetap nyambung.

### 👨‍💻 Athila Ramdani Saputra (Ketua BE) 👉 [Total: 14 Endpoints + Setup]
**Fokus Area: Base Setup, Autentikasi, dan Data Master Talent**
Konteks *prompting* Athila berpusat pada perancangan *User, Auth, dan Profil Talent*. AI-nya cukup difokuskan ke skema identitas.
- **Base Architecture:** Standardisasi format JSON response `envelope` dan Error Handling.
- **Kel. 1 - Authentication (4 API):** `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`.
- **Kel. 2 - User & Profile (2 API):** `PUT /users/profile`, `PUT /users/password`.
- **Kel. 3 - Talent Profile (7 API):** CRUD Talent `GET`, `POST`, `PUT`, `DELETE /talents` serta `POST`, `DELETE` media portofolio.
- **Kel. 11.2 - Genre (1 API):** `GET /genres`.
- **Swagger Integration:** Inisiasi anotasi awal.

### 👨‍💻 Muhammad Irgiansyah (BE) 👉 [Total: 13 Endpoints]
**Fokus Area: Event Management & Pertemuan Awal (Apply & Invite)**
Konteks *prompting* Irgi berpusat pada *Event* dan bagaimana jalurnya (Talent daftar ke Event ATAU EO ngundang Talent). AI-nya diajak fokus merangkai relasi `Event <-> Talent`.
- **Kel. 4 - Event (6 API):** CRUD Event tipe standar dan `GET /events/my`.
- **Kel. 5 - Application (4 API):** Talent *Apply* event (`POST /applications`, `GET /applications/my`, hapus), dan EO mengatur pelamar (`GET /events/{id}/applications`, `PUT status`).
- **Kel. 6 - Invitation (3 API):** EO nembak talent (`POST /invitations`), Talent lihat tawaran (`GET /invitations/my`), dan Talent setuju/tolak (`PUT respond`).

### 👨‍💻 Arfian Ghifari (BE) 👉 [Total: 14 Endpoints]
**Fokus Area: Finalisasi Transaksi (Booking), Fitur Ekstra (Notif, Matchmaking), dan Admin**
Konteks *prompting* Arfian berpusat pada alur akhir sebuah *Booking*, integrasi cerdas (algoritma pencocokan), serta kontrol Admin. Fokus AI-nya difokuskan pada manipulasi data yang sudah ada dan administrasi lintas tabel.
- **Kel. 7 - Booking (4 API):** Jika lamaran/undangan disetujui otomatis masuk kesini, `GET by ID`, `GET /bookings/my`, `PUT complete / cancel`.
- **Kel. 8 - Review (2 API):** Kasih ulasan kalau booking selesai (`POST /reviews`), dan melihat ulasan (`GET /talents/{id}/reviews`).
- **Kel. 9 - Notification (3 API):** `GET /notifications` dan fungsi tandai terbaca (`PUT /read`, `PUT /read-all`).
- **Kel. 10 - Admin (4 API):** `GET /admin/users`, `DELETE user`, `PUT /admin/talents/verify`, `PUT /admin/events/moderate`.
- **Kel. 11.1 - Matchmaking (1 API):** Algoritma pintar `GET /events/{id}/recommendations` yang ngitung bobot *Genre, Budget, dan Jarak*.

---

## 3. Strategi Prompting Paralel
Karena membagi per *domain/konteks*, kalau mau mulai ngerjain (nge-prompt), sarannya:
1. Tulis atau *Generate* dulu **Migration & Models** ke dalam satu teks yang disepakati (Bisa di- *handle* bareng via prompt).
2. Setelah struktur tabel *FIX Database*, masing-masing orang masukkan struktur DB tersebut ke masing-masing *chat/prompt box* AI miliknya.
3. Baru suruh AI nge- *generate* logika Controllers, Requests (Validasi), Resources (JSON Formatter). Lalu test *endpoint* bagian masing-masing di Postman secara mandiri!
