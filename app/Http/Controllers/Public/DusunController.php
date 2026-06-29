<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use Illuminate\Http\JsonResponse;

class DusunController extends Controller
{
    public function index(): JsonResponse
    {
        $dusun = Dusun::with(['galleries', 'keunggulan'])->where('is_active', true)->get();
        return response()->json($dusun);
    }

    public function show($id): JsonResponse
    {
        $dusun = Dusun::with(['galleries', 'keunggulan'])->where('is_active', true)->findOrFail($id);
        return response()->json($dusun);
    }
}
