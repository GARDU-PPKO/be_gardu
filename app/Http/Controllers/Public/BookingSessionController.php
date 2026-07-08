<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BookingSession;
use Illuminate\Http\JsonResponse;

class BookingSessionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(BookingSession::where('is_active', true)->get());
    }
}
