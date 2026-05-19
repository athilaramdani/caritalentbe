# Spec API CariTalent v2.0

Dokumen ini dibuat berdasarkan backend Laravel terbaru di repository ini, terutama `routes/api.php`, controller, request validation, model, dan migration. Spec lama tidak dijadikan acuan.

## Ringkasan

- Base URL lokal: `http://localhost:8000/api/v1`
- Format data utama: JSON
- Auth: Laravel Sanctum token
- Header auth:

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

Role yang digunakan:

- `talent`: membuat profil talent, apply event, menerima undangan, melihat booking/review sendiri.
- `eo`: membuat event, mengundang talent, menerima/menolak application, menyelesaikan booking, memberi review.
- `admin`: dashboard admin, moderasi user/talent/event, beberapa akses booking.

## Format Response Umum

Mayoritas endpoint memakai bentuk:

```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

Error umum:

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "field": ["Pesan error"]
  }
}
```

Status umum:

- `200`: berhasil
- `201`: resource berhasil dibuat
- `400`: request tidak valid secara proses
- `401`: belum login/token tidak valid
- `403`: role tidak punya akses
- `404`: data tidak ditemukan
- `422`: validasi gagal atau konflik bisnis
- `500`: kesalahan sistem

Catatan penting backend saat ini: beberapa endpoint legacy memanggil helper `successResponse($data, $message)` dengan urutan argumen terbalik. Endpoint tersebut tetap dicatat di bagian "Catatan Response Aktual".

## Auth

### Register

`POST /auth/register`

Akses: public

Body:

```json
{
  "name": "Budi Santoso",
  "email": "budi@email.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "role": "talent",
  "stage_name": "The Broken Strings"
}
```

Validasi:

- `name`: required, string, max 255
- `email`: required, email, unique
- `password`: required, min 6, confirmed
- `phone`: required, string, max 20
- `role`: required, enum `talent|eo`
- `stage_name`: opsional, hanya boleh untuk role `talent`

Jika role `talent`, backend otomatis membuat profil talent awal dengan:

- `id = user.id`
- `user_id = user.id`
- `stage_name = stage_name` atau nama user
- `city = ""` jika tidak dikirim
- `verified = false`

Response `201`:

```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {},
    "token": "plain_text_token",
    "talent": {}
  }
}
```

### Login

`POST /auth/login`

Akses: public

Body:

```json
{
  "email": "budi@email.com",
  "password": "password123"
}
```

Response `200`:

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {},
    "token": "plain_text_token"
  }
}
```

Error login salah: `401`

```json
{
  "success": false,
  "message": "Email atau password salah"
}
```

### Logout

`POST /auth/logout`

Akses: login

Response aktual `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": "Logout berhasil"
}
```

### Me

`GET /auth/me`

Akses: login

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@email.com",
    "phone": "081234567890",
    "role": "talent"
  }
}
```

## User & Profile

### Update User Profile

`PUT /users/profile`

Akses: login

Body:

```json
{
  "name": "Nama Baru",
  "phone": "081111111111"
}
```

Validasi:

- `name`: optional, required jika dikirim, string, max 255
- `phone`: optional, required jika dikirim, string, max 20

Response `200`:

```json
{
  "success": true,
  "message": "Profil berhasil diperbarui",
  "data": {}
}
```

### Update Password

`PUT /users/password`

Akses: login

Body:

```json
{
  "current_password": "password123",
  "new_password": "password456",
  "new_password_confirmation": "password456"
}
```

Validasi:

- `current_password`: required, string
- `new_password`: required, string, min 6, confirmed

Response `200`:

```json
{
  "success": true,
  "message": "Password berhasil diubah"
}
```

## Genres

### List Genres

`GET /genres`

Akses: public

Response aktual `200`:

```json
{
  "success": true,
  "message": {
    "genres": []
  },
  "data": "OK"
}
```

Catatan: secara intent endpoint ini mengembalikan daftar genre, tetapi response aktual backend saat ini masih tertukar antara `message` dan `data`.

## Talents

### List Talents

`GET /talents`

Akses: public

Query:

- `city`: filter kota exact match
- `verified`: `true|false|1|0`
- `search`: cari `stage_name`
- `genre`: cari nama genre dengan LIKE
- `price_min`: filter `price_min >= nilai`
- `price_max`: filter `price_max <= nilai`
- `per_page`: default 15

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "talents": [
      {
        "id": 4,
        "user_id": 4,
        "stage_name": "The Broken Strings",
        "genre": ["Pop Punk", "Alternative Rock"],
        "price_min": 500000,
        "price_max": 2000000,
        "city": "Bandung",
        "bio": "Band pop punk asal Bandung dengan 5 tahun pengalaman.",
        "portfolio_link": "https://youtube.com/thebrokenstrings",
        "verified": true,
        "average_rating": 4.5,
        "total_reviews": 12
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 1,
      "last_page": 1
    }
  }
}
```

### Detail Talent

`GET /talents/{id}`

Akses: public

Response `200` jika ditemukan:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 4,
    "user_id": 4,
    "stage_name": "The Broken Strings",
    "genre": ["Pop Punk", "Alternative Rock"],
    "price_min": 500000,
    "price_max": 2000000,
    "city": "Bandung",
    "bio": "Band pop punk asal Bandung dengan 5 tahun pengalaman.",
    "portfolio_link": "https://youtube.com/thebrokenstrings",
    "verified": true,
    "average_rating": 4.5,
    "total_reviews": 12
  }
}
```

Error `404`:

```json
{
  "success": false,
  "message": "Talent tidak ditemukan"
}
```

### My Talent

`GET /talents/my`

Akses: login, role `talent`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "user_id": 1,
    "stage_name": "The Broken Strings",
    "genre": ["Pop Punk", "Alternative Rock"],
    "price_min": 1000000,
    "price_max": 3000000,
    "city": "Bandung",
    "bio": "Band pop punk Bandung",
    "portfolio_link": "https://example.com",
    "verified": false,
    "average_rating": 0,
    "total_reviews": 0
  }
}
```

### Create Talent Profile

`POST /talents`

Akses: login, role `talent`

Body:

```json
{
  "stage_name": "The Broken Strings",
  "genre_ids": [1, 2],
  "price_min": 1000000,
  "price_max": 3000000,
  "city": "Bandung",
  "bio": "Band pop punk Bandung",
  "portfolio_link": "https://example.com"
}
```

Validasi:

- `stage_name`: required, string, max 255
- `genre_ids`: optional array, item harus ada di `genres.id`
- `price_min`: optional numeric
- `price_max`: optional numeric
- `city`: required, string, max 255
- `bio`: optional string
- `portfolio_link`: optional url

Response `201`:

```json
{
  "success": true,
  "message": "Profil talent berhasil dibuat",
  "data": {
    "id": 1,
    "user_id": 1,
    "stage_name": "The Broken Strings",
    "genre": ["Pop Punk", "Alternative Rock"],
    "price_min": 1000000,
    "price_max": 3000000,
    "city": "Bandung",
    "bio": "Band pop punk Bandung",
    "portfolio_link": "https://example.com",
    "verified": false,
    "average_rating": 0,
    "total_reviews": 0
  }
}
```

Catatan: registrasi role `talent` sudah otomatis membuat profil talent, jadi endpoint ini akan sering menghasilkan `422` `Profil talent sudah ada`.

### Update Talent Profile

`PUT /talents/{id}`

Akses route: login, role `talent`

Otorisasi controller: owner talent atau admin. Karena route hanya mengizinkan role `talent`, admin tidak bisa mencapai endpoint ini melalui route saat ini.

Body:

```json
{
  "stage_name": "Nama Stage Baru",
  "genre_ids": [1, 3],
  "price_min": 1500000,
  "price_max": 3500000,
  "city": "Jakarta",
  "bio": "Bio baru",
  "portfolio_link": "https://example.com/portfolio"
}
```

Response `200`:

```json
{
  "success": true,
  "message": "Profil talent berhasil diperbarui",
  "data": {
    "id": 1,
    "stage_name": "Nama Stage Baru",
    "genre": ["Pop Punk", "Alternative Rock"]
  }
}
```

### Delete Talent Profile

`DELETE /talents/{id}`

Akses route: login, role `talent`

Otorisasi controller: hanya admin. Karena route hanya mengizinkan role `talent`, endpoint ini praktis selalu gagal `403` untuk user biasa.

Error umum:

```json
{
  "success": false,
  "message": "Akses ditolak. Hanya admin yang dapat menghapus data ini."
}
```

## Events

### List Events

`GET /events`

Akses: public

Query yang benar-benar dipakai controller:

- `status`: `dibuka|ditutup|selesai|dibatalkan`
- `city`: exact match
- `budget_min`: numeric
- `budget_max`: numeric
- `search`: cari title
- `per_page`: default 15

Query yang tercantum di annotation tetapi belum diproses controller saat ini:

- `genre`
- `date_from`
- `date_to`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "events": [],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1
    }
  }
}
```

### Detail Event

`GET /events/{event}`

Akses: public

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "organizer_id": 2,
    "title": "Punk Night Vol. 3",
    "description": "Malam punk rock terbaik di Bandung.",
    "budget": "3000000.00",
    "event_date": "2026-04-15",
    "venue_name": "Kafe Kota Bandung",
    "latitude": "-6.91750000",
    "longitude": "107.61910000",
    "city": "Bandung",
    "status": "dibuka",
    "genres": [],
    "genre_needed": [],
    "total_applicants": 0
  }
}
```

### My Events

`GET /events/my`

Akses: login, role `eo`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "events": [],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1
    }
  }
}
```

### Create Event

`POST /events`

Akses: login, role `eo`

Body:

```json
{
  "title": "Punk Night Vol. 3",
  "description": "Malam punk rock terbaik di Bandung.",
  "budget": 3000000,
  "event_date": "2026-04-15",
  "venue_name": "Kafe Kota Bandung",
  "latitude": -6.9175,
  "longitude": 107.6191,
  "city": "Bandung",
  "status": "dibuka",
  "genre_ids": [1, 5]
}
```

Validasi:

- `title`: required, string, max 255
- `description`: required, string
- `budget`: required, numeric, min 0
- `event_date`: required, date
- `venue_name`: required, string, max 255
- `latitude`: nullable numeric between -90 and 90
- `longitude`: nullable numeric between -180 and 180
- `city`: required, string, max 255
- `status`: nullable enum `dibuka|ditutup|selesai|dibatalkan`
- `genre_ids`: nullable array
- `genre_ids.*`: integer

Response `201`:

```json
{
  "success": true,
  "message": "Event berhasil dibuat",
  "data": {}
}
```

### Update Event

`PUT /events/{event}`

Akses: login, role `eo`

Body: semua field optional, sama seperti create event.

Response `200`:

```json
{
  "success": true,
  "message": "Event berhasil diperbarui",
  "data": {}
}
```

Catatan: backend saat ini belum mengecek owner event di controller update, hanya role `eo`.

### Cancel Event

`DELETE /events/{event}`

Akses: login, role `eo` atau `admin`

Efek: tidak hard delete, hanya update `status = dibatalkan`.

Response `200`:

```json
{
  "success": true,
  "message": "Event berhasil dibatalkan"
}
```

## Applications

Application adalah record talent melamar event. Invitation juga memakai tabel/model `applications`, dengan `source = invitation`.

Status application:

- `pending`
- `accepted`
- `rejected`

Source:

- `apply`
- `invitation`

### Apply Event

`POST /applications`

Akses: login, role `talent`

Body:

```json
{
  "event_id": 1,
  "message": "Kami siap tampil di acara ini.",
  "proposed_price": 1500000
}
```

Validasi:

- `event_id`: required, exists `events.id`
- `message`: optional string
- `proposed_price`: required numeric min 0

Response `201`:

```json
{
  "success": true,
  "message": "Lamaran berhasil dikirim",
  "data": {
    "event_id": 1,
    "message": "Kami siap tampil di acara ini.",
    "proposed_price": 1500000,
    "talent_id": 3,
    "source": "apply",
    "status": "pending"
  }
}
```

Error jika sudah pernah apply/invited ke event tersebut:

```json
{
  "success": false,
  "message": "Kamu sudah pernah melamar ke event ini"
}
```

### My Applications

`GET /applications/my`

Akses: login, role `talent`

Hanya mengembalikan application dengan `source = apply`.

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "applications": []
  }
}
```

### Event Applications

`GET /events/{event_id}/applications`

Akses: login, role `eo`

Query:

- `status`: `pending|accepted|rejected`
- `source`: `apply|invitation`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "applications": [
      {
        "id": 10,
        "source": "apply",
        "message": "Kami siap tampil.",
        "proposed_price": "1500000.00",
        "status": "pending",
        "created_at": "2026-05-19T00:00:00.000000Z",
        "talent": {
          "id": 3,
          "stage_name": "The Broken Strings",
          "genre": ["Pop Punk"],
          "city": "Bandung",
          "verified": false,
          "average_rating": 0
        }
      }
    ]
  }
}
```

Catatan: backend saat ini belum mengecek apakah EO adalah pemilik event pada endpoint list application.

### Accept / Reject Application

`PUT /applications/{id}/status`

Akses: login, role `eo`

Body:

```json
{
  "status": "accepted",
  "agreed_price": 1500000
}
```

Validasi:

- `status`: required enum `accepted|rejected`
- `agreed_price`: required jika `status = accepted`, numeric min 0

Jika accepted, backend otomatis membuat booking dengan status `confirmed`.

Response accepted `200`:

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

Response rejected `200`:

```json
{
  "success": true,
  "message": "Lamaran ditolak",
  "data": {
    "application": {
      "id": 10,
      "status": "rejected"
    }
  }
}
```

### Cancel Application

`DELETE /applications/{id}`

Akses: login, role `talent`

Syarat: application masih `pending`.

Response `200`:

```json
{
  "success": true,
  "message": "Lamaran berhasil dibatalkan"
}
```

Catatan: backend saat ini belum mengecek owner application pada endpoint cancel application.

## Invitations

Invitation adalah application dengan `source = invitation`.

### Send Invitation

`POST /invitations`

Akses: login, role `eo`

Body:

```json
{
  "event_id": 1,
  "talent_id": 3,
  "offered_price": 2000000
}
```

Validasi:

- `event_id`: required, exists `events.id`
- `talent_id`: required, exists `users.id`
- `offered_price`: required numeric min 0

Response `201`:

```json
{
  "success": true,
  "message": "Undangan berhasil dikirim",
  "data": {
    "event_id": 1,
    "talent_id": 3,
    "offered_price": 2000000,
    "source": "invitation",
    "status": "pending"
  }
}
```

Catatan: validasi `talent_id` hanya memastikan user ada, belum memastikan role user adalah `talent`.

### My Invitations

`GET /invitations/my`

Akses: login, role `talent`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "invitations": []
  }
}
```

### Sent Invitations

`GET /invitations/sent`

Akses: login, role `eo`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "invitations": [
      {
        "id": 15,
        "event": {},
        "talent": {
          "id": 3,
          "stage_name": "The Broken Strings",
          "city": "Bandung",
          "verified": false,
          "average_rating": 0,
          "genre": []
        },
        "offered_price": "2000000.00",
        "proposed_price": "2000000.00",
        "status": "pending",
        "created_at": "2026-05-19T00:00:00.000000Z"
      }
    ]
  }
}
```

### Respond Invitation

`PUT /invitations/{id}/respond`

Akses: login, role `talent`

Body:

```json
{
  "status": "accepted"
}
```

Validasi:

- `status`: required enum `accepted|rejected`

Jika accepted, backend otomatis membuat booking dengan `agreed_price = offered_price`.

Response accepted `200`:

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
      "agreed_price": "2000000.00",
      "status": "confirmed"
    }
  }
}
```

Catatan: backend saat ini belum mengecek apakah user login adalah penerima undangan.

## Bookings

Booking dibuat otomatis ketika application atau invitation diterima.

Status booking:

- `confirmed`
- `completed`
- `cancelled`

### My Bookings

`GET /bookings/my`

Akses: login

Query:

- `status`: `confirmed|completed|cancelled`

Role behavior:

- `eo`: hanya booking dari event miliknya
- `talent`: hanya booking miliknya
- `admin`: semua booking

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "bookings": [
      {
        "id": 1,
        "application_id": 10,
        "source": "apply",
        "event": {
          "id": 1,
          "title": "Punk Night Vol. 3",
          "event_date": "2026-04-15",
          "venue_name": "Kafe Kota Bandung"
        },
        "talent": {
          "id": 3,
          "stage_name": "The Broken Strings"
        },
        "agreed_price": "1500000.00",
        "status": "confirmed",
        "created_at": "2026-05-19T00:00:00.000000Z"
      }
    ]
  }
}
```

### Detail Booking

`GET /bookings/{id}`

Akses: login, pemilik event, talent terkait, atau admin

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "application_id": 10,
    "source": "apply",
    "event": {
      "id": 1,
      "title": "Punk Night Vol. 3",
      "event_date": "2026-04-15",
      "venue_name": "Kafe Kota Bandung",
      "latitude": "-6.91750000",
      "longitude": "107.61910000"
    },
    "talent": {
      "id": 3,
      "stage_name": "The Broken Strings"
    },
    "agreed_price": "1500000.00",
    "status": "confirmed",
    "created_at": "2026-05-19T00:00:00.000000Z"
  }
}
```

### Complete Booking

`PUT /bookings/{id}/complete`

Akses: login, role `eo`, pemilik event

Syarat: status booking `confirmed`.

Efek tambahan: membuat notification untuk talent.

Response `200`:

```json
{
  "success": true,
  "message": "Booking telah ditandai selesai",
  "data": {
    "id": 1,
    "status": "completed"
  }
}
```

### Cancel Booking

`PUT /bookings/{id}/cancel`

Akses: login, pemilik event, talent terkait, atau admin

Syarat: status booking `confirmed`.

Response `200`:

```json
{
  "success": true,
  "message": "Booking berhasil dibatalkan"
}
```

## Reviews

Review hanya dibuat oleh EO setelah booking selesai.

### Create Review

`POST /reviews`

Akses: login, role `eo`

Body:

```json
{
  "booking_id": 1,
  "rating": 5,
  "comment": "Penampilan sangat bagus."
}
```

Validasi:

- `booking_id`: required, exists `bookings.id`
- `rating`: required integer min 1 max 5
- `comment`: optional string

Syarat bisnis:

- user adalah EO pemilik event booking
- booking status harus `completed`
- satu booking hanya boleh punya satu review

Efek tambahan:

- update `talents.average_rating`
- update `talents.total_reviews`
- membuat notification untuk talent

Response `201`:

```json
{
  "success": true,
  "message": "Review berhasil dikirim",
  "data": {
    "id": 1,
    "booking_id": 1,
    "talent": {
      "id": 3,
      "stage_name": "The Broken Strings"
    },
    "event": {
      "id": 1,
      "title": "Punk Night Vol. 3"
    },
    "rating": 5,
    "comment": "Penampilan sangat bagus.",
    "created_at": "2026-05-19T00:00:00.000000Z"
  }
}
```

### Public Talent Reviews

`GET /talents/{talent_id}/reviews`

Akses: public

`talent_id` bisa berupa user id talent. Backend juga punya fallback mencari by talent profile id.

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "talent_id": 3,
    "stage_name": "The Broken Strings",
    "average_rating": 4.5,
    "total_reviews": 2,
    "reviews": [
      {
        "id": 1,
        "organizer_name": "Kafe Kota",
        "event_title": "Punk Night Vol. 3",
        "rating": 5,
        "comment": "Penampilan sangat bagus.",
        "created_at": "2026-05-19T00:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 1,
      "last_page": 1
    }
  }
}
```

### My Reviews

`GET /reviews/my`

Akses: login, role `talent`

Response `200`: sama seperti public talent reviews, tetapi talent diambil dari user login.

## Notifications

Notification dibuat otomatis oleh backend dari aksi utama. Mobile tidak perlu mengirim request khusus untuk membuat notification. Mobile cukup mengambil daftar notification dan menandai sudah dibaca.

Trigger notification saat ini:

- `POST /applications`: EO mendapat `application_created`
- `PUT /applications/{id}/status`: Talent mendapat `application_accepted` atau `application_rejected`
- `DELETE /applications/{id}`: EO mendapat `application_cancelled`
- `POST /invitations`: Talent mendapat `invitation_received`
- `PUT /invitations/{id}/respond`: EO mendapat `invitation_accepted` atau `invitation_rejected`
- `PUT /bookings/{id}/complete`: Talent mendapat `booking_completed`
- `PUT /bookings/{id}/cancel`: pihak lawan mendapat `booking_cancelled`; jika admin yang membatalkan, EO dan talent sama-sama mendapat notification
- `POST /reviews`: Talent mendapat `review_created`
- `PUT /admin/talents/{id}/verify`: Talent mendapat `talent_verified` atau `talent_unverified`
- `PUT /admin/events/{id}/moderate`: EO mendapat `event_moderated`

Atribut notification:

- `type`: kategori besar, salah satu `application|booking|invitation|review|event|talent`
- `action`: kejadian spesifik, contoh `application_accepted`
- `reference_type`: target detail screen, contoh `booking`
- `reference_id`: id data target
- `data`: payload ringan untuk deep-link mobile
- `is_read`: status sudah dibaca
- `read_at`: waktu notification dibaca

### List Notifications

`GET /notifications`

Akses: login

Query:

- `is_read`: boolean
- `type`: `application|booking|invitation|review|event|talent`
- `action`: string, contoh `application_accepted`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "notifications": [
      {
        "id": 1,
        "user_id": 3,
        "title": "Review Baru",
        "body": "EO ... telah memberikan ulasan untuk performa kamu.",
        "type": "review",
        "action": "review_created",
        "reference_type": "review",
        "reference_id": 1,
        "data": {
          "review_id": 1,
          "booking_id": 4,
          "event_id": 7,
          "event_title": "Braga Punk Night Vol.4",
          "rating": 5
        },
        "is_read": false,
        "read_at": null,
        "created_at": "2026-05-19T00:00:00.000000Z"
      }
    ],
    "unread_count": 1,
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 1,
      "last_page": 1
    }
  }
}
```

### Mark Notification As Read

`PUT /notifications/{id}/read`

Akses: login, owner notification

Efek:

- `is_read` menjadi `true`
- `read_at` diisi waktu saat endpoint dipanggil

Response `200`:

```json
{
  "success": true,
  "message": "Notifikasi ditandai sudah dibaca"
}
```

### Mark All Notifications As Read

`PUT /notifications/read-all`

Akses: login

Efek:

- semua notification user login dengan `is_read = false` menjadi `true`
- `read_at` diisi waktu saat endpoint dipanggil

Response `200`:

```json
{
  "success": true,
  "message": "Semua notifikasi ditandai sudah dibaca"
}
```

## Matchmaking

### Talent Recommendations

`GET /events/{event_id}/recommendations`

Akses: login, role `eo`, pemilik event

Algoritma saat ini:

- genre match: +50 jika minimal ada satu genre event yang sama dengan genre talent
- budget match: +30 jika `event.budget >= talent.price_min`
- location match: +20 jika kota sama persis setelah lowercase dan trim
- hanya talent `verified = true`
- hasil diurutkan dari score tertinggi
- maksimal 5 rekomendasi

Response `200`:

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
          "genre": ["Pop Punk"],
          "price_min": "1000000.00",
          "price_max": "3000000.00",
          "city": "Bandung",
          "verified": true,
          "average_rating": 4.5
        }
      }
    ]
  }
}
```

## Admin

Semua endpoint admin berada di prefix `/admin` dan membutuhkan login role `admin`.

### Dashboard

`GET /admin/dashboard`

Response `200`:

```json
{
  "success": true,
  "message": "Dashboard stats",
  "data": {
    "total_users": 10,
    "total_talents": 6,
    "total_eo": 3,
    "total_events": 12,
    "active_events": 4,
    "total_bookings": 5,
    "completed_bookings": 2,
    "total_reviews": 2
  }
}
```

### List Users

`GET /admin/users`

Query:

- `role`: `eo|talent`
- `search`: cari `name` atau `email`

Response `200`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "users": [],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1
    }
  }
}
```

### Delete User

`DELETE /admin/users/{id}`

Tidak bisa menghapus user role `admin`.

Response `200`:

```json
{
  "success": true,
  "message": "Akun pengguna berhasil dihapus"
}
```

### Verify Talent

`PUT /admin/talents/{id}/verify`

Path `id`: user id talent, bukan selalu talent profile id.

Body:

```json
{
  "verified": true
}
```

Response `200`:

```json
{
  "success": true,
  "message": "Talent berhasil diverifikasi",
  "data": {
    "id": 3,
    "stage_name": "The Broken Strings",
    "verified": true
  }
}
```

Jika `verified = false`, message menjadi `Verifikasi talent dicabut`.

### Moderate Event

`PUT /admin/events/{id}/moderate`

Body:

```json
{
  "status": "dibatalkan",
  "reason": "Melanggar ketentuan platform"
}
```

Validasi:

- `status`: required enum `dibuka|ditutup|selesai|dibatalkan`
- `reason`: optional string

Status event yang diterima backend: `dibuka|ditutup|selesai|dibatalkan`.

Jika `reason` dikirim, backend membuat notification untuk EO.

Response `200`:

```json
{
  "success": true,
  "message": "Event berhasil dimoderasi"
}
```

## Entity Fields

### User

```json
{
  "id": 1,
  "name": "Budi Santoso",
  "email": "budi@email.com",
  "phone": "081234567890",
  "role": "talent",
  "email_verified_at": null,
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Talent

```json
{
  "id": 1,
  "user_id": 1,
  "stage_name": "The Broken Strings",
  "genre": ["Pop Punk", "Alternative Rock"],
  "price_min": "1000000.00",
  "price_max": "3000000.00",
  "city": "Bandung",
  "bio": "Band pop punk Bandung",
  "portfolio_link": "https://example.com",
  "verified": false,
  "average_rating": 0,
  "total_reviews": 0,
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Event

```json
{
  "id": 1,
  "organizer_id": 2,
  "title": "Punk Night Vol. 3",
  "description": "Malam punk rock terbaik di Bandung.",
  "budget": "3000000.00",
  "event_date": "2026-04-15",
  "venue_name": "Kafe Kota Bandung",
  "latitude": "-6.91750000",
  "longitude": "107.61910000",
  "city": "Bandung",
  "status": "dibuka",
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Application / Invitation

```json
{
  "id": 10,
  "event_id": 1,
  "talent_id": 3,
  "source": "apply",
  "message": "Kami siap tampil.",
  "proposed_price": "1500000.00",
  "offered_price": null,
  "status": "pending",
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Booking

```json
{
  "id": 1,
  "application_id": 10,
  "agreed_price": "1500000.00",
  "status": "confirmed",
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Review

```json
{
  "id": 1,
  "booking_id": 1,
  "rating": 5,
  "comment": "Penampilan sangat bagus.",
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

### Notification

```json
{
  "id": 1,
  "user_id": 3,
  "title": "Review Baru",
  "body": "EO ... telah memberikan ulasan untuk performa kamu.",
  "type": "review",
  "action": "review_created",
  "reference_type": "review",
  "reference_id": 1,
  "data": {
    "review_id": 1,
    "booking_id": 4,
    "event_id": 7,
    "event_title": "Braga Punk Night Vol.4",
    "rating": 5
  },
  "is_read": false,
  "read_at": null,
  "created_at": "2026-05-19T00:00:00.000000Z",
  "updated_at": "2026-05-19T00:00:00.000000Z"
}
```

## Endpoint Matrix

| Method | Endpoint | Akses |
| --- | --- | --- |
| POST | `/auth/register` | Public |
| POST | `/auth/login` | Public |
| POST | `/auth/logout` | Login |
| GET | `/auth/me` | Login |
| PUT | `/users/profile` | Login |
| PUT | `/users/password` | Login |
| GET | `/genres` | Public |
| GET | `/talents` | Public |
| GET | `/talents/my` | Talent |
| GET | `/talents/{id}` | Public |
| POST | `/talents` | Talent |
| PUT | `/talents/{id}` | Talent |
| DELETE | `/talents/{id}` | Talent route, tetapi controller hanya admin |
| GET | `/talents/{talent_id}/reviews` | Public |
| GET | `/events` | Public |
| GET | `/events/my` | EO |
| GET | `/events/{event}` | Public |
| POST | `/events` | EO |
| PUT | `/events/{event}` | EO |
| DELETE | `/events/{event}` | EO/Admin |
| POST | `/applications` | Talent |
| GET | `/applications/my` | Talent |
| DELETE | `/applications/{id}` | Talent |
| GET | `/events/{event_id}/applications` | EO |
| PUT | `/applications/{id}/status` | EO |
| POST | `/invitations` | EO |
| GET | `/invitations/sent` | EO |
| GET | `/invitations/my` | Talent |
| PUT | `/invitations/{id}/respond` | Talent |
| GET | `/bookings/my` | Login |
| GET | `/bookings/{id}` | Related user/Admin |
| PUT | `/bookings/{id}/complete` | EO owner |
| PUT | `/bookings/{id}/cancel` | Related user/Admin |
| POST | `/reviews` | EO |
| GET | `/reviews/my` | Talent |
| GET | `/notifications` | Login |
| PUT | `/notifications/{id}/read` | Login |
| PUT | `/notifications/read-all` | Login |
| GET | `/events/{event_id}/recommendations` | EO owner |
| GET | `/admin/dashboard` | Admin |
| GET | `/admin/users` | Admin |
| DELETE | `/admin/users/{id}` | Admin |
| PUT | `/admin/talents/{id}/verify` | Admin |
| PUT | `/admin/events/{id}/moderate` | Admin |

## Catatan Integrasi Frontend

- Simpan token dari `login/register`, lalu kirim lewat `Authorization: Bearer`.
- Untuk talent baru, profil talent sudah otomatis dibuat saat register role `talent`; gunakan `GET /talents/my` untuk mengambil profilnya.
- Gunakan user id talent sebagai `talent_id` untuk application, invitation, review, dan recommendation output.
- `DELETE /events/{event}` adalah cancel event, bukan hapus permanen.
- `DELETE /applications/{id}` adalah cancel application dan hanya bisa jika status masih `pending`.
- Booking tidak dibuat lewat endpoint manual; booking dibuat otomatis dari accepted application/invitation.
- Review hanya bisa setelah booking `completed`.
- Untuk endpoint yang response aktualnya tertukar, frontend sebaiknya disesuaikan dengan backend aktual atau backend diperbaiki agar kembali ke format standar.
