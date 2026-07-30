<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\VillageStat;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Statistik Desa')]
class VillageStatController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Statistik Desa')]
    public function index(): JsonResponse
    {
        $stats = VillageStat::where('is_active', true)->orderBy('urutan')->get();
        return $this->success($stats);
    }
}
