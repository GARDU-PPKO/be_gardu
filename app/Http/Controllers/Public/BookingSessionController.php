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
    #[Endpoint('Daftar / Cek Kuota Sesi Booking')]
    public function index(Request $request): JsonResponse
    {
        if (! $request->has('package_id') && ! $request->has('tanggal')) {
            $sessions = BookingSession::where('is_active', true)->get();
            return ApiResponse::success($sessions, 'Success retrieving booking sessions');
        }

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
                'kuota' => $session->kuota,
                'terisi' => $session->terisiPadaTanggal($data['tanggal']),
                'is_active' => (bool) $session->is_active,
            ])
            ->values();

        return ApiResponse::success($sessions, 'Success retrieving booking sessions');
    }
}
