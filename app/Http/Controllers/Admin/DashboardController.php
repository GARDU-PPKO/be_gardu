<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dusun;
use App\Models\PaketWisata;
use App\Models\UmkmProduct;
use App\Models\Budaya;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_dusun' => Dusun::count(),
            'total_packages' => PaketWisata::count(),
            'total_bookings' => Booking::count(),
            'total_umkm' => UmkmProduct::count(),
            'total_budaya' => Budaya::count(),
            'total_admins' => User::count(),
            'pending_bookings' => Booking::where('status', Booking::STATUS_PENDING_VERIFY)->count(),
            'recent_bookings' => Booking::with('paketWisata:id,nama')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', $stats);
    }
}
