<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use Illuminate\Http\JsonResponse;

class TourPackageController extends Controller
{
    public function index(): JsonResponse
    {
        $packages = TourPackage::with('includes')->where('is_active', true)->get();
        return response()->json($packages);
    }

    public function show($id): JsonResponse
    {
        $package = TourPackage::with('includes')->where('is_active', true)->findOrFail($id);
        return response()->json($package);
    }
}
