<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    /**
     * Ambil daftar add-on yang tersedia untuk booking.
     *
     * @queryParam package_id int|null Opsional, add-on global dipakai untuk semua paket.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving add-ons"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Makan Siang (Nasi Box)",
     *       "harga": 25000,
     *       "satuan": "per orang",
     *       "deskripsi": "Nasi box + lauk pauk + air mineral untuk satu peserta.",
     *       "gambar": "https://...",
     *       "is_free": false,
     *       "is_active": true,
     *       "urutan": 1
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'package_id' => 'nullable|integer|exists:paket_wisata,id',
        ]);

        $addOns = AddOn::where('aktif', true)
            ->orderByRaw("CASE WHEN urutan IS NULL THEN 1 ELSE 0 END")
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return ApiResponse::success(
            $addOns->map(fn (AddOn $a) => $this->shape($a))->values(),
            'Success retrieving add-ons'
        );
    }

    private function shape(AddOn $a): array
    {
        return [
            'id' => $a->id,
            'nama' => $a->nama,
            'harga' => (float) $a->harga,
            'satuan' => $a->labelSatuan(),
            'deskripsi' => $a->deskripsi,
            'gambar' => $a->gambar,
            'is_free' => $a->isFree(),
            'is_active' => (bool) $a->aktif,
            'urutan' => $a->urutan,
        ];
    }
}
