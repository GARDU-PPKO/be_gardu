<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class BookingExpireStale extends Command
{
    protected $signature = 'booking:expire-stale';

    protected $description = 'Auto-expire booking PENDING_PAYMENT yang lewat expired_at';

    public function handle(): int
    {
        $stale = Booking::where('status', Booking::STATUS_PENDING_PAYMENT)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->get();

        if ($stale->isEmpty()) {
            $this->info('Tidak ada booking menggantung.');

            return self::SUCCESS;
        }

        $fonnte = app(FonnteService::class);

        foreach ($stale as $booking) {
            $booking->update(['status' => Booking::STATUS_EXPIRED]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'admin_id' => null,
                'action' => 'expired',
                'detail' => 'Auto-expire: melewati batas waktu pembayaran (expired_at).',
                'created_at' => now(),
            ]);

            $fonnte->send($booking->no_whatsapp, $this->buildExpiredMessage($booking));
        }

        $this->info("{$stale->count()} booking di-expire.");

        return self::SUCCESS;
    }

    private function buildExpiredMessage(Booking $booking): string
    {
        $feUrl = Setting::getValue('fe_url') ?: url('/');

        return "Halo {$booking->nama_lengkap},\n\n"
            . "Booking dengan kode *{$booking->booking_code}* telah *dibatalkan otomatis* karena pembayaran tidak diselesaikan dalam batas waktu 24 jam.\n\n"
            . "Tidak masalah! Jika masih tertarik, silahkan lakukan booking ulang melalui website kami:\n"
            . "🔗 {$feUrl}\n\n"
            . "Terima kasih 🙏";
    }
}
