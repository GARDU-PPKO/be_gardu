<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\BookingSession;
use App\Models\PaketWisata;
use App\Models\Setting;
use App\Services\BuktiPembayaranService;
use App\Services\FonnteService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    private const PAYMENT_DEADLINE_HOURS = 24;

    /**
     * Buat booking baru (submit form data diri, tanpa bukti pembayaran).
     *
     * @bodyParam package_id int required ID paket wisata. Example: 1
     * @bodyParam customer_name string required Nama pemesan. Example: Budi Santoso
     * @bodyParam phone string required Nomor WhatsApp valid. Example: 6281234567890
     * @bodyParam email string|null Email opsional. Example: budi@gmail.com
     * @bodyParam kontak_darurat string|null Nomor kontak darurat. Example: 6289876543210
     * @bodyParam date string required Tanggal kunjungan (YYYY-MM-DD). Example: 2026-08-10
     * @bodyParam session_time string required Sesi lengkap. Example: Pagi (08.00 - 11.00)
     * @bodyParam participants int required Jumlah peserta. Example: 3
     * @bodyParam addons array|null Daftar add-on yang dipilih [{id, quantity?}]. quantity diabaikan untuk add-on per orang. Example: [{"id":1,"quantity":3}]
     * @bodyParam notes string|null Catatan tambahan. Example: Tidak ada alergi
     *
     * @response status=201 {
     *   "meta": {"success": true, "status_code": "201", "message": "Booking successfully created"},
     *   "data": {
     *     "id": 42,
     *     "kode_booking": "GTS-827394",
     *     "total_harga": 275000,
     *     "status": "pending_payment",
     *     "expired_at": "2026-08-10 08:00:00"
     *   }
     * }
     *
     * @response status=409 scenario="Duplicate pending payment" {
     *   "meta": {"success": false, "status_code": "409", "message": "..."},
     *   "data": { "kode_booking": "GTS-827394", ... }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $addons = $request->input('addons');
        if (is_array($addons)) {
            foreach ($addons as $i => $addon) {
                if (is_array($addon) && isset($addon['qty']) && ! isset($addon['quantity'])) {
                    $addons[$i]['quantity'] = $addon['qty'];
                }
            }
            $request->merge(['addons' => $addons]);
        }

        $data = $request->validate([
            'package_id' => 'required|integer|exists:paket_wisata,id',
            'customer_name' => 'required|string|min:3|max:100',
            'phone' => [
                'required', 'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $trimmed = trim($value);
                    $isValidPrefix = str_starts_with($trimmed, '08') || str_starts_with($trimmed, '628') || str_starts_with($trimmed, '+628');
                    $digitsOnly = preg_replace('/\D/', '', $trimmed);
                    if (! $isValidPrefix || strlen($digitsOnly) < 10 || strlen($digitsOnly) > 15) {
                        $fail('Nomor WhatsApp harus diawali 08 (contoh: 081234567890).');
                    }
                },
            ],
            'email' => 'nullable|email|max:150',
            'kontak_darurat' => 'nullable|string|min:3|max:100',
            'date' => 'required|date|after_or_equal:today',
            'session_time' => 'required|string|max:100',
            'participants' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'addons' => 'nullable|array',
            'addons.*.id' => 'required|integer|exists:add_ons,id',
            'addons.*.quantity' => 'nullable|integer|min:1',
        ], [
            'phone.regex' => 'The phone field must be a valid WhatsApp number.',
        ]);

        $paket = PaketWisata::where('aktif', true)->find($data['package_id']);
        if (! $paket) {
            return ApiResponse::error('Paket wisata tidak ditemukan.', 422);
        }

        $sesi = $this->extractSession($data['session_time']);

        $session = BookingSession::where('sesi', $sesi)
            ->where('is_active', true)
            ->first();

        if (! $session) {
            return ApiResponse::error('Sesi yang dipilih tidak tersedia.', 422);
        }

        $existing = Booking::where('status', Booking::STATUS_PENDING_PAYMENT)
            ->where('no_whatsapp', $data['phone'])
            ->where('paket_wisata_id', $data['package_id'])
            ->whereDate('tanggal_kunjungan', $data['date'])
            ->where('sesi', $sesi)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->first();

        if ($existing) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status_code' => '409',
                    'message' => 'Booking aktif sudah ada untuk paket, tanggal, sesi, dan nomor WhatsApp yang sama.',
                ],
                'data' => $this->detailShape($existing->load(['paketWisata', 'addOns'])),
            ], 409);
        }

        $sisaKuota = $session->sisaPadaTanggal($data['date']);

        if ($sisaKuota !== null && $data['participants'] > $sisaKuota) {
            return ApiResponse::error(
                'Kuota sesi tidak mencukupi untuk jumlah peserta tersebut.',
                422,
                ['participants' => ["Sisa kuota sesi ini hanya {$sisaKuota} orang."]]
            );
        }

        try {
            $total = $paket->hitungTotalHarga($data['participants']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        $addOnResult = $this->processAddOns($data['addons'] ?? [], $data['participants']);

        if (! $addOnResult['ok']) {
            return ApiResponse::error($addOnResult['message'], 422, $addOnResult['errors']);
        }

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => $data['customer_name'],
            'no_whatsapp' => $data['phone'],
            'email' => $data['email'] ?? null,
            'alamat' => null,
            'kontak_darurat_nama' => null,
            'kontak_darurat_telp' => $data['kontak_darurat'] ?? null,
            'notes' => $data['notes'] ?? null,
            'jumlah_peserta' => $data['participants'],
            'tanggal_kunjungan' => $data['date'],
            'paket_wisata_id' => $paket->id,
            'sesi' => $sesi,
            'total_harga' => $total['total'] + $addOnResult['total'],
            'status' => Booking::STATUS_PENDING_PAYMENT,
            'expired_at' => now()->addHours(self::PAYMENT_DEADLINE_HOURS),
        ]);

        foreach ($addOnResult['items'] as $item) {
            $booking->addOns()->attach($item['add_on_id'], [
                'qty' => $item['quantity'],
                'harga_satuan' => $item['harga'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        app(FonnteService::class)->send($booking->no_whatsapp, $this->buildPaymentInstructionMessage($booking));

        return ApiResponse::success([
            'id' => $booking->id,
            'kode_booking' => $booking->booking_code,
            'total_harga' => (float) $booking->total_harga,
            'status' => $this->publicStatus($booking->status),
            'expired_at' => $booking->expired_at?->toIso8601String(),
        ], 'Booking successfully created', 201);
    }

    /**
     * Validasi dan hitung total add-on yang dipilih user.
     *
     * @return array{ok: bool, message: string, errors: array, total: float, items: array}
     */
    private function processAddOns(array $addOns, int $participants): array
    {
        $items = [];
        $total = 0.0;
        $errors = [];

        foreach ($addOns as $index => $addOn) {
            $model = AddOn::where('aktif', true)->find($addOn['id']);

            if (! $model) {
                $errors["addons.{$index}.id"] = ['Add-on tidak ditemukan atau tidak aktif.'];

                continue;
            }

            $qty = $model->tipe_harga === 'per_orang'
                ? $participants
                : (int) ($addOn['quantity'] ?? $addOn['qty'] ?? 1);

            if ($qty < 1) {
                $errors["addons.{$index}.quantity"] = ['Quantity minimal 1.'];

                continue;
            }

            $subtotal = (float) $model->harga * $qty;
            $total += $subtotal;

            $items[] = [
                'add_on_id' => $model->id,
                'nama' => $model->nama,
                'harga' => (float) $model->harga,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        if ($errors !== []) {
            return ['ok' => false, 'message' => 'Ada add-on yang tidak valid.', 'errors' => $errors, 'total' => 0.0, 'items' => []];
        }

        return ['ok' => true, 'message' => '', 'errors' => [], 'total' => $total, 'items' => $items];
    }

    private function extractSession(string $sessionTime): string
    {
        $sesi = strtoupper(trim(explode('(', $sessionTime)[0]));

        if (in_array($sesi, ['PAGI', 'SIANG', 'SORE'], true)) {
            return ucfirst(strtolower($sesi));
        }

        return trim(explode('(', $sessionTime)[0]) ?: $sessionTime;
    }

    private function buildPaymentInstructionMessage(Booking $booking): string
    {
        $feUrl = Setting::getValue('fe_url') ?: url('/');
        $paymentUrl = rtrim($feUrl, '/') . "/payment/{$booking->booking_code}";
        $batasWaktu = $booking->expired_at?->format('d-m-Y H:i') ?? '24 jam';

        return "Halo {$booking->nama_lengkap}! 👋\n\n"
            . "Terima kasih sudah booking di Desa Wisata Getas.\n\n"
            . "📋 Kode Booking: {$booking->booking_code}\n"
            . "🏕️ Paket: {$booking->paketWisata?->nama}\n"
            . "📅 Tanggal: {$booking->tanggal_kunjungan->format('d-m-Y')}\n"
            . "👥 Jumlah Peserta: {$booking->jumlah_peserta}\n"
            . "💰 Total Bayar: Rp" . number_format((float) $booking->total_harga, 0, ',', '.') . "\n\n"
            . "Silahkan selesaikan pembayaran melalui link berikut:\n"
            . "🔗 {$paymentUrl}\n\n"
            . "⏰ Batas waktu pembayaran: *" . self::PAYMENT_DEADLINE_HOURS . " jam* dari sekarang (sampai {$batasWaktu}).\n"
            . "Jika melewati batas waktu, booking akan otomatis dibatalkan.\n\n"
            . "Terima kasih! 🙏";
    }

    /**
     * Cek status & detail booking berdasarkan kode.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving booking detail"
     *   },
     *   "data": {
     *     "id": 42,
     *     "kode_booking": "GTS-827394",
     *     "nama_pemesan": "Budi Santoso",
     *     "no_wa_pemesan": "6281234567890",
     *     "kontak_darurat": "6289876543210",
     *     "kota_asal": "Semarang",
     *     "catatan": "Tidak ada alergi",
     *     "tanggal": "2026-08-10",
     *     "sesi": "Pagi (08.00 - 11.00)",
     *     "jumlah_peserta": 3,
     *     "total_harga": 275000,
     *     "status": "pending_payment",
     *     "expired_at": "2026-08-09 23:59:59",
     *     "rejected_reason": null,
     *     "rejected_at": null,
     *     "bukti_bayar": null,
     *     "payment_info": {
     *       "bank": "BRI",
     *       "nomor_rekening": "0012 3456 7890",
     *       "atas_nama": "Desa Wisata Getas",
     *       "qris_image": "https://.../qris.png",
     *       "batas_waktu_jam": 24
     *     },
     *     "package": {
     *       "id": 1,
     *       "nama": "Tubing Adventure",
     *       "durasi": "±2 jam",
     *       "gambar": "https://...",
     *       "satuan": "orang"
     *     },
     *     "addons": [
     *       {
     *         "id": 1,
     *         "nama": "Makan Siang",
     *         "harga": 25000,
     *         "quantity": 3
     *       }
     *     ]
     *   }
     * }
     */
    public function show(string $bookingCode): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->with(['paketWisata', 'addOns'])
            ->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        return ApiResponse::success($this->detailShape($booking), 'Success retrieving booking detail');
    }

    /**
     * Cari booking by kode atau nomor WhatsApp.
     *
     * @queryParam kode string|null Kode booking.
     * @queryParam phone string|null Nomor WhatsApp.
     */
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $kode = $data['kode'] ?? null;
        $phone = $data['phone'] ?? null;

        if (! $kode && ! $phone) {
            return ApiResponse::error('Minimal isi kode booking atau nomor WhatsApp.', 422);
        }

        $query = Booking::with(['paketWisata', 'addOns']);

        if ($kode) {
            $query->where('booking_code', $kode);
        } else {
            $query->where('no_whatsapp', $phone)->latest('created_at');
        }

        $booking = $query->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        return ApiResponse::success($this->detailShape($booking), 'Success retrieving booking detail');
    }

    /**
     * Upload bukti pembayaran untuk booking.
     *
     * @bodyParam bukti_bayar file required File bukti (jpg/jpeg/png/pdf, max 5MB).
     * @bodyParam nominal_transfer number|null Nominal transfer.
     * @bodyParam metode_pembayaran string|null Metode pembayaran.
     */
    public function uploadBukti(Request $request, string $bookingCode, BuktiPembayaranService $buktiService): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            return ApiResponse::error('Booking tidak dalam status menunggu pembayaran.', 422);
        }

        $request->validate([
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'nominal_transfer' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:50',
        ]);

        $file = $request->file('bukti_bayar') ?? $request->file('bukti');

        if (! $file) {
            return ApiResponse::error('Field bukti_bayar wajib diisi.', 422, [
                'bukti_bayar' => ['Field bukti_bayar wajib diisi.'],
            ]);
        }

        $path = $buktiService->simpan($file);

        $booking->update([
            'bukti_pembayaran_path' => $path,
            'nominal_transfer' => $request->filled('nominal_transfer') ? $request->nominal_transfer : null,
            'metode_pembayaran' => $request->metode_pembayaran ?? null,
            'status' => Booking::STATUS_PENDING_VERIFY,
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => null,
            'action' => 'upload_bukti',
            'detail' => 'Bukti pembayaran diunggah. Menunggu verifikasi admin.',
            'created_at' => now(),
        ]);

        $fonnte = app(FonnteService::class);
        $fonnte->send($booking->no_whatsapp, $this->buildUserMessage($booking));
        $fonnte->notifyAdmin("Booking baru menunggu verifikasi.\nKode: {$booking->booking_code}\nNama: {$booking->nama_lengkap}\nPaket: {$booking->paketWisata?->nama}\nTotal: Rp" . number_format((float) $booking->total_harga, 0, ',', '.'));

        return ApiResponse::success([
            'id' => $booking->id,
            'kode_booking' => $booking->booking_code,
            'status' => $this->publicStatus($booking->status),
            'bukti_bayar' => url("/api/bookings/{$booking->booking_code}/bukti"),
        ], 'Payment proof uploaded');
    }

    /**
     * Stream file bukti pembayaran (publik, tanpa login).
     */
    public function showBukti(string $bookingCode): StreamedResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->first();

        abort_unless($booking?->bukti_pembayaran_path, 404);
        abort_unless(Storage::disk('local')->exists($booking->bukti_pembayaran_path), 404);

        return Storage::disk('local')->response($booking->bukti_pembayaran_path);
    }

    /**
     * Update data diri booking (edit di cek-pesanan).
     */
    public function update(Request $request, string $bookingCode): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        if ($booking->isFinalStatus()) {
            return ApiResponse::error('Booking tidak dapat diubah pada status ini.', 422);
        }

        $data = $request->validate([
            'customer_name' => 'nullable|string|min:3|max:100',
            'phone' => [
                'nullable', 'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $trimmed = trim($value);
                    if ($trimmed === '') return;
                    $isValidPrefix = str_starts_with($trimmed, '08') || str_starts_with($trimmed, '628') || str_starts_with($trimmed, '+628');
                    $digitsOnly = preg_replace('/\D/', '', $trimmed);
                    if (! $isValidPrefix || strlen($digitsOnly) < 10 || strlen($digitsOnly) > 15) {
                        $fail('Nomor WhatsApp harus diawali 08 (contoh: 081234567890).');
                    }
                },
            ],
            'kontak_darurat' => 'nullable|string|min:3|max:100',
        ]);

        if (! $request->filled('customer_name') && ! $request->filled('phone') && ! $request->filled('kontak_darurat')) {
            return ApiResponse::error('Minimal satu field data diri wajib diisi.', 422);
        }

        $booking->update([
            'nama_lengkap' => $data['customer_name'] ?? $booking->nama_lengkap,
            'no_whatsapp' => $data['phone'] ?? $booking->no_whatsapp,
            'kontak_darurat_telp' => $data['kontak_darurat'] ?? $booking->kontak_darurat_telp,
        ]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => null,
            'action' => 'updated',
            'detail' => 'Data diri diperbarui oleh user.',
            'created_at' => now(),
        ]);

        $booking->load(['paketWisata', 'addOns']);

        return ApiResponse::success($this->detailShape($booking), 'Booking updated');
    }

    /**
     * Batalkan booking.
     */
    public function cancel(string $bookingCode): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        if (! in_array($booking->status, [Booking::STATUS_PENDING_PAYMENT, Booking::STATUS_PENDING_VERIFY], true)) {
            return ApiResponse::error('Booking tidak dapat dibatalkan pada status ini.', 422);
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        BookingLog::create([
            'booking_id' => $booking->id,
            'admin_id' => null,
            'action' => 'cancelled',
            'detail' => 'Booking dibatalkan oleh user.',
            'created_at' => now(),
        ]);

        return ApiResponse::success([
            'kode_booking' => $booking->booking_code,
            'status' => $this->publicStatus($booking->status),
        ], 'Booking cancelled');
    }

    /**
     * Kirim ulang link pembayaran (WA #1) untuk booking pending_payment.
     */
    public function resendWa(string $bookingCode): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT) {
            return ApiResponse::error('Link pembayaran hanya bisa dikirim ulang untuk booking menunggu pembayaran.', 422);
        }

        app(FonnteService::class)->send($booking->no_whatsapp, $this->buildPaymentInstructionMessage($booking));

        return ApiResponse::success([
            'kode_booking' => $booking->booking_code,
        ], 'Payment link resent');
    }

    /**
     * Riwayat booking berdasarkan nomor WhatsApp.
     *
     * @queryParam phone string required Nomor WhatsApp.
     * @queryParam page int|null Halaman. Example: 1
     * @queryParam limit int|null Jumlah per halaman. Example: 10
     */
    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $limit = max(1, min(100, (int) $request->integer('limit', 10)));

        $paginator = Booking::with('paketWisata:id,nama')
            ->where('no_whatsapp', $data['phone'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($limit)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Booking $b) => [
                'id' => $b->id,
                'kode_booking' => $b->booking_code,
                'nama_pemesan' => $b->nama_lengkap,
                'tanggal' => $b->tanggal_kunjungan->toDateString(),
                'sesi' => $this->sesiLabelForBooking($b),
                'jumlah_peserta' => $b->jumlah_peserta,
                'total_harga' => (float) $b->total_harga,
                'status' => $this->publicStatus($b->status),
                'package' => $b->paketWisata ? [
                    'id' => $b->paketWisata->id,
                    'nama' => $b->paketWisata->nama,
                ] : null,
            ])
        );

        return ApiResponse::paginated($paginator, 'Success retrieving booking history');
    }

    /**
     * Shape lengkap detail booking sesuai kontrak FE.
     */
    private function detailShape(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'kode_booking' => $booking->booking_code,
            'nama_pemesan' => $booking->nama_lengkap,
            'no_wa_pemesan' => $booking->no_whatsapp,
            'kontak_darurat' => $booking->kontak_darurat_telp,
            'kota_asal' => $booking->alamat,
            'catatan' => $booking->notes,
            'tanggal' => $booking->tanggal_kunjungan->toDateString(),
            'sesi' => $this->sesiLabelForBooking($booking),
            'jumlah_peserta' => $booking->jumlah_peserta,
            'total_harga' => (float) $booking->total_harga,
            'status' => $this->publicStatus($booking->status),
            'expired_at' => $booking->expired_at?->toIso8601String(),
            'rejected_reason' => $booking->rejected_reason,
            'rejected_at' => $booking->verified_at?->toIso8601String(),
            'bukti_bayar' => $booking->bukti_pembayaran_path
                ? url("/api/bookings/{$booking->booking_code}/bukti")
                : null,
            'payment_info' => [
                'bank' => Setting::getValue('rekening_bank'),
                'nomor_rekening' => Setting::getValue('rekening_no'),
                'atas_nama' => Setting::getValue('rekening_atas_nama'),
                'qris_image' => Setting::getValue('qris_image'),
                'batas_waktu_jam' => self::PAYMENT_DEADLINE_HOURS,
            ],
            'package' => $booking->paketWisata ? [
                'id' => $booking->paketWisata->id,
                'nama' => $booking->paketWisata->nama,
                'durasi' => $booking->paketWisata->durasi,
                'gambar' => $booking->paketWisata->gambar,
                'satuan' => $booking->paketWisata->tipe_harga === 'per_orang_tier' ? 'orang' : 'paket',
            ] : null,
            'addons' => $booking->addOns->map(fn ($addOn) => [
                'id' => $addOn->id,
                'nama' => $addOn->nama,
                'harga' => (float) $addOn->pivot->harga_satuan,
                'quantity' => $addOn->pivot->qty,
            ])->values(),
        ];
    }

    private function sesiLabelForBooking(Booking $booking): string
    {
        $session = BookingSession::where('sesi', $booking->sesi)->first();

        if (! $session) {
            return $booking->sesi;
        }

        return $this->sesiLabel($session);
    }

    private function sesiLabel(BookingSession $session): string
    {
        $mulai = $session->jam_mulai ? \Illuminate\Support\Carbon::parse($session->jam_mulai)->format('H.i') : null;
        $selesai = $session->jam_selesai ? \Illuminate\Support\Carbon::parse($session->jam_selesai)->format('H.i') : null;

        if ($mulai && $selesai) {
            return "{$session->sesi} ({$mulai} - {$selesai})";
        }

        return $session->sesi;
    }

    private function publicStatus(string $status): string
    {
        return match ($status) {
            Booking::STATUS_PENDING_PAYMENT => 'pending_payment',
            Booking::STATUS_PENDING_VERIFY => 'pending_verify',
            Booking::STATUS_CONFIRMED => 'confirmed',
            Booking::STATUS_REJECTED => 'rejected',
            Booking::STATUS_EXPIRED => 'expired',
            Booking::STATUS_COMPLETED => 'completed',
            Booking::STATUS_CANCELLED => 'cancelled',
            default => strtolower($status),
        };
    }

    private function buildUserMessage(Booking $booking): string
    {
        return "Halo {$booking->nama_lengkap},\n\n"
            . "Bukti pembayaran untuk booking dengan kode *{$booking->booking_code}* sudah kami terima ✅\n\n"
            . "Saat ini bukti pembayaran sedang dalam proses verifikasi oleh admin kami. Mohon ditunggu maksimal *1x10 jam kerja* ya (bisa lebih cepat, tergantung antrian verifikasi atau kendala teknis dari pihak bank).\n\n"
            . "Kami akan kirim kabar melalui WhatsApp ini begitu verifikasi selesai. Tidak perlu upload ulang atau booking baru.\n\n"
            . "Terima kasih atas kesabarannya 🙏";
    }
}
