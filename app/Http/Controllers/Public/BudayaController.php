<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Budaya;
use Illuminate\Http\JsonResponse;

class BudayaController extends Controller
{
    public function index(): JsonResponse
    {
        $budaya = Budaya::with('schedules')->where('is_active', true)->get();
        return response()->json($budaya);
    }

    public function show($id): JsonResponse
    {
        $budaya = Budaya::with('schedules')->where('is_active', true)->findOrFail($id);
        return response()->json($budaya);
    }
}
