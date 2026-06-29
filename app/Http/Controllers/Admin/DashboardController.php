<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dusun;
use App\Models\TourPackage;
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
            'total_packages' => TourPackage::count(),
            'total_bookings' => Booking::count(),
            'total_umkm' => UmkmProduct::count(),
            'total_budaya' => Budaya::count(),
            'total_admins' => User::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'recent_bookings' => Booking::with('package:id,nama')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', $stats);
    }
}
