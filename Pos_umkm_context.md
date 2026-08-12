# Context: POS On-Site + Integrasi Produk UMKM

## 1. Latar Belakang & Tujuan

Saat ini alur booking (`bookings`) dirancang untuk **online booking** (user isi form web → bayar mandiri → upload bukti → verifikasi admin via WA). Kebutuhan baru: pengunjung yang datang **langsung ke lokasi** (walk-in) bisa transaksi di tempat lewat **admin/receptionist** menggunakan **tablet**, tanpa perlu lewat alur WA/upload bukti.

POS ini juga harus bisa jual **2 jenis item sekaligus dalam satu transaksi**:
1. **Tiket paket wisata** (dari `paket_wisata`)
2. **Produk UMKM** (dari `umkm_products` — makanan, kerajinan, oleh-oleh, dsb)

Ditambah kemungkinan item lain-lain (sewa alat, dsb) lewat `pos_products` yang sudah ada.

Role yang bisa akses POS: `admin` dan `superadmin` (pakai `users.role` yang sudah ada, gak perlu tabel baru).

---

## 2. Gap di ERD Sekarang

| Masalah | Detail |
|---|---|
| `pos_products` terpisah total dari `paket_wisata` & `umkm_products` | Kalau dipakai apa adanya, data paket wisata & produk UMKM harus didobel manual ke `pos_products` → rawan gak sinkron (harga beda, stok beda, produk baru gak ke-input) |
| `umkm_products` gak punya kolom stok | Gak ada cara nahan stok pas transaksi di POS |
| `pos_transaction_items.product_id` cuma FK ke `pos_products` | Gak bisa nunjuk ke `paket_wisata` atau `umkm_products` |
| Jual tiket lewat POS gak nyambung ke `bookings` | Padahal `booking_stats_monthly` & histori booking ngambil datanya dari tabel `bookings`. Kalau transaksi POS gak bikin row di `bookings`, laporan kunjungan jadi gak akurat |
| Alur booking online (`PENDING_PAYMENT` → WA → upload bukti → verifikasi) gak relevan buat walk-in | Orang udah di depan admin & bayar cash/QRIS langsung, gak perlu nunggu 24 jam atau verifikasi manual terpisah |

---

## 3. Perubahan Skema yang Diusulkan

### 3.1 `umkm_products` — tambah kolom stok
```
+ stock            int          default 0
+ sku              varchar      nullable, unique   (opsional, buat kemudahan cari di POS)
```

### 3.2 `pos_transaction_items` — bikin polymorphic, bukan FK kaku ke `pos_products`
```diff
pos_transaction_items {
    bigint id PK
    bigint transaction_id FK
-   bigint product_id FK "nullable"
+   enum   item_source   "paket_wisata | umkm_product | pos_product"
+   bigint item_id                      "id dari tabel sesuai item_source (bukan FK constraint, karena beda tabel)"
+   bigint booking_id FK "nullable"     "diisi otomatis kalau item_source = paket_wisata, nunjuk ke bookings.id yang baru dibuat"
    varchar product_name                "snapshot nama, tetap dipertahankan"
    decimal price
    int quantity
    decimal subtotal
    timestamp created_at
    timestamp updated_at
}
```
Kenapa `item_id` bukan FK constraint biasa: karena bisa nunjuk ke 3 tabel berbeda (`paket_wisata`, `umkm_products`, `pos_products`) tergantung `item_source`. Snapshot `product_name` & `price` tetap dipertahankan biar histori transaksi gak berubah walau harga produk aslinya diedit belakangan.

### 3.3 `pos_transactions` — tambah info kasir & tipe transaksi
```diff
pos_transactions {
    bigint id PK
    varchar invoice_number "unique"
    bigint user_id FK "nullable"    → kasir/admin yang input, wajib diisi (bukan nullable lagi)
    decimal total_amount
    decimal paid_amount
    decimal change_amount
    enum payment_method "cash|qris|transfer"
    text notes "nullable"
    enum status "completed|cancelled"
+   varchar customer_name  "nullable"   -- opsional, cuma buat dicetak di struk (bukan buat WA)
    timestamp created_at
    timestamp updated_at
}
```

---

## 4. Alur Transaksi POS

### 4.1 Kasir buka POS (tablet)
- Tampilkan katalog gabungan: tab **"Paket Wisata"** (dari `paket_wisata` + `paket_wisata_tier`, hitung harga sesuai jumlah peserta) dan tab **"Produk UMKM"** (dari `umkm_products`, filter `is_active = true` dan `stock > 0`).
- Admin bisa campur kedua jenis item dalam satu keranjang (misal: 1 tiket tubing + 2 pcs keripik).

### 4.2 Checkout
1. Admin input jumlah bayar → sistem hitung kembalian (`change_amount`).
2. Pilih `payment_method` (cash / qris / transfer).
3. Kalau ada item `paket_wisata` di keranjang → wajib isi minimal `nama_lengkap` + `no_whatsapp` (dipetakan ke `bookings.nama_lengkap` & `no_whatsapp` — kolom ini `NOT NULL` di skema, jadi tetap wajib diisi walau gak dipakai kirim WA, sekadar data pengunjung), tanggal kunjungan default hari ini, sesi dipilih manual.
4. Submit → sistem proses dalam 1 transaksi DB (pakai transaction/rollback):
   - Insert ke `pos_transactions` (status `completed`)
   - Untuk tiap item di keranjang → insert ke `pos_transaction_items`
   - Kalau `item_source = paket_wisata` → **buat row baru di `bookings`**:
     - `status = CONFIRMED` (langsung, karena bayar di tempat)
     - `metode_pembayaran = cash/qris` (ikut payment_method transaksi)
     - `verified_by = user_id` (admin yang transaksi)
     - `verified_at = now()`
     - `bukti_pembayaran_path = NULL` (gak perlu, karena bayar tunai/QRIS langsung di depan admin)
     - `created_by = user_id`
     - link balik: `pos_transaction_items.booking_id` diisi id booking yang baru dibuat
   - Kalau `item_source = umkm_product` → **decrement `umkm_products.stock`** sejumlah `quantity`
5. Cetak struk (invoice_number) via printer thermal — ini pengganti WA, jadi satu-satunya bukti transaksi buat customer.

### 4.3 Pembatalan transaksi (cancel)
- Kalau status diubah jadi `cancelled` (misal salah input):
  - Booking terkait (kalau ada) di-update jadi `status = CANCELLED`
  - Stok `umkm_products` yang tadi dikurangi harus **dikembalikan** (increment lagi sejumlah `quantity`)
  - Catat di `booking_logs` (action: `cancelled_via_pos`)

---

## 5. Keputusan (v1 — biar gak nge-block, bisa direvisi nanti)

1. **Tanggal kunjungan** — default hari ini, tapi tetap disediakan date picker biar admin bisa override (misal ada yang bayar di tempat buat kunjungan besok). Gak butuh logic baru, cuma default value beda dari form booking online.
2. **Kuota sesi** — POS **ikut kuota yang sama** dengan `booking_sessions.kuota` (satu sumber kebenaran). Kalau sesi udah penuh dari booking online, POS juga harus nolak/warning ke admin. Ini paling aman, hindari over-booking guide/slot.
3. **WA e-ticket dari POS** — **tidak ada sama sekali**. Transaksi on-site cukup dibuktikan struk cetak dari printer thermal, gak perlu kirim WA apa pun ke customer. `FonnteService` gak dipanggil sama sekali di alur POS.
4. **`add_ons`** — **di luar scope v1**. Pola datanya nanti bisa nyusul dengan cara yang sama (polymorphic `item_source`), tapi gak usah dipaksain sekarang biar rilis POS + UMKM gak molor.
5. **Refund** — v1 cukup ubah `status = cancelled` + catatan di `notes`, tanpa field `refund_amount` terpisah. Kalau nanti butuh audit lebih detail, gampang ditambahin belakangan tanpa ubah struktur inti.
6. **Laporan per kasir/kategori** — v1 gak perlu tabel/kolom baru. Karena tiap transaksi POS udah nyambung ke `pos_transactions.user_id` (kasir) dan `pos_transaction_items.item_source` (kategori), rekap breakdown bisa dibikin lewat query/laporan biasa nanti, gak butuh pre-agregasi dari awal.

---

## 7. Cetak Struk & Cash Drawer (Thermal Printer via Web Admin)

Karena aksesnya lewat **browser di tablet** (bukan aplikasi native), pendekatan paling gampang & stabil:

### 7.1 Printer thermal
- Setup printer thermal (58mm/80mm) sebagai **printer default di OS tablet** (driver USB/Bluetooth bawaan printer, bukan lewat Laravel).
- Halaman struk di web dibuat sebagai **halaman HTML terpisah** (`/pos/receipt/{invoice_number}`) dengan CSS `@media print` yang di-set lebar sesuai kertas thermal (misal `width: 58mm`), font monospace, margin 0.
- Tombol "Cetak Struk" di admin panggil `window.print()` — browser bakal munculin dialog print, admin tinggal pilih printer thermal yang udah default, langsung cetak. Gak perlu integrasi ESC/POS raw command atau library tambahan di backend.
- Kalau nanti mau full-otomatis tanpa dialog print muncul tiap transaksi (auto-print), itu baru butuh setup tambahan di level OS/browser (kiosk mode Chrome dengan flag `--kiosk-printing`) — **opsional, bisa nyusul**, bukan blocker buat v1.

### 7.2 Cash drawer
- Cash drawer thermal umumnya nyambung ke printer lewat port **RJ11 (kick-out cable)**, bukan ke tablet langsung.
- Artinya laci akan **otomatis kebuka tiap kali printer nerima perintah cetak** (built-in behavior sebagian besar printer thermal POS) — gak butuh kode/integrasi khusus di Laravel/web.
- Perlu dicek di manual printer yang dipakai: apakah kick-drawer aktif default, atau perlu di-enable lewat setting printer/DIP switch. Kalau printer-nya support ESC/POS command khusus buka laci tanpa nyetak (`ESC p`), itu baru butuh fitur tambahan (tombol "Buka Laci" manual di admin) — bisa masuk v2 kalau dibutuhkan.

### 7.3 Ringkasan buat v1
- Cukup: halaman struk HTML print-friendly + tombol print browser biasa. Cash drawer ngikut otomatis lewat printer. Gak perlu library ESC/POS, gak perlu WebUSB, gak perlu service tambahan di server.

### 7.4 (v2 — opsional) Auto-detect Printer Bluetooth & Buka Drawer Tanpa Print
- Web Bluetooth API **tidak bisa dipakai** — cuma support perangkat BLE, sementara printer thermal murah kebanyakan pakai Bluetooth Classic.
- Yang bisa dipakai: **Web Serial API**, yang sudah mendukung Bluetooth Classic (RFCOMM/SPP) — support di Chrome desktop sejak v117, dan di Chrome Android (beta) sejak v148 (April 2026). Alurnya: printer dipairing manual dulu lewat setting Bluetooth OS → web app panggil `navigator.serial.requestPort()` sekali (wajib via klik user) → berikutnya pakai `navigator.serial.getPorts()` buat ambil port yang udah disetujui, cek `port.connected` buat tau mana yang lagi nyambung → kirim byte `[0x1B, 0x70, 0x00, 0x19, 0xFA]` (ESC/POS command buka drawer) lewat `port.writable` kalau mau buka drawer tanpa nyetak struk.
- **Catatan penting**: kalau tabletnya iPad/Safari, cara ini gak bisa dipakai sama sekali (Web Serial gak disupport di iOS). Dan statusnya masih beta di Android, jadi gak dijadiin andalan utama.
- Statusnya: fitur tambahan/nice-to-have, bukan prasyarat buat POS jalan. v1 tetap pakai cara print biasa di 7.3.

---

## 8. Admin Panel Responsif untuk Tablet

Admin/POS diakses lewat tablet, jadi layout harus adaptif ke 2 orientasi. Breakpoint yang dipakai:

| Mode | Lebar CSS (px) | Kisaran skala (density browser) |
|---|---|---|
| **Landscape** (mendatar) | `1280px` | `960px` – `1024px` |
| **Portrait** (tegak) | `800px` | `600px` – `640px` |

### 8.1 Implementasi breakpoint (Tailwind)
Karena breakpoint default Tailwind (`sm/md/lg/xl`) gak pas buat kasus ini, tambahin custom breakpoint di config:
```js
// tailwind.config.js
theme: {
  extend: {
    screens: {
      'tablet-portrait': '600px',   // portrait mulai dari skala terkecil
      'tablet-landscape': '960px',  // landscape mulai dari skala terkecil
    }
  }
}
```
Pakai `min-width` (mobile-first) biar konsisten sama pola Tailwind yang udah ada — style default nempel ke portrait/skala terkecil, terus di-override pas landscape.

### 8.2 Panduan layout per mode

**Portrait (≈600–800px):**
- Layout POS jadi **1 kolom bertumpuk**: katalog produk di atas (grid 2 kolom item), keranjang/cart di bawah sebagai panel yang bisa di-collapse/expand (bottom sheet), bukan sidebar tetap.
- Navigasi admin (sidebar menu) disembunyikan jadi hamburger/drawer, karena lebar gak cukup buat sidebar + konten + cart sekaligus.

**Landscape (≈960–1280px):**
- Layout POS jadi **2 kolom bersebelahan**: katalog produk grid 3–4 kolom di kiri (~65–70% lebar), panel keranjang + checkout tetap nempel (sticky) di kanan (~30–35% lebar) — biar admin bisa liat total & item keranjang tanpa scroll pas milih-milih produk.
- Sidebar menu admin bisa tampil permanen (gak collapse), karena lebar udah cukup.

### 8.3 Touch target & UX tablet
Karena diakses via sentuh (bukan mouse), bukan cuma soal breakpoint:
- Minimal ukuran tombol/elemen yang bisa disentuh: **44×44px** (standar minimum touch target, biar gak salah pencet pas transaksi buru-buru).
- Jarak antar tombol aksi penting (misal tombol "Bayar" vs "Batal") dikasih spacing lebih lebar dari biasanya, hindari numpuk berdekatan — salah pencet pas transaksi cash itu masalah nyata, bukan sekadar UI glitch.
- Input angka (jumlah peserta, nominal bayar) pakai `inputmode="numeric"` biar keyboard yang muncul di tablet langsung numpad, gak full keyboard.
- Font ukuran total harga/kembalian dibesarin (misal `text-2xl`+) karena ini angka yang paling sering dicek cepat sama kasir sambil ngomong ke customer.

### 8.4 Testing
- Wajib dites di kedua orientasi pas rotate tablet beneran (bukan cuma resize browser desktop), karena viewport tablet asli kadang beda dari devtools emulator terutama soal `dvh`/`svh` unit dan notch/safe-area kalau tabletnya ada bezel kamera.

---

## 6. Non-Goals (Di Luar Scope Dokumen Ini)

- Alur booking online yang sudah ada (WA #1–#3c) **tidak berubah** — dokumen ini cuma nambah jalur baru (POS), gak modifikasi flow existing.
- Tidak membahas UI/UX detail tablet, cuma flow data & skema.
- Tidak membahas payment gateway terintegrasi (QRIS dinamis dsb) — asumsi `payment_method` masih dicatat manual oleh kasir.