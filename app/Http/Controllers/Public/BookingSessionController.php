<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Sesi Booking')]
class BookingSessionController extends Controller
{
    /**
     * Cek sisa kuota sesi pada tanggal tertentu.
     *
     * @queryParam package_id int|null ID paket wisata (sesi bersifat global, di-echo untuk kompatibilitas).
     * @queryParam tanggal string required Tanggal kunjungan (YYYY-MM-DD). Example: 2026-08-10
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving booking sessions"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "package_id": 1,
     *       "tanggal": "2026-08-10",
     *       "sesi": "Pagi (08.00 - 11.00)",
     *       "kuota": 30,
     *       "terisi": 5,
     *       "sisa_kuota": 25,
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        if (! $request->has('package_id') && ! $request->has('tanggal')) {
            $sessions = BookingSession::where('is_active', true)->get();
            return ApiResponse::success($sessions, 'Success retrieving booking sessions');
        }

        $data = $request->validate([
            'package_id' => 'nullable|integer|exists:paket_wisata,id',
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        $sessions = BookingSession::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (BookingSession $session) => [
                'id' => $session->id,
                'package_id' => $data['package_id'] ?? null,
                'tanggal' => $data['tanggal'],
                'sesi' => $this->sesiLabel($session),
                'kuota' => $session->kuota,
                'terisi' => $session->terisiPadaTanggal($data['tanggal']),
                'sisa_kuota' => $session->sisaPadaTanggal($data['tanggal']),
                'is_active' => (bool) $session->is_active,
            ])
            ->values();

        return ApiResponse::success($sessions, 'Success retrieving booking sessions');
    }

    /**
     * Format label sesi penuh: "Pagi (08.00 - 11.00)".
     */
    private function sesiLabel(BookingSession $session): string
    {
        $mulai = $session->jam_mulai ? \Illuminate\Support\Carbon::parse($session->jam_mulai)->format('H.i') : null;
        $selesai = $session->jam_selesai ? \Illuminate\Support\Carbon::parse($session->jam_selesai)->format('H.i') : null;

        if ($mulai && $selesai) {
            return "{$session->sesi} ({$mulai} - {$selesai})";
        }

        return $session->sesi;
    }
}
