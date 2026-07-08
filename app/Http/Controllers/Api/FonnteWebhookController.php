<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FonnteWebhook;
use App\Models\TourPackage;
use App\Services\FonnteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FonnteWebhookController extends Controller
{
    public function __invoke(Request $request, FonnteService $fonnte): JsonResponse
    {
        $phone = $request->input('phone');
        $message = $request->input('message');
        $attachment = $request->input('attachment');

        FonnteWebhook::create([
            'phone' => $phone,
            'message' => $message,
            'attachment' => $attachment,
            'event' => $request->input('event'),
            'fonnte_type' => $message ? 'incoming' : 'status',
            'raw_payload' => $request->all(),
        ]);

        if (!$phone || !$message) {
            return response()->json(['status' => 'ignored']);
        }

        $data = $this->extractBookingData($message);

        if (empty($data['nama']) || empty($data['no_wa']) || empty($data['package_name'])) {
            $fonnte->sendMessage($phone, "Maaf, format data booking tidak lengkap.\n\nPastikan format:\nNama: ...\nNo. WA: ...\nPaket: ...\nTanggal: ...\nSesi: ...\nPeserta: ...\nTotal: ...");
            return response()->json(['status' => 'invalid_format']);
        }

        $package = TourPackage::where('is_active', true)
            ->where('nama', 'like', '%' . $data['package_name'] . '%')
            ->first();

        if (!$package) {
            $fonnte->sendMessage($phone, "Maaf, paket \"{$data['package_name']}\" tidak ditemukan. Silakan cek daftar paket wisata yang tersedia.");
            return response()->json(['status' => 'package_not_found']);
        }

        $kodeBooking = 'GB-' . strtoupper(Str::random(8));

        $booking = Booking::create([
            'kode_booking' => $kodeBooking,
            'nama_pemesan' => $data['nama'],
            'no_wa_pemesan' => $data['no_wa'],
            'email' => $data['email'] ?? null,
            'kota_asal' => $data['kota'] ?? '',
            'catatan' => $data['catatan'] ?? null,
            'package_id' => $package->id,
            'tanggal' => $data['tanggal'] ?: now()->toDateString(),
            'sesi' => $data['sesi'] ?: 'Pagi',
            'jumlah_peserta' => max(1, $data['jumlah_peserta']),
            'total_harga' => max(0, $data['total_harga'] ?: $package->harga),
            'status' => 'pending',
            'bukti_bayar' => $attachment,
            'raw_wa_text' => $message,
            'created_by' => 1,
        ]);

        $reply = "✅ *Booking Berhasil!*\n\n"
            . "Kode Booking: *{$kodeBooking}*\n"
            . "Paket: {$package->nama}\n"
            . "Tanggal: {$booking->tanggal}\n"
            . "Sesi: {$booking->sesi}\n"
            . "Peserta: {$booking->jumlah_peserta} orang\n"
            . "Total: Rp " . number_format($booking->total_harga, 0, ',', '.') . "\n\n"
            . "Silakan transfer ke:\n"
            . "Bank BNI 123456789 a.n. Desa Getas\n\n"
            . "Kirimkan bukti transfer ke nomor ini untuk konfirmasi.";

        $fonnte->sendMessage($phone, $reply);

        return response()->json(['status' => 'success', 'booking' => $kodeBooking]);
    }

    private function extractBookingData(string $text): array
    {
        $data = [
            'nama' => '',
            'no_wa' => '',
            'email' => null,
            'kota' => '',
            'catatan' => null,
            'package_name' => '',
            'tanggal' => '',
            'sesi' => '',
            'jumlah_peserta' => 1,
            'total_harga' => 0,
        ];

        foreach (explode("\n", $text) as $raw) {
            $line = trim(preg_replace('/[^\p{L}\p{N}:.@\s\/-]+/u', ' ', $raw));
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if (!$line) continue;

            if (preg_match('/^(?:Paket|Nama\s*Paket|Package|Pack)\s*:\s*(.+)$/iu', $line, $m))
                $data['package_name'] = trim($m[1]);
            elseif (preg_match('/^(?:Tanggal|Tgl|Date|Tangeal)\s*:\s*(.+)$/iu', $line, $m))
                $data['tanggal'] = trim($m[1]);
            elseif (preg_match('/^(?:Sesi|Session|Jam|Waktu)\s*:\s*(.+)$/iu', $line, $m))
                $data['sesi'] = trim($m[1]);
            elseif (preg_match('/^(?:Peserta|Jumlah\s*Peserta|Pax|Orang|Peseta)\s*:\s*(\d+)/iu', $line, $m))
                $data['jumlah_peserta'] = (int) $m[1];
            elseif (preg_match('/^(?:Total|Total\s*Harga|Harga|Price|Biaya)\s*:\s*(?:Rp\.?\s*)?([\d.,]+)/iu', $line, $m))
                $data['total_harga'] = (int) str_replace(['.', ','], '', $m[1]);
            elseif (preg_match('/^(?:Nama|Name|Nama\s*Pemesan)\s*:\s*(.+)$/iu', $line, $m))
                $data['nama'] = trim($m[1]);
            elseif (preg_match('/^(?:WhatsApp|No\.?\s*WA|WA|Phone|Telepon|No\.?\s*HP|HP)\s*:\s*(.+)$/iu', $line, $m))
                $data['no_wa'] = trim($m[1]);
            elseif (preg_match('/^(?:Email|E-mail|Mail|Surel)\s*:\s*(.+)$/iu', $line, $m))
                $data['email'] = trim($m[1]);
            elseif (preg_match('/^(?:Kota|City|Asal|Kota\s*Asal|Domisili)\s*:\s*(.+)$/iu', $line, $m))
                $data['kota'] = trim($m[1]);
            elseif (preg_match('/^(?:Catatan|Note|Pesan|Keterangan)\s*:\s*(.+)$/iu', $line, $m))
                $data['catatan'] = trim($m[1]);
        }

        return $data;
    }
}
