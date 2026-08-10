<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageProfile;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VillageProfileController extends Controller
{    /**
     * Ambil profil desa (sejarah, visi, misi, pemerintahan).
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving village profile"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "tipe": "sejarah",
     *       "judul": "Sejarah Desa Getas",
     *       "konten": "<p>Desa Getas didirikan pada tahun...</p>",
     *       "urutan": 1,
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $profiles = VillageProfile::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(fn (VillageProfile $p) => [
                'id' => $p->id,
                'tipe' => $p->tipe,
                'judul' => $p->judul,
                'konten' => $p->konten,
                'urutan' => $p->urutan,
                'is_active' => (bool) $p->is_active,
            ])
            ->values();

        return ApiResponse::success($profiles, 'Success retrieving village profile');
    }
}
