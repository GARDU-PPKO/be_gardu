<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageStat;
use Illuminate\Http\JsonResponse;

class VillageStatController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = VillageStat::where('is_active', true)->orderBy('urutan')->get();
        return response()->json($stats);
    }
}
