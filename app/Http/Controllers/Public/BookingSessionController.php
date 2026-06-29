<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BookingSession::where('is_active', true);

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        $sessions = $query->with('package:id,nama')->get();

        return response()->json($sessions);
    }
}
