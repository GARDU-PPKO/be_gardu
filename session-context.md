# SESSION CONTEXT — be_gardu

> Dokumen ini dibuat agar konteks diskusi tidak hilang (laptop bisa mati sewaktu-waktu).
> Update file ini SETIAP kali ada keputusan/perubahan penting.

## Proyek
- **BE:** `F:\Developments\be_gardu` — Laravel (backend API) Desa Wisata Getas (GARDU).
- **FE:** terpisah; URL frontend disimpan di setting `fe_url` (`http://localhost:5713`).
- **Dok API:** Scramble di `/docs/api` (`config/scramble.php`, `api_path = api`). 18 path terdaftar.
- **DB seeded:** add_ons=4, booking_sessions=3, settings=12, bookings=8, paket_wisata=5.

## Status booking (enum DB → public)
`PENDING_PAYMENT`→`pending_payment` | `PENDING_VERIFY`→`pending_verify` | `CONFIRMED`→`confirmed`
| `REJECTED`→`rejected` | `EXPIRED`→`expired` | `COMPLETED`→`completed` | `CANCELLED`→`cancelled`

## Yang SUDAH selesai (implementasi booking flow publik)
1. **Booking tanpa login** (`routes/api.php`):
   - `POST /api/bookings` — submit data diri → `pending_payment`, `expired_at`=+24 jam; hitung `total_harga` server-side (paket + add-ons); **anti-duplikat 409** (paket+tanggal+sesi+no WA sama, `pending_payment` aktif).
   - `GET /api/bookings/{kode}` — detail (`detailShape`: payment_info, package, addons).
   - `POST /api/bookings/{kode}/bukti` + `GET .../bukti` (stream file).
   - `PATCH /api/bookings/{kode}` — edit data diri (blokir via `isFinalStatus`).
   - `PATCH /api/bookings/{kode}/cancel` (hanya pending_payment/pending_verify).
   - `POST /api/bookings/{kode}/resend-wa` (hanya pending_payment).
   - `GET /api/bookings/check?kode=|phone=`; `GET /api/bookings?phone=` (riwayat, pagination).
2. **WA (backend, FonnteService):** #1 link payment (create), #2 bukti diterima + notif admin (upload), #3c auto-expire `php artisan booking:expire-stale`.
3. **DB/migrasi:** `expired_at` (backfill created_at+1 hari), `urutan` di add_ons.
4. **Add-ons:** `GET /api/addons`; integrasi hitung total booking; CRUD admin + field `urutan`.
5. **`GET /api/home`** agregat (settings, village_stats, dusun, tour_packages, umkm_products, budaya).
6. **Sesi** dikembalikan label penuh `"Pagi (08.00 - 11.00)"` (BookingSessionController).
7. **Setting::setValue()** ditambahkan di model Setting.
8. **Test:** `AddOnTest` + `BookingFlowTest` → **20 pass, 116 assertions** (`php artisan test`).
9. `API_EXPECTED_RESPONSES.md` sudah memuat kontrak baru.

## GOTCHA (jangan dilanggar)
- **Jangan POST live `/api/bookings`** — setting `fonnte_token` aktif → akan kirim WA asli ke nomor test.
- **SQLite menyimpan tanggal + waktu** (`2026-08-12 00:00:00`) → pakai `whereDate()` untuk pencocokan tanggal (sudah dipakai di anti-dup & `BookingSession::terisiPadaTanggal`).
- **Seeder TIDAK idempotent** (User/PaketWisata/Dusun pakai `::create`) → JANGAN `db:seed` ulang (duplikat). Settings & add-ons aman (firstOrCreate).
- Fonnte tidak dikonfigurasi (token/wa_admin kosong) → send() hanya log warning, tidak throw.

## KEPUTUSAN TERBARU (sesi terakhir)
**Re-upload bukti setelah ditolak admin: TIDAK ADA.** Reject = final; user harus booking ulang.
Gap yang ditemukan:
- WA reject admin (buildRejectMessage) saat ini malah menyuruh "unggah ulang" → harus diganti jadi "booking ulang".
- `detailShape` belum mengembalikan `rejected_reason` → FE tidak bisa tampilkan alasan.

### TODO berikutnya
1. `BookingController::detailShape` + tambah `rejected_reason` & `rejected_at` (null jika tidak ditolak) + update `@response`. ✅ SELESAI
2. `AdminBookingController::buildRejectMessage` — WA #3b: "Booking ditolak karena <alasan>. Silakan booking ulang di <fe_url>". ✅ SELESAI
3. Test baru di `BookingFlowTest`: (a) detail REJECTED berisi `rejected_reason`; (b) upload bukti REJECTED → 422; (c) PATCH data diri REJECTED → 422. ✅ SELESAI
4. Update `API_EXPECTED_RESPONSES.md` (bagian 4, tabel WA #3b, catatan reject=final). ✅ SELESAI
5. `php artisan test` semua hijau. ✅ SELESAI (23 pass, 125 assertions)

### Hasil sesi terakhir
- `detailShape` kini mengembalikan `rejected_reason` & `rejected_at` (terverifikasi muncul di Scramble).
- WA #3b (reject) diubah: tidak lagi menyuruh re-upload, jadi "Silakan lakukan booking ulang di {fe_url}/booking".
- `history` memakai `orderByDesc('created_at')` + `orderByDesc('id')` (tie-breaker agar deterministik di SQLite).
- Test bertambah 3 (total 23 pass, 125 assertions).

## TEMPLATE WA FINAL (sudah diterapkan)
Keputusan user: WA #1 murni template (tanpa rekening & rincian add-on), TANPA `nomor_cs`, TANPA `meeting_point`/`jam_kumpul`.

- **WA #1** (`BookingController::buildPaymentInstructionMessage`): sapa 👋 → kode/paket/tanggal/peserta/total → link `fe_url/payment/{kode}` → batas 24 jam (sampai `expired_at` d-m-Y H:i) → "booking akan otomatis dibatalkan" → terima kasih 🙏.
- **WA #2** (`BookingController::buildUserMessage`): bukti diterima ✅ → verifikasi admin maks 1x10 jam kerja → "Tidak perlu upload ulang atau booking baru".
- **WA #3a** (`AdminBookingController::buildConfirmMessage`): dikonfirmasi ✅ → kode/paket/tanggal → "Silahkan datang sesuai jadwal booking Anda" → "Sampai jumpa di Desa Wisata Getas! 🌿".
- **WA #3b** (`AdminBookingController::buildRejectMessage`): *tidak dapat kami verifikasi* ❌ → Alasan → "Mohon maaf atas ketidaknyamanannya 🙏". (Tanpa link/CS; reject = final.)
- **WA #3c** (`BookingExpireStale::buildExpiredMessage`): *dibatalkan otomatis* (24 jam) → booking ulang via `fe_url` → terima kasih 🙏.

Catatan: `buildAddOnMessage` dihapus (dead code). `use App\Models\Setting;` dihapus dari AdminBookingController, ditambah di BookingExpireStale.

## Setting fe_url
- Nilai DB kini `http://localhost:5173` (diupdate via tinker). DatabaseSeeder sudah disamakan.
- `fe_url` dipakai untuk: link payment WA #1, link website WA #3c.

## Catatan teknis lain
- Scramble annotations: sebagian besar endpoint sudah punya `@response`/`@bodyParam`. Yang masih generik: `check` (response), `uploadBukti` (response), `showBukti` (stream).
- `RestrictedDocsAccess` middleware → di production docs bisa terkunci ke localhost (perlu dicek nanti).
- File penting: `app/Http/Controllers/Public/BookingController.php` (core flow), `app/Services/FonnteService.php`, `app/Models/Booking.php`, `app/Support/ApiResponse.php`.
