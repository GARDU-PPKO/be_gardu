<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingSessionController extends Controller
{
    /**
     * Cek sisa kuota sesi pada tanggal dan paket tertentu.
     *
     * @queryParam package_id int required ID paket wisata. Example: 1
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
     *       "sesi": "Pagi",
     *       "kuota": 20,
     *       "terisi": 5,
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_id' => 'required|integer|exists:paket_wisata,id',
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        $sessions = BookingSession::where('paket_wisata_id', $data['package_id'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (BookingSession $session) => [
                'id' => $session->id,
                'package_id' => $session->paket_wisata_id,
                'tanggal' => $data['tanggal'],
                'sesi' => $session->sesi,
                'jam_mulai' => $session->jam_mulai,
                'jam_selesai' => $session->jam_selesai,
                'kuota' => $session->kuota,
                'terisi' => $session->terisiPadaTanggal($data['tanggal']),
                'is_active' => (bool) $session->is_active,
            ])
            ->values();

        return ApiResponse::success($sessions, 'Success retrieving booking sessions');
    }
}
