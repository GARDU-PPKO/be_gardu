<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->filled('keys')) {
            $keys = explode(',', $request->keys);
            $query->whereIn('key', $keys);
        }

        return response()->json($query->get());
    }
}
