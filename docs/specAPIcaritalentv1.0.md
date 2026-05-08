# CariTalent API Specification V1.0

**Base URL:** `https://api.caritalent.id/api/v1`
**Format:** JSON
**Auth:** Bearer Token (Laravel Sanctum)
**Content-Type:** `application/json`

---

## Daftar Isi
1. Konvensi Umum
2. Authentication
3. User & Profile
4. Talent Profile
5. Event
6. Application
7. Invitation
8. Booking
9. Review
10. Notification
11. Admin
12. Matchmaking
13. Error Codes

---

## Konvensi Umum

### HTTP Status Codes
| Code | Keterangan |
|---|---|
| 200 | OK — request berhasil |
| 201 | Created — resource berhasil dibuat |
| 400 | Bad Request — input tidak valid |
| 401 | Unauthorized — token tidak ada / expired |
| 403 | Forbidden — tidak punya akses |
| 404 | Not Found — resource tidak ditemukan |
| 422 | Unprocessable Entity — validasi gagal |
| 500 | Internal Server Error |

### Response Envelope
Semua response menggunakan format:
```json
{
  "success": true,
  "message": "Deskripsi singkat",
  "data": { ... }
}
```

Response error:
```json
{
  "success": false,
  "message": "Pesan error",
  "errors": { ... }
}
```

### Role Akses
| Role | Keterangan |
|---|---|
| admin | Administrator platform |
| eo | Event Organizer |
| talent | Talent / Seniman |
| public | Tanpa autentikasi |

---

## 1. Authentication

### 1.1 Register
`POST /auth/register`
**Access:** Public
**Request Body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@email.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "role": "talent"
}
```
> `role` hanya boleh: `talent` atau `eo`. Role `admin` tidak bisa diregistrasi via endpoint ini.

**Response 201:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@email.com",
      "phone": "081234567890",
      "role": "talent",
      "created_at": "2026-03-08T10:00:00Z"
    },
    "token": "1|abc123tokenhere"
  }
}
```
**Response 422 (validasi gagal):**
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "email": ["Email sudah terdaftar."],
    "password": ["Password minimal 8 karakter."]
  }
}
```

### 1.2 Login
`POST /auth/login`
**Access:** Public
**Request Body:**
```json
{
  "email": "budi@email.com",
  "password": "password123"
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@email.com",
      "role": "talent"
    },
    "token": "1|abc123tokenhere"
  }
}
```
**Response 401:**
```json
{
  "success": false,
  "message": "Email atau password salah"
}
```

### 1.3 Logout
`POST /auth/logout`
**Access:** All authenticated users
**Headers:** `Authorization: Bearer {token}`
**Response 200:**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

### 1.4 Get Current User
`GET /auth/me`
**Access:** All authenticated users
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@email.com",
    "phone": "081234567890",
    "role": "talent",
    "created_at": "2026-03-08T10:00:00Z"
  }
}
```

---

## 2. User & Profile

### 2.1 Update Profile
`PUT /users/profile`
**Access:** All authenticated users
**Request Body:**
```json
{
  "name": "Budi Santoso Updated",
  "phone": "089876543210"
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Profil berhasil diperbarui",
  "data": {
    "id": 1,
    "name": "Budi Santoso Updated",
    "email": "budi@email.com",
    "phone": "089876543210",
    "role": "talent"
  }
}
```

### 2.2 Change Password
`PUT /users/password`
**Access:** All authenticated users
**Request Body:**
```json
{
  "current_password": "password123",
  "new_password": "newpassword456",
  "new_password_confirmation": "newpassword456"
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Password berhasil diubah"
}
```
**Response 422:**
```json
{
  "success": false,
  "message": "Password lama tidak sesuai"
}
```

---

## 3. Talent Profile

### 3.1 Get All Talents
`GET /talents`
**Access:** Public
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| genre | string | Filter berdasarkan genre |
| city | string | Filter berdasarkan kota |
| price_min | int | Filter harga minimum |
| price_max | int | Filter harga maksimum |
| verified | boolean | Filter hanya talent terverifikasi |
| search | string | Cari berdasarkan nama panggung |
| page | int | Halaman (default: 1) |
| per_page | int | Jumlah per halaman (default: 15) |

**Contoh Request:** `GET /talents?genre=Pop+Punk&city=Bandung&verified=true`
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "talents": [
      {
        "id": 1,
        "user_id": 1,
        "stage_name": "The Broken Strings",
        "genre": ["Pop Punk", "Alternative"],
        "price_min": 500000,
        "price_max": 2000000,
        "city": "Bandung",
        "bio": "Band pop punk asal Bandung dengan 5 tahun pengalaman.",
        "portfolio_link": "https://youtube.com/thebrokenstrings",
        "verified": true,
        "average_rating": 4.5,
        "total_reviews": 12,
        "media": [
          {
            "id": 1,
            "media_url": "https://storage.caritalent.id/media/1.jpg",
            "type": "image"
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 48,
      "last_page": 4
    }
  }
}
```

### 3.2 Get Talent by ID
`GET /talents/{id}`
**Access:** Public
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "user_id": 1,
    "stage_name": "The Broken Strings",
    "genre": ["Pop Punk", "Alternative"],
    "price_min": 500000,
    "price_max": 2000000,
    "city": "Bandung",
    "bio": "Band pop punk asal Bandung dengan 5 tahun pengalaman.",
    "portfolio_link": "https://youtube.com/thebrokenstrings",
    "verified": true,
    "average_rating": 4.5,
    "total_reviews": 12,
    "reviews": [
      {
        "id": 1,
        "organizer_name": "Kafe Kota",
        "rating": 5,
        "comment": "Sangat profesional dan memukau penonton.",
        "created_at": "2026-02-10T20:00:00Z"
      }
    ],
    "media": [
      {
        "id": 1,
        "media_url": "https://storage.caritalent.id/media/1.jpg",
        "type": "image"
      }
    ]
  }
}
```
**Response 404:**
```json
{
  "success": false,
  "message": "Talent tidak ditemukan"
}
```

### 3.3 Create Talent Profile
`POST /talents`
**Access:** talent
**Request Body:**
```json
{
  "stage_name": "The Broken Strings",
  "genre_ids": [1, 3],
  "price_min": 500000,
  "price_max": 2000000,
  "city": "Bandung",
  "bio": "Band pop punk asal Bandung dengan 5 tahun pengalaman.",
  "portfolio_link": "https://youtube.com/thebrokenstrings"
}
```
**Response 201:**
```json
{
  "success": true,
  "message": "Profil talent berhasil dibuat",
  "data": {
    "id": 1,
    "stage_name": "The Broken Strings",
    "genre": ["Pop Punk", "Heavy Metal"],
    "price_min": 500000,
    "price_max": 2000000,
    "city": "Bandung",
    "verified": false
  }
}
```

### 3.4 Update Talent Profile
`PUT /talents/{id}`
**Access:** talent (owner), admin
**Request Body:**
```json
{
  "stage_name": "The Broken Strings II",
  "price_min": 750000,
  "price_max": 2500000,
  "bio": "Bio terbaru.",
  "genre_ids": [1, 2, 4]
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Profil talent berhasil diperbarui",
  "data": { ... }
}
```

### 3.5 Delete Talent Profile
`DELETE /talents/{id}`
**Access:** admin
**Response 200:**
```json
{
  "success": true,
  "message": "Profil talent berhasil dihapus"
}
```

### 3.6 Upload Portfolio Media
`POST /talents/{id}/media`
**Access:** talent (owner)
**Content-Type:** `multipart/form-data`
**Request Body:**
| Field | Type | Keterangan |
|---|---|---|
| file | file | File gambar/video/audio |
| type | string | image / video / audio |

**Response 201:**
```json
{
  "success": true,
  "message": "Media berhasil diunggah",
  "data": {
    "id": 5,
    "media_url": "https://storage.caritalent.id/media/5.mp4",
    "type": "video"
  }
}
```

### 3.7 Delete Portfolio Media
`DELETE /talents/{talent_id}/media/{media_id}`
**Access:** talent (owner), admin
**Response 200:**
```json
{
  "success": true,
  "message": "Media berhasil dihapus"
}
```

---

## 4. Event

### 4.1 Get All Events
`GET /events`
**Access:** Public
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| status | string | draft / open / closed / completed / cancelled |
| genre | string | Filter genre yang dibutuhkan |
| city | string | Filter kota venue |
| budget_min | int | Filter budget minimum |
| budget_max | int | Filter budget maksimum |
| date_from | date | Filter tanggal mulai |
| date_to | date | Filter tanggal akhir |
| search | string | Cari berdasarkan judul event |
| page | int | Halaman |

**Contoh Request:** `GET /events?status=open&genre=Pop+Punk&city=Bandung`
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "events": [
      {
        "id": 1,
        "organizer_id": 2,
        "organizer_name": "Kafe Kota",
        "title": "Punk Night Vol. 3",
        "description": "Malam punk rock terbaik di Bandung.",
        "genre_needed": ["Pop Punk", "Hardcore"],
        "budget": 3000000,
        "event_date": "2026-04-15",
        "venue_name": "Kafe Kota Bandung",
        "latitude": -6.9175,
        "longitude": 107.6191,
        "city": "Bandung",
        "status": "open",
        "created_at": "2026-03-01T09:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 23,
      "last_page": 2
    }
  }
}
```

### 4.2 Get Event by ID
`GET /events/{id}`
**Access:** Public
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "organizer_id": 2,
    "organizer_name": "Kafe Kota",
    "title": "Punk Night Vol. 3",
    "description": "Malam punk rock terbaik di Bandung.",
    "genre_needed": ["Pop Punk", "Hardcore"],
    "budget": 3000000,
    "event_date": "2026-04-15",
    "venue_name": "Kafe Kota Bandung",
    "latitude": -6.9175,
    "longitude": 107.6191,
    "city": "Bandung",
    "status": "open",
    "total_applicants": 5,
    "created_at": "2026-03-01T09:00:00Z"
  }
}
```

### 4.3 Create Event
`POST /events`
**Access:** eo
**Request Body:**
```json
{
  "title": "Punk Night Vol. 3",
  "description": "Malam punk rock terbaik di Bandung.",
  "genre_ids": [1, 5],
  "budget": 3000000,
  "event_date": "2026-04-15",
  "venue_name": "Kafe Kota Bandung",
  "latitude": -6.9175,
  "longitude": 107.6191,
  "city": "Bandung",
  "status": "draft"
}
```
**Response 201:**
```json
{
  "success": true,
  "message": "Event berhasil dibuat",
  "data": {
    "id": 1,
    "title": "Punk Night Vol. 3",
    "status": "draft",
    "created_at": "2026-03-08T10:00:00Z"
  }
}
```

### 4.4 Update Event
`PUT /events/{id}`
**Access:** eo (owner)
**Request Body:**
```json
{
  "title": "Punk Night Vol. 3 - Special Edition",
  "budget": 4000000,
  "status": "open"
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Event berhasil diperbarui",
  "data": { ... }
}
```

### 4.5 Cancel Event
`DELETE /events/{id}`
**Access:** eo (owner), admin
> Event tidak dihapus permanen, status berubah menjadi `cancelled`.

**Response 200:**
```json
{
  "success": true,
  "message": "Event berhasil dibatalkan"
}
```

### 4.6 Get My Events (EO)
`GET /events/my`
**Access:** eo
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "events": [ ... ],
    "pagination": { ... }
  }
}
```

---

## 5. Application

### 5.1 Apply ke Event (Talent)
`POST /applications`
**Access:** talent
**Request Body:**
```json
{
  "event_id": 1,
  "message": "Kami band pop punk dari Bandung dengan pengalaman 5 tahun, siap tampil di acara ini.",
  "proposed_price": 1500000
}
```
> Sistem otomatis set `source = apply` dan `status = pending`. UNIQUE constraint mencegah talent apply dua kali ke event yang sama.

**Response 201:**
```json
{
  "success": true,
  "message": "Lamaran berhasil dikirim",
  "data": {
    "id": 10,
    "event_id": 1,
    "talent_id": 3,
    "source": "apply",
    "message": "Kami band pop punk dari Bandung...",
    "proposed_price": 1500000,
    "status": "pending",
    "created_at": "2026-03-08T11:00:00Z"
  }
}
```
**Response 422 (sudah pernah apply):**
```json
{
  "success": false,
  "message": "Kamu sudah pernah melamar ke event ini"
}
```

### 5.2 Get Applications by Event (EO melihat pelamar)
`GET /events/{event_id}/applications`
**Access:** eo (owner event)
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| status | string | pending / accepted / rejected |
| source | string | apply / invitation |

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "applications": [
      {
        "id": 10,
        "talent": {
          "id": 3,
          "stage_name": "The Broken Strings",
          "genre": ["Pop Punk"],
          "city": "Bandung",
          "verified": true,
          "average_rating": 4.5
        },
        "source": "apply",
        "message": "Kami band pop punk dari Bandung...",
        "proposed_price": 1500000,
        "status": "pending",
        "created_at": "2026-03-08T11:00:00Z"
      }
    ]
  }
}
```

### 5.3 Get My Applications (Talent melihat lamarannya)
`GET /applications/my`
**Access:** talent
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "applications": [
      {
        "id": 10,
        "event": {
          "id": 1,
          "title": "Punk Night Vol. 3",
          "event_date": "2026-04-15",
          "venue_name": "Kafe Kota Bandung",
          "city": "Bandung",
          "latitude": -6.9175,
          "longitude": 107.6191
        },
        "source": "apply",
        "proposed_price": 1500000,
        "status": "pending",
        "created_at": "2026-03-08T11:00:00Z"
      }
    ]
  }
}
```

### 5.4 Accept / Reject Application (EO)
`PUT /applications/{id}/status`
**Access:** eo (owner event)
**Request Body:**
```json
{
  "status": "accepted",
  "agreed_price": 1500000
}
```
> Jika status = `accepted`, sistem otomatis membuat record Booking baru. `agreed_price` wajib diisi jika accepted.

**Response 200:**
```json
{
  "success": true,
  "message": "Lamaran diterima dan booking telah dibuat",
  "data": {
    "application": {
      "id": 10,
      "status": "accepted"
    },
    "booking": {
      "id": 5,
      "application_id": 10,
      "agreed_price": 1500000,
      "status": "confirmed"
    }
  }
}
```

### 5.5 Cancel Application (Talent)
`DELETE /applications/{id}`
**Access:** talent (owner)
> Hanya bisa dilakukan jika status masih `pending`.

**Response 200:**
```json
{
  "success": true,
  "message": "Lamaran berhasil dibatalkan"
}
```

---

## 6. Invitation

### 6.1 Send Invitation (EO mengundang Talent)
`POST /invitations`
**Access:** eo
**Request Body:**
```json
{
  "event_id": 1,
  "talent_id": 3,
  "offered_price": 2000000
}
```
> Sistem otomatis membuat record di tabel applications dengan source = `invitation` dan status = `pending`.

**Response 201:**
```json
{
  "success": true,
  "message": "Undangan berhasil dikirim",
  "data": {
    "id": 15,
    "event_id": 1,
    "talent_id": 3,
    "offered_price": 2000000,
    "status": "pending",
    "created_at": "2026-03-08T12:00:00Z"
  }
}
```
**Response 422 (sudah apply/invitation aktif):**
```json
{
  "success": false,
  "message": "Talent ini sudah memiliki lamaran aktif untuk event tersebut"
}
```

### 6.2 Get My Invitations (Talent melihat undangan masuk)
`GET /invitations/my`
**Access:** talent
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "invitations": [
      {
        "id": 15,
        "event": {
          "id": 1,
          "title": "Punk Night Vol. 3",
          "event_date": "2026-04-15",
          "venue_name": "Kafe Kota Bandung",
          "city": "Bandung",
          "budget": 3000000,
          "latitude": -6.9175,
          "longitude": 107.6191
        },
        "organizer_name": "Kafe Kota",
        "offered_price": 2000000,
        "status": "pending",
        "created_at": "2026-03-08T12:00:00Z"
      }
    ]
  }
}
```

### 6.3 Accept / Reject Invitation (Talent)
`PUT /invitations/{id}/respond`
**Access:** talent (penerima undangan)
**Request Body:**
```json
{
  "status": "accepted"
}
```
> status hanya boleh: `accepted` atau `rejected`. Jika `accepted`, sistem otomatis membuat record Booking.

**Response 200 (accepted):**
```json
{
  "success": true,
  "message": "Undangan diterima dan booking telah dibuat",
  "data": {
    "invitation": {
      "id": 15,
      "status": "accepted"
    },
    "booking": {
      "id": 6,
      "application_id": 15,
      "agreed_price": 2000000,
      "status": "confirmed"
    }
  }
}
```
**Response 200 (rejected):**
```json
{
  "success": true,
  "message": "Undangan berhasil ditolak",
  "data": {
    "invitation": {
      "id": 15,
      "status": "rejected"
    }
  }
}
```

---

## 7. Booking

### 7.1 Get Booking by ID
`GET /bookings/{id}`
**Access:** eo (owner event), talent (yang di-booking), admin
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 5,
    "application_id": 10,
    "source": "apply",
    "event": {
      "id": 1,
      "title": "Punk Night Vol. 3",
      "event_date": "2026-04-15",
      "venue_name": "Kafe Kota Bandung",
      "latitude": -6.9175,
      "longitude": 107.6191
    },
    "talent": {
      "id": 3,
      "stage_name": "The Broken Strings"
    },
    "agreed_price": 1500000,
    "status": "confirmed",
    "created_at": "2026-03-08T11:30:00Z"
  }
}
```

### 7.2 Get My Bookings
`GET /bookings/my`
**Access:** eo, talent
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| status | string | confirmed / completed |

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "bookings": [ ... ]
  }
}
```

### 7.3 Complete Booking (EO)
`PUT /bookings/{id}/complete`
**Access:** eo (owner event)
> Menandai booking selesai setelah event berlangsung. Status berubah dari `confirmed` → `completed`. Setelah ini EO dapat memberikan review.

**Response 200:**
```json
{
  "success": true,
  "message": "Booking telah ditandai selesai",
  "data": {
    "id": 5,
    "status": "completed"
  }
}
```

### 7.4 Cancel Booking
`PUT /bookings/{id}/cancel`
**Access:** eo (owner event), admin
> Hanya bisa jika status masih `confirmed`.

**Response 200:**
```json
{
  "success": true,
  "message": "Booking berhasil dibatalkan"
}
```

---

## 8. Review

### 8.1 Create Review (EO memberi review ke Talent)
`POST /reviews`
**Access:** eo
> Hanya bisa dilakukan setelah booking berstatus `completed`. Satu booking hanya bisa dibuatkan satu review.

**Request Body:**
```json
{
  "booking_id": 5,
  "rating": 5,
  "comment": "The Broken Strings tampil luar biasa, penonton sangat antusias. Sangat profesional dan tepat waktu."
}
```
**Response 201:**
```json
{
  "success": true,
  "message": "Review berhasil dikirim",
  "data": {
    "id": 8,
    "booking_id": 5,
    "talent": {
      "id": 3,
      "stage_name": "The Broken Strings"
    },
    "event": {
      "id": 1,
      "title": "Punk Night Vol. 3"
    },
    "rating": 5,
    "comment": "The Broken Strings tampil luar biasa...",
    "created_at": "2026-04-16T10:00:00Z"
  }
}
```
**Response 422 (belum completed / sudah direview):** 
```json
{
  "success": false,
  "message": "Review hanya bisa diberikan setelah event selesai / Kamu sudah memberikan review untuk booking ini"
}
```

### 8.2 Get Reviews by Talent
`GET /talents/{talent_id}/reviews`
**Access:** Public
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "talent_id": 3,
    "stage_name": "The Broken Strings",
    "average_rating": 4.5,
    "total_reviews": 12,
    "reviews": [
      {
        "id": 8,
        "organizer_name": "Kafe Kota",
        "event_title": "Punk Night Vol. 3",
        "rating": 5,
        "comment": "The Broken Strings tampil luar biasa...",
        "created_at": "2026-04-16T10:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

## 9. Notification

### 9.1 Get My Notifications
`GET /notifications`
**Access:** All authenticated users
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| is_read | boolean | Filter notifikasi yang sudah/belum dibaca |
| type | string | application / booking / invitation / review |

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "notifications": [
      {
        "id": 20,
        "title": "Undangan Manggung Baru!",
        "body": "Kafe Kota mengundang kamu untuk tampil di Punk Night Vol. 3",
        "type": "invitation",
        "reference_id": 15,
        "is_read": false,
        "created_at": "2026-03-08T12:00:00Z"
      }
    ],
    "unread_count": 3,
    "pagination": { ... }
  }
}
```

### 9.2 Mark Notification as Read
`PUT /notifications/{id}/read`
**Access:** All authenticated users (owner notifikasi)
**Response 200:**
```json
{
  "success": true,
  "message": "Notifikasi ditandai sudah dibaca"
}
```

### 9.3 Mark All Notifications as Read
`PUT /notifications/read-all`
**Access:** All authenticated users
**Response 200:**
```json
{
  "success": true,
  "message": "Semua notifikasi ditandai sudah dibaca"
}
```

---

## 10. Admin

### 10.1 Get All Users
`GET /admin/users`
**Access:** admin
**Query Parameters:**
| Parameter | Type | Keterangan |
|---|---|---|
| role | string | eo / talent |
| search | string | Cari berdasarkan nama / email |
| page | int | Halaman |

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "users": [
      {
        "id": 1,
        "name": "Budi Santoso",
        "email": "budi@email.com",
        "role": "talent",
        "phone": "081234567890",
        "created_at": "2026-03-08T10:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

### 10.2 Delete User
`DELETE /admin/users/{id}`
**Access:** admin
**Response 200:**
```json
{
  "success": true,
  "message": "Akun pengguna berhasil dihapus"
}
```

### 10.3 Verify Talent
`PUT /admin/talents/{id}/verify`
**Access:** admin
**Request Body:**
```json
{
  "verified": true
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Talent berhasil diverifikasi",
  "data": {
    "id": 1,
    "stage_name": "The Broken Strings",
    "verified": true
  }
}
```

### 10.4 Get Dashboard Statistics
`GET /admin/dashboard`
**Access:** admin
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "total_users": 245,
    "total_talents": 180,
    "total_eo": 65,
    "total_events": 120,
    "active_events": 34,
    "total_bookings": 89,
    "completed_bookings": 56,
    "total_reviews": 48
  }
}
```

### 10.5 Moderate Event
`PUT /admin/events/{id}/moderate`
**Access:** admin
**Request Body:**
```json
{
  "status": "cancelled",
  "reason": "Konten event melanggar ketentuan platform."
}
```
**Response 200:**
```json
{
  "success": true,
  "message": "Event berhasil dimoderasi"
}
```

---

## 11. Matchmaking

### 11.1 Get Talent Recommendations untuk Event
`GET /events/{event_id}/recommendations`
**Access:** eo (owner event)
> Menjalankan rule-based matchmaking engine dan mengembalikan Top 5 talent yang paling cocok.

**Scoring Rules:**
| Rule | Kondisi | Skor |
|---|---|---|
| Genre Match | event.genre ada di talent.genre | +50 |
| Budget Match | event.budget >= talent.price_min | +30 |
| Location Match | Jarak venue < 20km dari kota talent | +20 |

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "event_id": 1,
    "event_title": "Punk Night Vol. 3",
    "recommendations": [
      {
        "rank": 1,
        "score": 100,
        "score_breakdown": {
          "genre_score": 50,
          "budget_score": 30,
          "location_score": 20
        },
        "talent": {
          "id": 3,
          "stage_name": "The Broken Strings",
          "genre": ["Pop Punk", "Alternative"],
          "price_min": 500000,
          "price_max": 2000000,
          "city": "Bandung",
          "verified": true,
          "average_rating": 4.5
        }
      },
      {
        "rank": 2,
        "score": 80,
        "score_breakdown": {
          "genre_score": 50,
          "budget_score": 30,
          "location_score": 0
        },
        "talent": {
          "id": 7,
          "stage_name": "Riot Kids",
          "genre": ["Pop Punk"],
          "price_min": 800000,
          "price_max": 2500000,
          "city": "Jakarta",
          "verified": true,
          "average_rating": 4.2
        }
      }
    ]
  }
}
```

### 11.2 Get Genre List
`GET /genres`
**Access:** Public
**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "genres": [
      { "id": 1, "name": "Pop Punk" },
      { "id": 2, "name": "Heavy Metal" },
      { "id": 3, "name": "DJ" },
      { "id": 4, "name": "Solo Singer" },
      { "id": 5, "name": "Hardcore" },
      { "id": 6, "name": "Jazz" },
      { "id": 7, "name": "Seniman Visual" },
      { "id": 8, "name": "Street Performer" }
    ]
  }
}
```

---

## 13. Error Codes

| Code | Message | Keterangan |
|---|---|---|
| `AUTH_001` | Token tidak valid | Token expired atau tidak ditemukan |
| `AUTH_002` | Akses ditolak | Role tidak memiliki izin |
| `VALID_001` | Validasi gagal | Input tidak memenuhi aturan |
| `APP_001` | Sudah pernah melamar | Duplicate application |
| `APP_002` | Event tidak tersedia | Event bukan status open |
| `BOOK_001` | Booking tidak ditemukan | ID booking tidak valid |
| `BOOK_002` | Status tidak sesuai | Aksi tidak sesuai status booking |
| `REV_001` | Review sudah ada | Booking sudah pernah direviw |
| `REV_002` | Event belum selesai | Booking belum completed |
