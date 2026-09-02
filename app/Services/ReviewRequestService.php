<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\BookingSession;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReviewRequestService
{
    /**
     * Otomatis kirim undangan ulasan WA ke pengunjung yang telah menyelesaikan kunjungannya.
     *
     * @return int Jumlah pesan WhatsApp yang berhasil dikirim
     */
    public function autoSendReviewRequests(): int
    {
        $today = now()->toDateString();
        $currentTime = now()->format('H:i');

        // Ambil data booking yang siap dikirimkan link ulasannya
        $eligibleBookings = Booking::with(['paketWisata'])
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->whereNull('review_invitation_sent_at')
            ->whereNull('reviewed_at')
            ->whereDate('tanggal_kunjungan', '<=', $today)
            ->get();

        if ($eligibleBookings->isEmpty()) {
            return 0;
        }

        // Cache sesi untuk mencocokkan jam selesai
        $sessions = BookingSession::all()->keyBy('sesi');

        $fonnte = app(FonnteService::class);
        $sentCount = 0;

        foreach ($eligibleBookings as $booking) {
            $isPastDate = $booking->tanggal_kunjungan->format('Y-m-d') < $today;
            $isToday = $booking->tanggal_kunjungan->format('Y-m-d') === $today;

            $shouldSend = false;

            if ($isPastDate) {
                // Kunjungan sudah lewat hari -> langsung kirim
                $shouldSend = true;
            } elseif ($isToday) {
                // Kunjungan hari ini -> periksa apakah sesi kunjungan sudah selesai
                $sesiName = trim(explode('(', (string) $booking->sesi)[0]);
                $sessionObj = $sessions->get($sesiName) ?? $sessions->first(fn ($s) => str_contains(strtolower($booking->sesi), strtolower($s->sesi)));

                $endTime = $sessionObj?->jam_selesai ?? match (strtolower($sesiName)) {
                    'pagi' => '11:00',
                    'siang' => '14:00',
                    'sore' => '16:00',
                    default => '15:00',
                };

                // Berikan jeda 15 menit setelah sesi selesai agar pengunjung sudah bersiap pulang
                $endCarbon = Carbon::createFromTimeString($endTime)->addMinutes(15)->format('H:i');

                if ($currentTime >= $endCarbon) {
                    $shouldSend = true;
                }
            }

            if (! $shouldSend) {
                continue;
            }

            // Generate token ulasan jika belum ada
            if (! $booking->review_token) {
                $booking->update(['review_token' => Booking::generateUniqueReviewToken()]);
                $booking->refresh();
            }

            // Update status dan waktu kirim
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'review_invitation_sent_at' => now(),
            ]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'admin_id' => null,
                'action' => 'review_requested',
                'detail' => 'Otomatis: Link ulasan dikirim via WhatsApp pasca kunjungan wisata.',
                'created_at' => now(),
            ]);

            $message = $this->buildReviewMessage($booking);
            $fonnte->send($booking->no_whatsapp, $message);
            $sentCount++;
        }

        return $sentCount;
    }

    public function buildReviewMessage(Booking $booking): string
    {
        $feUrl = rtrim(Setting::getValue('fe_url') ?: config('app.frontend_url', 'http://localhost:5173'), '/');
        $reviewUrl = "{$feUrl}/review/{$booking->review_token}";
        $packageName = $booking->paketWisata->nama ?? 'Paket Wisata';

        return "Halo *{$booking->nama_lengkap}*! 👋\n\n"
            . "Terima kasih banyak telah berkunjung dan berpetualang di Desa Wisata Getas (*{$packageName}*)! 🌿✨\n\n"
            . "Bagaimana kesan dan pengalaman serumu hari ini? Kami sangat menghargai ulasan dan masukan dari kamu agar kami dapat terus memberikan pelayanan terbaik.\n\n"
            . "Yuk luangkan waktu 1 menit untuk memberikan penilaian dan ulasan melalui link berikut:\n"
            . "👉 {$reviewUrl}\n\n"
            . "Ulasanmu sangat berarti bagi kemajuan wisata desa kami. Sampai jumpa di petualangan seru berikutnya! 🙏😊\n\n"
            . "— *Pengelola Desa Wisata Getas*";
    }
}
