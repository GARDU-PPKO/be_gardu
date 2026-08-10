# BRIEFING SEMINAR PROGRESS — DESA GETAS

---

## 1. RINGKASAN PROYEK (1 MENIT)

> **"Website ini tujuannya untuk apa?"**

Website **Desa Getas** adalah portal wisata desa berbasis web yang digunakan sebagai:
- **Media promosi wisata** desa (paket wisata tubing, river exploration, dll)
- **Katalog UMKM** desa (makanan, kerajinan, pertanian, oleh-oleh)
- **Media informasi budaya dan profil desa** (sejarah, visi-misi, pemerintahan)
- **Sistem booking** paket wisata berbasis WhatsApp
- **Panel admin** untuk pengelolaan konten oleh perangkat desa

Dibangun dengan dua aplikasi terpisah:
1. **Frontend Publik** (React + TypeScript) — untuk pengunjung website
2. **Backend + Admin Panel** (Laravel 13) — untuk pengelola desa dan API

---

## 2. PROGRESS YANG SUDAH SELESAI

### Backend (be_gardu) — Laravel 13

✅ **Login Admin** (session-based, role: superadmin & admin)
✅ **CRUD Paket Wisata** + fitur include/exclude
✅ **CRUD UMKM** (kategori: Makanan, Kerajinan, Pertanian, Oleh-Oleh)
✅ **CRUD Dusun** + galeri + keunggulan
✅ **CRUD Budaya** + jadwal acara
✅ **CRUD Booking** + konfirmasi via WhatsApp
✅ **CRUD Profil Desa** (sejarah, visi, misi, pemerintahan)
✅ **CRUD Statistik Desa**
✅ **Parse WhatsApp Text** (import booking dari chat WA)
✅ **Export Booking ke Excel**
✅ **Webhook WhatsApp (Fonnte)** — booking otomatis via WA
✅ **Manajemen Admin** (khusus superadmin)
✅ **Pengaturan** (nomor WA admin, token Fonnte, rekening bank)
✅ **REST API Publik** — semua endpoint untuk frontend

### Frontend (fe_gardu) — React + TypeScript

✅ **Halaman Utama (Home)** — hero, paket wisata, dusun, UMKM, budaya, AR, profil, statistik, kontak
✅ **Halaman Dusun** — daftar dusun + detail dusun
✅ **Halaman Paket Wisata** — daftar paket + detail paket
✅ **Halaman UMKM** — daftar produk + filter kategori
✅ **Halaman Budaya** — daftar budaya + detail + jadwal
✅ **Halaman Booking (3 langkah)** — pilih paket → isi data → konfirmasi WA
✅ **Halaman Profil Desa** — sejarah, visi-misi, pemerintahan
✅ **Halaman Statistik Desa**
✅ **Halaman Sesi Booking**
✅ **Fitur AR** (section promosi)
✅ **Navbar & Footer** responsif
✅ **Integrasi API** dengan backend

---

## 3. YANG BISA DIDEMOKAN

### Saat ini jalan di **localhost**

| Bagian | Cara Akses |
|--------|-----------|
| **Frontend Website** | `http://localhost:5173` (Vite dev server) |
| **Admin Panel** | `http://localhost:8000/admin` |
| **Login Admin** | Username: `superadmin`, Password: `superadmin123` |
| **REST API** | `http://localhost:8000/api/dusun`, `/api/tour-packages`, dll |

### Ceklis Demo:
- [ ] Website bisa dibuka
- [ ] Internet aman (pastikan tethering/jaringan siap)
- [ ] Admin bisa login (superadmin / superadmin123)
- [ ] Data dummy sudah bersih (cek isi konten)
- [ ] Tidak ada error yang terlihat
- [ ] Frontend memuat data dari backend (API tercantum di .env fe_gardu — saat ini pakai `https://api.gardu.site/api`)

> ⚠️ **Catatan penting:** .env frontend (`fe_gardu/.env`) saat ini mengarah ke `https://api.gardu.site/api`, bukan localhost. Jika demo backend jalan di localhost, ubah `VITE_API_URL` ke `http://localhost:8000/api`. Atau pastikan URL tersebut memang domain yang sudah dideploy.

---

## 4. CATATAN KENDALA

| No | Kendala | Status |
|----|---------|--------|
| 1 | **Alur booking via WhatsApp** — booking tidak tersimpan di database dari sisi frontend. Pengunjung dikirim ke WhatsApp, admin harus input manual. Belum ada integrasi booking dari frontend ke backend via API POST | ⚠️ |
| 2 | **Belum ada frontend publik yang dideploy** — masih jalan di localhost | ⚠️ |
| 3 | **Gambar masih pakai URL** — banyak konten pakai URL gambar eksternal, belum upload file lokal | ⚠️ |
| 4 | **Beberapa file komponen masih stub/kosong** — Button, Card, Input, Modal, Container, BookingCard, BookingHistory, currency.ts, date.ts | ⚠️ |
| 5 | **Versi react-router bentrok** — package.json punya react-router-dom@7 dan react-router@8 | ⚠️ |
| 6 | **Home.tsx duplikat** — dua file Home (Home.tsx kosong, HomePage.tsx dipakai) | ⚠️ |
| 7 | **Belum ada testing** — hanya 1 test file untuk webhook Fonnte di backend | ⚠️ |
| 8 | **Belum ada pagination** — beberapa halaman admin tidak pakai pagination | ⚠️ |

---

## 5. PERTANYAAN UNTUK DOSEN

### Tentang Fitur
1. Apakah fitur yang saat ini kami buat sudah sesuai dengan kebutuhan desa wisata?
2. Apakah perlu ditambahkan fitur **galeri publik** (foto/video desa) di halaman utama?
3. Apakah **fitur AR** perlu dipertahankan atau dihapus?

### Tentang Booking
4. Apakah alur booking melalui WhatsApp sudah sesuai? Atau perlu ada sistem booking terintegrasi penuh (pembayaran online, konfirmasi otomatis)?

### Tentang Konten
5. Apakah ada informasi khusus dari desa yang perlu ditambahkan?
6. Apakah konten yang ada saat ini sudah representatif?

### Tentang Pengelolaan
7. Setelah website selesai, **siapa yang akan mengelola kontennya?** (perangkat desa, karang taruna, atau tim khusus?)

### Target & Skala
8. Apakah cukup **satu desa** (Desa Getas) atau nantinya akan dikembangkan untuk **beberapa desa**?
9. **Kapan target penyelesaian dan deployment?** Apakah ada tenggat tertentu?

---

## 6. JAWABAN SIAP UNTUK PERTANYAAN DOSEN

### "Progress berapa persen?"
> **Sekitar 85–90%, Bu/Pak.** Untuk backend dan admin panel hampir selesai (95%). Frontend publik juga sudah sekitar 85%. Yang masih tersisa adalah penyempurnaan alur booking, perapihan komponen, dan deployment.

### "Fitur apa saja?"
Fitur yang sudah jadi:
1. Website informasi desa (profil, dusun, budaya, statistik)
2. Katalog paket wisata dan UMKM
3. Booking paket wisata via WhatsApp (3 langkah)
4. Admin panel CRUD untuk semua konten
5. Login admin dengan dua peran (superadmin & admin)
6. Integrasi WhatsApp untuk booking otomatis via Fonnte
7. Fitur AR (Augmented Reality) sebagai media promosi

### "Apa kendalanya?"
Kendala yang masih kami hadapi:
1. **Alur booking** — belum fully terintegrasi ke backend dari frontend. Saat ini booking mengarah ke WhatsApp, admin harus input manual. Kami masih mempertimbangkan apakah perlu sistem booking penuh dengan pembayaran online.
2. **Gambar masih pakai URL eksternal** — belum menggunakan upload file lokal. Masih butuh kepastian mana yang lebih efektif.
3. **Deployment** — masih di localhost, kami masih menunggu kepastian domain/hosting.
4. **Fitur AR** — kami belum yakin apakah fitur ini benar-benar dibutuhkan atau hanya pemanis.

### "Kapan target selesai?"
> **Target kami minggu depan fitur utama sudah selesai, dan minggu berikutnya deployment.** Namun tergantung masukan dari Bapak/Ibu hari ini, apakah ada revisi atau penambahan fitur.

### "Siapa target pengguna?"
Ada dua kelompok pengguna:
1. **Pengunjung / wisatawan** — mengakses website untuk mencari informasi dan booking paket wisata
2. **Admin desa** — mengelola konten website (paket wisata, UMKM, budaya, dll) melalui admin panel

---

## 7. OPSI YANG PERLU DIPUTUSKAN DOSEN

| Opsi | Pilihan A | Pilihan B | Rekomendasi |
|------|-----------|-----------|-------------|
| **Alur Booking** | Via WhatsApp (sekarang) + admin input manual | Booking online penuh (tersimpan di database) dengan pembayaran | B — lebih profesional, data otomatis tercatat |
| **Fitur AR** | Dipertahankan | Dihapus | A — bisa jadi nilai plus promosi desa |
| **Gambar** | Pakai URL eksternal | Upload file lokal | B — lebih terkontrol, tidak bergantung pihak ketiga |
| **Deployment** | Hosting mandiri | Server kampus/desa | Menyesuaikan ketersediaan |
| **Target Desa** | Satu desa (Getas) | Bisa dikembangkan multi-desa | A dulu, B untuk pengembangan |
| **Pengelola Konten** | Perangkat desa | Karang taruna / tim khusus | Diskusikan dengan pihak desa |

---

## RINGKASAN TEKNIS CEPAT

| Item | Detail |
|------|--------|
| Nama Aplikasi | **Desa Getas** |
| Lokasi | Desa Getas, Kec. Singorojo, Kab. Kendal, Jawa Tengah |
| Frontend | React 19 + TypeScript + Vite 8 + Tailwind CSS 4 |
| Backend | Laravel 13 + MySQL + Sanctum Auth |
| API | REST JSON (15 endpoint publik) |
| WhatsApp | Fonnte API (webhook + notifikasi) |
| Booking | 3-step wizard → WhatsApp deep link |
| Admin Panel | Blade + Tailwind (login: superadmin) |
| Total Halaman Frontend | 15 halaman (termasuk 3 step booking) |
| Total CRUD Admin | 11 fitur CRUD |
