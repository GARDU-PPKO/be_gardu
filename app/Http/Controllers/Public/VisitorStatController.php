<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorStatController extends Controller
{
    /**
     * Ambil data statistik total pengunjung website.
     */
    public function index(): JsonResponse
    {
        $today = now()->toDateString();
        $totalLogs = VisitorLog::count();
        $todayLogs = VisitorLog::where('visited_date', $today)->count();

        $baseSetting = Setting::where('key', 'base_visitor_count')->first();
        $baseCount = $baseSetting ? (int) $baseSetting->value : 0;

        return response()->json([
            'total_visitors' => $baseCount + $totalLogs,
            'today_visitors' => $todayLogs,
        ]);
    }

    /**
     * Catat kunjungan pengunjung baru dan kembalikan total terkini.
     */
    public function track(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $ip = $request->ip();
        $sessionId = $request->input('session_id');

        $existsQuery = VisitorLog::where('visited_date', $today);
        if ($sessionId) {
            $existsQuery->where('session_id', $sessionId);
        } else if ($ip) {
            $existsQuery->where('ip_address', $ip);
        }

        if (!$existsQuery->exists()) {
            VisitorLog::create([
                'ip_address' => $ip,
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'session_id' => $sessionId,
                'visited_date' => $today,
            ]);
        }

        $totalLogs = VisitorLog::count();
        $todayLogs = VisitorLog::where('visited_date', $today)->count();

        $baseSetting = Setting::where('key', 'base_visitor_count')->first();
        $baseCount = $baseSetting ? (int) $baseSetting->value : 0;

        return response()->json([
            'total_visitors' => $baseCount + $totalLogs,
            'today_visitors' => $todayLogs,
        ]);
    }
}
