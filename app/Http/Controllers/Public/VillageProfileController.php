<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VillageProfile;
use Illuminate\Http\JsonResponse;

class VillageProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $profiles = VillageProfile::where('is_active', true)->orderBy('urutan')->get();
        return response()->json($profiles);
    }
}
