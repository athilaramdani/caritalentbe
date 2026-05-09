# Panduan Presentasi Proyek Backend ABP: CariTalent

Dokumen ini berisi panduan alur presentasi, pembagian materi, penjelasan konsep teknis (arsitektur), naskah (script) berbicara, serta prediksi pertanyaan dan jawaban (QnA) dari Dosen penguji.

---

## 1. Pembagian Materi & Naskah Presentasi (Script)

Posisikan layar sedang menampilkan **Swagger UI** (atau Postman Desktop) dan kodingan di **VS Code**. 

### A. Pembukaan & Arsitektur Dasar (Oleh: Athila Ramdani S. - Ketua)
**Fokus:** Membuka presentasi, perkenalan, arsitektur dasar, Auth, dan Profil Talent.

**Naskah Berbicara (Script):**
> "Selamat pagi/siang Bapak/Ibu Dosen penguji. Kami dari kelompok [Sebut nama kelompok / CariTalent] akan mempresentasikan hasil pengerjaan proyek mata kuliah [Sebut nama matkul, Misal: Aplikasi Berbasis Platform / Web Framework], yakni pembuatan Backend Web Service API untuk 'CariTalent', sebuah sistem direktori dan *booking* talent di industri ekonomi kreatif."
> 
> "Perkenalkan saya Athila Ramdani selaku ketua kelompok, dan rekan saya Muhammad Irgiansyah serta Arfian Ghifari."
> 
> *(Buka VS Code / Jelaskan Arsitektur singkat)*
> "Pada pengembangan proyek kali ini, kami memutuskan untuk mengusung arsitektur **Client-Server** dengan mengimplementasikan sistem RESTful API yang ditulis menggunakan framework **Laravel**. Fokus proyek kami saat ini murni di sisi **Server-Side (Backend)**. Seluruh endpoint API dari sistem ini menggunakan standar *JSON Envelope* yang terstruktur (berisi parameter `success`, `message`, dan `data`) agar kelak sangat mudah di- *consume* oleh Client atau Front-End."
> 
> *(Pindah layer ke tampilan Swagger UI / Postman)*
> "Saya akan mendemokan alur logika awalnya. Aplikasi kami berdiri di atas konsep pengamanan **Stateless Authentication** dengan memanfaatkan *Laravel Sanctum*. Ini adalah endpoint Register dan Login. Saat _user_ (baik Talent maupun EO) berhasil Login, sistem kami akan memunculkan *Bearer Token*." 
> *(Demo tekan pencet Try & Execute/Send API Login -> copy Token keluaran yang didapat)*
> "Token inilah yang nantinya harus disisipkan secara konstan di *Header Authentication* untuk mengakses fasilitas (endpoint) lainnya yang *restricted*."
> 
> "Setelah autentikasi, mengandalkan Token ini, setiap pihak Talent bisa memanipulasi informasi profil, status verifikasi, dan memanajemen media portofolio mereka sendiri via grup endpoint `/talents` dan `/users`. Sementara grup *Event*-nya itu sendiri nanti ada alur tersendiri yang akan didemokan oleh rekan saya, Irgi."

---

### B. Core Flow 1: Event & Proses Lamaran (Oleh: Muhammad Irgiansyah)
**Fokus:** Menjelaskan Entitas Event, sistem Apply dari Talent, dan Invite dari EO.

**Naskah Berbicara (Script):**
> "Baik, terima kasih Athila. Saya Irgiansyah akan menjabarkan alur bisnis di dalam modul **Event** dan tahap interaksi antara si Talent dengan Event Organizer (EO)."
> 
> *(Tunjukkan daftar API di menu/tag Event pada layar Swagger)*
> "Di sini, pihak EO dapat mempublikasikan sebuah lowongan proyek atau acara (event) baru via *endpoint* CRUD pada `/events`. Apabila event tersebut sudah *publish*, di dalam sistem kami ada dua skenario pertemuan antara pencari bakat dan si talentnya."
> 
> *(Arahkan/sorot tabel API Applications - Apply)*
> "Skenario pertama didorong oleh pihak **Talent yang proaktif**. Talent dapat mencari katalog event, lalu jika tertarik, mereka melakukan *apply* (pelamaran) ke event target. Itu kami fasilitasi semuanya pada gerbang *endpoint* `/applications`."
> *(Pencet Eksekusi/Send pada metode POST /applications kalau bisa didemokan)*
> 
> *(Arahkan/sorot tabel API Invitations - Invite)*
> "Lalu, skenario pembalikannya yaitu skenario kedua: **EO yang proaktif**. Apabila ada seorang EO berjalan-jalan di katalog Profile Talents, dan merasa 'Wah, ini masuk untuk model saya', maka peran EO-nya bisa 'menembak' atau mengirimkan *invitation* eksklusif (*undangan*) kepada Talent tersebut. Ini diproses via rute *endpoint* `/invitations`."
> 
> "Nantinya, apabila lamaran (*Apply*) tadi di-terima oleh eo-nya, **ATAU** Undangan (*Invite*) dari sang EO-nya di-terima pihak talent, maka statusnya memicu *(trigger)* tahapan transisi terakhir, yaitu **Booking**. Proses Booking transaksi penutup ini akan dilanjutkan oleh rekan saya bagian ini, Arfian."

---

### C. Core Flow 2: Transaksi Akhir, Matchmaking & Admin (Oleh: Arfian Ghifari)
**Fokus:** Menjelaskan finalisasi via Booking, Review, Matchmaking cerdas, dan Administrator.

**Naskah Berbicara (Script):**
> "Terima kasih banyak, Irgi. Saya Arfian yang akan mengambil alih menceritakan skema dari tahap finalisasi transaksi dalam sistem CariTalent."
> 
> *(Tunjukkan kelompok endpoint /bookings)*
> "Jadi, manakala seorang Talent dan EO sudah bersepakat (disebut *Match* seperti yang dijelaskan Irgi), nah sistem kami secara *backend* merangkai data otomatis menjadi semacam nota catatan atau entiti baru yang disebut **Booking**. Lewat endpoint ini, dua belah pihak dapat terikat kontrak status pengerjaan acaranya. Ada yang sifatnya pending hingga bisa membatalkannya, atau mengubah ke status 'Complete' kalau proyeknya dirasa selesai."
> *(Jika mau, demokan hit API PUT update untuk mengganti status penyelesaian)*
> 
> *(Tunjukkan kelompok endpoint /reviews)*
> "Selanjutnya, fitur penyerta untuk Booking yang sudah *Complete* tadi, EO diizinkan mendistribusikan penilaian atau *rating / review* kepada si Talent. Data review ini disimpan untuk menggenjot performa katalog portofolio Talent ke depannya."
> 
> *(Tunjukkan kelompok endpoint /recommendations atau algoritma yang dimiliki)*
> "Disamping *basic* form tadi, kami juga coba mendeveop secarik algoritma khusus di modul **Matchmaking**. Jika endpoint *recommendations* ini di-_hit_ atau di-_request_, sistem di *background* akan coba membandingkan syarat di dalam tabel tabel Event (seperti *Genre* yang dibutuhkan dan batasan *Budget* acara), memetakannya dengan spesialisasi Talent, lalu mengukur kecocokan. Output balikan API-nya langsung mentotalkan prioritas rekomendasi ke Client."
> 
> "Terakhir juga ada beberapa kontrol khusus *Administrator / Staff* untuk menonaktifkan aktivitas ilegal user maupun meninjau ulang event melanggar. Secara garis besar alur _software engineer_ *backend* kelompok ini berjalan demikian rupa. Saya tarik dan kembalikan waktunya ke Athila kembali."

*(Athila Menutup)*
> "Demikianlah rancangan fungsional dan model database back-end *CariTalent* dari progres tim kami. Kami mengucapkan mohon maaf atas kekurangannya, dan terima kasih besar atas perhatiannya. Kami persilakan waktu kepada Bapak/Ibu dosen sekiranya jika ada celah, yang ingin dielaborasi maupun dikritisi, dicoba koreksi ke kami."

---

## 2. Penjelasan Konsep Teknis (Bahan Hafalan / Pemahaman Tambahan)

Untuk kelancaran saat presentasi, sangat disarankan jika tim memahami 4 konsep kunci berikut ini yang menjadi *fondasi* pembuatan proyek:

- **Server-side vs Client-side:** Kami saat ini murni mengembangkan Server-side yang berperan sebagai penyedia layanan Data (Web Service). Frontend (Client) dipisah karena proyek dikhususkan untuk membangun *Headless API*. Client Side yang berupa Mobile Application/Web tidak dipublish.
- **RESTful API:** Arsitektur yang tidak me_render_ HTML, melainkan mengandalkan standar transfer data (JSON) menggunakan manipulatif *HTTP Verb* (`GET`, `POST`, `PUT`, `DELETE`).
- **Sanctum (Bearer Token):** Di dunia API, sistem dilarang menyimpan riwayat `session()` seperti ketika nge-_build_ full-stack website laravel dengan blade biasa. Token di-_generate_ setiap kali *device* login, memanggil token rahasia ini sebagai kunci (Auth Bearer).
- **API Resource (Envelope Model):** Mekanisme terstandarisir untuk me_mapping_ kolom *database* mana saja yang direktur (biar password/id *privacy* tidak kesedot keluar) beserta mengamankan struktur JSON dalam kotak/form *envelope* konsisten (`success, message, data`).

---

## 3. Prediksi Pertanyaan Dosen & Cara Menjawabnya (QnA)

Berikut merupakan pola jawaban template untuk membalas 'hantaman' tanya-jawab oleh dosen:

**Q1: "Mana antarmuka (UI / Frontend) web-nya/aplikasinya? Kenapa cuma pakai Swagger & Layar Hitam Postman kayak gini saja jalannya?"**
> **A:** *"Izin menjawab pak/bu. Sesuai dengan batasan rancangan kami, arsitekur software nya mengambil ranah 'Client-Server Architecture', yang berfokus ke fondasi logika kerangka di Server.** (Pause sedikit).** Jadi, tujuannya semata untuk mematangkan *Headless API* / Web Servicenya. Kalau ini diselesaikan secara solid, implementasi antarmuka/UI nantinya oleh tim Front-end akan sangat mudah dan bisa dipakai untuk banyak multi-platform (Android, iOS, Web) karena tinggal consume satu sumber endpoint dari Swagger kami saja ini."*

**Q2: "Lalu, bagaimana logikanya agar API kalian ini tidak di acak-acak (dihack) oleh orang luar yang tidak memiliki *account*?"**
> **A:** *"Iya, menyambung soal security, kami mengimplementasikan layer *Authentication Bearer Token* bawaan library Laravel yakni **Sanctum**. Token akan dibangkitkan pada saat prosedur `login` API, dan kami menjaga titik temu data dengan memasang filter penjaga *middleware:* (`auth:sanctum`) di hampir semua *endpoint* routingan krusial. Selain dari token otorisasi keamanan ini, Validasi *Form / Request Validation* wajib diterapkan supaya menghindari bug *SQL Attack*."*

**Q3: "Apa maksudnya dengan respons yang dibungkus? Mengapa tidak datanya saja (*raw database*) langsung dimunculkan ke JSON kalian?"**
> **A:** *"Konsep ini kami pasang sebagai 'Envelope Response Pattern' (Pola Pembungkusan). Logikanya: Kalau kita lempar data mentah atau apalagi log *error exception*-nya langsung ke Front-end, si developer Front-end akan pusing membaca respon yang berbeda-rupa. Dengan format kami, client aplikasi cuma cukup bertanya 'If `success` = `true`?' Jika ya memuat *data*, jika gagal maka mencetak string di variabel *message*.* Mudah dimengerti antar *programmer*."

**Q4: "Coba Arfian jelaskan logika Matchmaking atau Rekomendasi kalian seperti apa jalannya secara gambaran besarnya atau dalam *codingan*?"**
> **A:** *(Ini prediksi pertanyaan untuk fokus pada *fitur-kompleks*/unggulan. Jika dosen bertanya hal teknis mendalam tentang Matchmaking).*
> *"Izin untuk bagian yang ini, kami mempraktekan query data gabung. Kami me-kroscek dan mengambil *table-relation* label *Genre* profil Talent dan *Event*. Jika Match/Sama irisannya, mendapatkan bobot prioritas algoritma. Lalu kita bandingkan ekspektasi bayaran (*Talent Expected Fee*) agar tidak lebih tinggi dari *Budget Acara Event*-nya."*

**Q5: "Ini pembagiannya lumayan banyak yah API nya, ada berpuluh endpoint, nah untuk model kerja timnya sendiri bagaimana agar tidak *nabrak* pas ngoding (tabrakan Git / File Controller)?"**
> **A:** *"Betul Pak/Bu, maka untuk manajemen versinya, sejak awal, kami memutuskan untuk membagi arsitektur ini kedalam bagian per **Domain Fitur (Modul Bisnis)**. Athila memegang inti master *User*, *Auth*, dan pendataan utama *Profil Talent*. Irgi memegang *Core Flow* pertukaran *Event* dan *Invitations*-nya lalu Arfian masuk di kelanjutan akhir yaitu validasi *Booking* transaksi sistem *Matchmaking*. Sebelum kami mecah kode controller ke Postman, kami sepakati model file Migration Databasenya dahulu sebagai inti perjanjian *schema*-nya.*"
