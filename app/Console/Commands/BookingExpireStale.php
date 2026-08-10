<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingLog;
use Illuminate\Console\Command;

class BookingExpireStale extends Command
{
    protected $signature = 'booking:expire-stale {--hours=24 : Batas umur booking PENDING_PAYMENT sebelum di-expire}';

    protected $description = 'Auto-expire booking yang menggantung (PENDING_PAYMENT lebih dari N jam)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $stale = Booking::where('status', Booking::STATUS_PENDING_PAYMENT)
            ->where('created_at', '<', now()->subHours($hours))
            ->get();

        if ($stale->isEmpty()) {
            $this->info('Tidak ada booking menggantung.');

            return self::SUCCESS;
        }

        foreach ($stale as $booking) {
            $booking->update(['status' => Booking::STATUS_EXPIRED]);

            BookingLog::create([
                'booking_id' => $booking->id,
                'admin_id' => null,
                'action' => 'expired',
                'detail' => "Auto-expire: tidak mengunggah bukti dalam {$hours} jam.",
                'created_at' => now(),
            ]);
        }

        $this->info("{$stale->count()} booking di-expire.");

        return self::SUCCESS;
    }
}
