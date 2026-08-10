<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaketWisata;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TourPackageController extends Controller
{
    /**
     * Ambil daftar paket wisata.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving tour packages"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Tubing Adventure",
     *       "deskripsi": "Menyusuri Sungai Blukar sepanjang 1,5 km.",
     *       "harga": 75000,
     *       "satuan": "orang",
     *       "tag": "Adventure",
     *       "durasi": "±2 jam",
     *       "min_participants": 1,
     *       "max_participants": 10,
     *       "gambar": "https://...",
     *       "is_active": true,
     *       "includes": [
     *         {
     *           "id": 1,
     *           "package_id": 1,
     *           "item": "Pemandu bersertifikat",
     *           "urutan": 1
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $packages = PaketWisata::with('tiers')
            ->where('aktif', true)
            ->orderBy('nama')
            ->get();

        return ApiResponse::success(
            $packages->map(fn (PaketWisata $p) => $this->shape($p))->values(),
            'Success retrieving tour packages'
        );
    }

    /**
     * Ambil detail paket wisata.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving tour package detail"
     *   },
     *   "data": {
     *     "id": 1,
     *     "nama": "Tubing Adventure",
     *     "deskripsi": "...",
     *     "harga": 75000,
     *     "satuan": "orang",
     *     "tag": "Adventure",
     *     "durasi": "±2 jam",
     *     "min_participants": 1,
     *     "max_participants": 10,
     *     "gambar": "https://...",
     *     "is_active": true,
     *     "includes": []
     *   }
     * }
     */
    public function show($id): JsonResponse
    {
        $package = PaketWisata::with('tiers')->where('aktif', true)->findOrFail($id);

        return ApiResponse::success($this->shape($package), 'Success retrieving tour package detail');
    }

    private function shape(PaketWisata $p): array
    {
        $tiers = $p->tiers->sortBy('min_peserta')->values();

        $isPerOrang = $p->tipe_harga === 'per_orang_tier';

        $minParticipants = $isPerOrang
            ? ($tiers->first()?->min_peserta ?? 1)
            : 1;

        $harga = $isPerOrang
            ? (float) ($tiers->first()?->harga_per_orang ?? 0)
            : (float) ($p->harga_paket ?? 0);

        $includes = collect($p->fasilitas ?? [])
            ->values()
            ->map(fn ($item, $i) => [
                'id' => $i + 1,
                'package_id' => $p->id,
                'item' => (string) $item,
                'urutan' => $i + 1,
            ])
            ->values();

        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'deskripsi' => $p->deskripsi,
            'harga' => $harga,
            'satuan' => $isPerOrang ? 'orang' : 'paket',
            'tag' => $p->tag,
            'durasi' => $p->durasi,
            'min_participants' => $minParticipants,
            'max_participants' => $isPerOrang ? ($tiers->last()?->min_peserta ?? $minParticipants) : ($p->kapasitas_per_unit ?? $minParticipants),
            'gambar' => $p->gambar,
            'is_active' => (bool) $p->aktif,
            'includes' => $includes,
        ];
    }
}
