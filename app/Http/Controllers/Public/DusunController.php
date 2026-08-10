<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DusunController extends Controller
{
    /**
     * Ambil daftar dusun untuk slider.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving dusun list"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Seklotok",
     *       "rw": "RW 01",
     *       "jumlah_rt": 3,
     *       "jumlah_penduduk": 412,
     *       "luas_wilayah": "1,2 km²",
     *       "deskripsi": "Dusun di tepi sungai dengan sawah hijau membentang luas.",
     *       "thumbnail": "https://...",
     *       "hero_img": "https://...",
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $dusun = Dusun::where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->map(fn (Dusun $d) => $this->listShape($d))
            ->values();

        return ApiResponse::success($dusun, 'Success retrieving dusun list');
    }

    /**
     * Ambil detail dusun termasuk galeri dan keunggulan.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving dusun detail"
     *   },
     *   "data": {
     *     "id": 1,
     *     "nama": "Seklotok",
     *     "rw": "RW 01",
     *     "jumlah_rt": 3,
     *     "jumlah_penduduk": 412,
     *     "luas_wilayah": "1,2 km²",
     *     "deskripsi": "...",
     *     "thumbnail": "https://...",
     *     "hero_img": "https://...",
     *     "is_active": true,
     *     "detail": "...",
     *     "galleries": [
     *       {
     *         "id": 11,
     *         "dusun_id": 1,
     *         "image_url": "https://...",
     *         "urutan": 1
     *       }
     *     ],
     *     "keunggulan": [
     *       {
     *         "id": 101,
     *         "dusun_id": 1,
     *         "keunggulan": "Sawah organik tepi sungai",
     *         "urutan": 1
     *       }
     *     ]
     *   }
     * }
     */
    public function show($id): JsonResponse
    {
        $dusun = Dusun::with(['galleries', 'keunggulan'])
            ->where('is_active', true)
            ->findOrFail($id);

        return ApiResponse::success($this->detailShape($dusun), 'Success retrieving dusun detail');
    }

    private function listShape(Dusun $d): array
    {
        return [
            'id' => $d->id,
            'nama' => $d->nama,
            'rw' => $d->rw,
            'jumlah_rt' => $d->jumlah_rt,
            'jumlah_penduduk' => $d->jumlah_penduduk,
            'luas_wilayah' => $d->luas_wilayah,
            'deskripsi' => $d->deskripsi,
            'thumbnail' => $d->thumbnail,
            'hero_img' => $d->hero_img,
            'is_active' => (bool) $d->is_active,
        ];
    }

    private function detailShape(Dusun $d): array
    {
        return [
            ...$this->listShape($d),
            'detail' => $d->detail,
            'galleries' => $d->galleries->sortBy('urutan')->values()->map(fn ($g) => [
                'id' => $g->id,
                'dusun_id' => $g->dusun_id,
                'image_url' => $g->image_url,
                'urutan' => $g->urutan,
            ]),
            'keunggulan' => $d->keunggulan->sortBy('urutan')->values()->map(fn ($k) => [
                'id' => $k->id,
                'dusun_id' => $k->dusun_id,
                'keunggulan' => $k->keunggulan,
                'urutan' => $k->urutan,
            ]),
        ];
    }
}
