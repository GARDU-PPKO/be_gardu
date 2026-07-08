<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected ?string $token;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = config('fonnte.token') ?: null;
        $this->baseUrl = config('fonnte.base_url');
    }

    public function sendMessage(string $target, string $message, ?string $attachment = null): array
    {
        if (!$this->token) {
            Log::info('Fonnte skipped (no token)', ['target' => $target]);
            return ['status' => 'skipped'];
        }

        $payload = [
            'target' => $target,
            'message' => $message,
            'countryCode' => '62',
        ];

        if ($attachment) {
            $payload['attachment'] = $attachment;
        }

        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post("{$this->baseUrl}/send", $payload);

        if ($response->failed()) {
            Log::error('Fonnte send failed', [
                'target' => $target,
                'response' => $response->body(),
            ]);
        }

        return $response->json() ?? [];
    }

    public function checkQuota(): array
    {
        if (!$this->token) {
            return ['status' => 'skipped', 'message' => 'Token Fonnte belum dikonfigurasi'];
        }

        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->get("{$this->baseUrl}/device");

        if ($response->failed()) {
            Log::error('Fonnte device check failed', ['response' => $response->body()]);
            return ['status' => 'error', 'message' => 'Gagal mengambil data device'];
        }

        return $response->json() ?? [];
    }
}
