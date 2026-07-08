<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FonnteService;
use Illuminate\View\View;

class FonnteController extends Controller
{
    public function device(FonnteService $fonnte): View
    {
        $device = $fonnte->checkQuota();

        return view('admin.fonnte.device', compact('device'));
    }
}
