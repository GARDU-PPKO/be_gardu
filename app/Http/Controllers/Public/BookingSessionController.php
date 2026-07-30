<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\BookingSession;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Sesi Booking')]
class BookingSessionController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Sesi Booking')]
    public function index(): JsonResponse
    {
        return $this->success(BookingSession::where('is_active', true)->get());
    }
}
