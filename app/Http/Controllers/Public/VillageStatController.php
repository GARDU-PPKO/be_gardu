<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageStat;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VillageStatController extends Controller
{
    /**
     * Ambil statistik/indikator desa untuk banner hero.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving village stats"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "label": "Wisatawan / Tahun",
     *       "nilai": "8.500",
     *       "satuan": "+",
     *       "icon": "Mountain",
     *       "urutan": 1,
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $stats = VillageStat::where('is_active', true)
            ->orderBy('urutan')
            ->get()
            ->map(fn (VillageStat $s) => [
                'id' => $s->id,
                'label' => $s->label,
                'nilai' => $s->nilai,
                'satuan' => $s->satuan,
                'icon' => $s->icon,
                'urutan' => $s->urutan,
                'is_active' => (bool) $s->is_active,
            ])
            ->values();

        return ApiResponse::success($stats, 'Success retrieving village stats');
    }
}
