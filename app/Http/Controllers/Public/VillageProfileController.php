<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageProfile;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Profil Desa')]
class VillageProfileController extends Controller
{
    #[Endpoint('Daftar Profil Desa')]
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
