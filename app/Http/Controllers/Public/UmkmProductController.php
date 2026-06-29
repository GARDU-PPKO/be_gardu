<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UmkmProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UmkmProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UmkmProduct::where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        return response()->json($query->get());
    }
}
