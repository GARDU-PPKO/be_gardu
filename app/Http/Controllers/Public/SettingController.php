<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Ambil daftar pengaturan umum desa.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving settings"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "key": "nama_desa",
     *       "value": "Desa Wisata Getas",
     *       "deskripsi": "Nama desa wisata utama"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query()->orderBy('id');

        if ($request->filled('keys')) {
            $keys = explode(',', $request->string('keys')->toString());
            $query->whereIn('key', array_map('trim', $keys));
        }

        $settings = $query->get()->map(fn (Setting $s) => [
            'id' => $s->id,
            'key' => $s->key,
            'value' => $s->value,
            'deskripsi' => $s->deskripsi,
        ])->values();

        return ApiResponse::success($settings, 'Success retrieving settings');
    }
}
