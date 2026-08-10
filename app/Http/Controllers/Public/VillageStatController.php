<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageStat;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Statistik Desa')]
class VillageStatController extends Controller
{
    #[Endpoint('Daftar Statistik Desa')]
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
