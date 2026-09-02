<?php

namespace App\Console\Commands;

use App\Services\ReviewRequestService;
use Illuminate\Console\Command;

class SendReviewRequests extends Command
{
    protected $signature = 'booking:send-review-requests';

    protected $description = 'Otomatis kirim link review WhatsApp ke pengunjung yang telah selesai berkunjung';

    public function handle(ReviewRequestService $service): int
    {
        $this->info('Memeriksa kunjungan yang telah selesai...');

        $count = $service->autoSendReviewRequests();

        if ($count > 0) {
            $this->info("Berhasil mengirim {$count} link ulasan ke WhatsApp pengunjung.");
        } else {
            $this->info('Tidak ada pengunjung yang perlu dikirimi link ulasan saat ini.');
        }

        return self::SUCCESS;
    }
}
