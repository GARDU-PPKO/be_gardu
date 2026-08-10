<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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

class BookingController extends Controller
{
    /**
     * Buat booking baru (submit form data diri, tanpa bukti pembayaran).
     *
     * @bodyParam package_id int required ID paket wisata. Example: 1
     * @bodyParam customer_name string required Nama pemesan. Example: Budi Santoso
     * @bodyParam phone string required Nomor WhatsApp valid. Example: 6281234567890
     * @bodyParam email string|null Email opsional. Example: budi@gmail.com
     * @bodyParam date string required Tanggal kunjungan (YYYY-MM-DD). Example: 2026-08-10
     * @bodyParam session_time string required Sesi lengkap. Example: Pagi (07.00 - 09.00)
     * @bodyParam participants int required Jumlah peserta. Example: 3
     * @bodyParam notes string|null Catatan tambahan. Example: Tidak ada alergi
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "201",
     *     "message": "Booking successfully created"
     *   },
     *   "data": {
     *     "id": 42,
     *     "kode_booking": "GTS-827394",
     *     "total_harga": 225000,
     *     "status": "pending"
     *   }
     * }
     *
     * @response status=422 scenario="Validation error" {
     *   "meta": {
     *     "success": false,
     *     "status_code": "422",
     *     "message": "Validation Error"
     *   },
     *   "errors": {
     *     "phone": ["The phone field must be a valid WhatsApp number."],
     *     "participants": ["The participants must be at least 1."]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_id' => 'required|integer|exists:paket_wisata,id',
            'customer_name' => 'required|string|max:100',
            'phone' => [
                'required', 'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('/^(0|62)[0-9]{8,15}$/', $value)) {
                        $fail('The phone field must be a valid WhatsApp number.');
                    }
                },
            ],
            'email' => 'nullable|email|max:150',
            'date' => 'required|date|after_or_equal:today',
            'session_time' => 'required|string|max:100',
            'participants' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ], [
            'phone.regex' => 'The phone field must be a valid WhatsApp number.',
        ]);

        $paket = PaketWisata::where('aktif', true)->find($data['package_id']);
        if (! $paket) {
            return ApiResponse::error('Paket wisata tidak ditemukan.', 422);
        }

        $sesi = $this->extractSession($data['session_time']);

        $session = BookingSession::where('paket_wisata_id', $paket->id)
            ->where('sesi', $sesi)
            ->where('is_active', true)
            ->first();

        if (! $session) {
            return ApiResponse::error('Sesi yang dipilih tidak tersedia.', 422);
        }

        if ($data['participants'] > $session->sisaPadaTanggal($data['date'])) {
            return ApiResponse::error(
                'Kuota sesi tidak mencukupi untuk jumlah peserta tersebut.',
                422,
                ['participants' => ["Sisa kuota sesi ini hanya {$session->sisaPadaTanggal($data['date'])} orang."]]
            );
        }

        try {
            $total = $paket->hitungTotalHarga($data['participants']);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        $booking = Booking::create([
            'booking_code' => Booking::generateBookingCode(),
            'nama_lengkap' => $data['customer_name'],
            'no_whatsapp' => $data['phone'],
            'email' => $data['email'] ?? null,
            'alamat' => null,
            'kontak_darurat_nama' => null,
            'kontak_darurat_telp' => null,
            'notes' => $data['notes'] ?? null,
            'jumlah_peserta' => $data['participants'],
            'tanggal_kunjungan' => $data['date'],
            'paket_wisata_id' => $paket->id,
            'sesi' => $sesi,
            'total_harga' => $total['total'],
            'status' => Booking::STATUS_PENDING_PAYMENT,
        ]);

        app(FonnteService::class)->send($booking->no_whatsapp, $this->buildPaymentInstructionMessage($booking));

        return ApiResponse::success([
            'id' => $booking->id,
            'kode_booking' => $booking->booking_code,
            'total_harga' => (float) $booking->total_harga,
            'status' => $this->publicStatus($booking->status),
        ], 'Booking successfully created', 201);
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
        $uploadUrl = rtrim($feUrl, '/') . "/booking/upload/{$booking->booking_code}";
        $rekening = Setting::getValue('rekening_bank') ?: '-';

        return "Halo {$booking->nama_lengkap}, pendaftaran booking GARDU berhasil!\n"
            . "Kode: {$booking->booking_code}\n"
            . "Paket: {$booking->paketWisata?->nama}\n"
            . "Tanggal: {$booking->tanggal_kunjungan->format('d-m-Y')} ({$booking->sesi})\n"
            . "Peserta: {$booking->jumlah_peserta} orang\n"
            . "Total: Rp" . number_format((float) $booking->total_harga, 0, ',', '.') . "\n\n"
            . "Silakan transfer ke:\n{$rekening}\n\n"
            . "Setelah transfer, upload bukti pembayaran dalam 24 jam melalui link berikut:\n"
            . "{$uploadUrl}\n\n"
            . "Jika dalam 24 jam bukti tidak diupload, booking otomatis dibatalkan.";
    }

    /**
     * Upload bukti pembayaran untuk booking.
     *
     * @bodyParam bukti file required File bukti (jpg/jpeg/png/pdf, max 3MB).
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
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'nominal_transfer' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|max:50',
        ]);

        $path = $buktiService->simpan($request->file('bukti'));

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
            'kode_booking' => $booking->booking_code,
            'status' => $this->publicStatus($booking->status),
            'status_url' => url("/api/bookings/{$booking->booking_code}"),
        ], 'Bukti pembayaran berhasil diunggah. Status: menunggu verifikasi admin.');
    }

    /**
     * Cek status booking berdasarkan kode booking.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success"
     *   },
     *   "data": {
     *     "kode_booking": "GRD-20260805-A3F1",
     *     "status": "pending",
     *     "customer_name": "Budi Santoso",
     *     "paket": "GENTA Explorer",
     *     "tanggal": "2026-08-10",
     *     "sesi": "Pagi",
     *     "participants": 3,
     *     "total_harga": 225000,
     *     "bukti_terupload": false,
     *     "rejected_reason": null,
     *     "verified_at": null
     *   }
     * }
     */
    public function show(string $bookingCode): JsonResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->with('paketWisata:id,nama')
            ->first();

        if (! $booking) {
            return ApiResponse::error('Booking tidak ditemukan.', 404);
        }

        return ApiResponse::success([
            'kode_booking' => $booking->booking_code,
            'status' => $this->publicStatus($booking->status),
            'customer_name' => $booking->nama_lengkap,
            'paket' => $booking->paketWisata?->nama,
            'tanggal' => $booking->tanggal_kunjungan->toDateString(),
            'sesi' => $booking->sesi,
            'participants' => $booking->jumlah_peserta,
            'total_harga' => (float) $booking->total_harga,
            'bukti_terupload' => $booking->bukti_pembayaran_path !== null,
            'rejected_reason' => $booking->rejected_reason,
            'verified_at' => $booking->verified_at?->toIso8601String(),
        ]);
    }

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
        ], 'Booking berhasil dibatalkan.');
    }

    private function publicStatus(string $status): string
    {
        return match ($status) {
            Booking::STATUS_PENDING_PAYMENT => 'pending',
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
        return "Terima kasih {$booking->nama_lengkap}!\n"
            . "Bukti pembayaran untuk booking {$booking->booking_code} telah kami terima.\n\n"
            . "Tim admin akan melakukan verifikasi manual. Mohon tunggu notifikasi selanjutnya ya.";
    }
}
