<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private const API_URL = 'https://api.fonnte.com/send';

    private ?string $token;
    private ?string $adminNumber;

    public function __construct()
    {
        $this->token = Setting::getValue('fonnte_token') ?: config('fonnte.token');
        $this->adminNumber = Setting::getValue('wa_admin') ?: null;
    }

    /**
     * Normalisasi nomor HP Indonesia ke format internasional (08xx -> 628xx).
     */
    public static function normalizeNumber(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }

        return $number;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->token);
    }

    public function getAdminNumber(): ?string
    {
        return $this->adminNumber;
    }

    /**
     * Kirim pesan WhatsApp. Gagal apapun hanya dicatat di log, tidak melempar exception.
     */
    public function send(string $target, string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Fonnte tidak dikonfigurasi (fonnte_token kosong). Pesan tidak terkirim.', [
                'target' => $target,
            ]);

            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->token])
                ->asForm()
                ->post(self::API_URL, [
                    'target' => self::normalizeNumber($target),
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('Fonnte gagal mengirim pesan.', [
                    'target' => $target,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Fonnte exception saat mengirim pesan.', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendMessage(string $target, string $message, ?string $attachment = null): array
    {
        $success = $this->send($target, $message);
        return ['status' => $success ? 'success' : 'failed'];
    }

    public function checkQuota(): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'skipped', 'message' => 'Token Fonnte belum dikonfigurasi'];
        }

        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->get('https://api.fonnte.com/device');

        if ($response->failed()) {
            Log::error('Fonnte device check failed', ['response' => $response->body()]);
            return ['status' => 'error', 'message' => 'Gagal mengambil data device'];
        }

        return $response->json() ?? [];
    }

    /**
     * Kirim notifikasi ke nomor admin (wa_admin di settings).
     */
    public function notifyAdmin(string $message): bool
    {
        if (! $this->adminNumber) {
            return false;
        }

        return $this->send($this->adminNumber, $message);
    }
}
