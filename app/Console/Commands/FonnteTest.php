<?php

namespace App\Console\Commands;

use App\Services\FonnteService;
use Illuminate\Console\Command;

class FonnteTest extends Command
{
    protected $signature = 'fonnte:test {target? : Nomor tujuan (opsional, default nomor admin di settings)} {--message=Test pesan GARDU : Isi pesan}';

    protected $description = 'Kirim pesan WA tes via Fonnte';

    public function handle(FonnteService $fonnte): int
    {
        if (! $fonnte->isConfigured()) {
            $this->error('Fonnte belum dikonfigurasi. Isi "fonnte_token" dan "wa_admin" di menu Pengaturan.');

            return self::FAILURE;
        }

        $target = $this->argument('target') ?: $fonnte->getAdminNumber();
        $sent = $fonnte->send($target, $this->option('message'));

        if ($sent) {
            $this->info("Pesan terkirim ke {$target}");

            return self::SUCCESS;
        }

        $this->error('Gagal mengirim. Cek log untuk detail.');

        return self::FAILURE;
    }
}
