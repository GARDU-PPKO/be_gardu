<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Budaya;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BudayaController extends Controller
{
    /**
     * Ambil daftar kebudayaan beserta jadwal event-nya.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving budaya list"
     *   },
     *   "data": [
     *     {
     *       "id": 1,
     *       "judul": "Kuda Lumping",
     *       "kategori": "Seni Pertunjukan",
     *       "deskripsi": "Tarian tradisional...",
     *       "gambar": "https://...",
     *       "span_grid": 2,
     *       "is_active": true,
     *       "schedules": [
     *         {
     *           "id": 1,
     *           "budaya_id": 1,
     *           "nama_acara": "Kuda Lumping Suroan",
     *           "hari": "Sabtu",
     *           "jam": "09.00 - 15.00 WIB",
     *           "deskripsi": "Pentas utama di Dusun Sanggar",
     *           "is_active": true
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $budaya = Budaya::with('schedules')
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (Budaya $b) => $this->shape($b))
            ->values();

        return ApiResponse::success($budaya, 'Success retrieving budaya list');
    }

    /**
     * Ambil detail kebudayaan beserta jadwal event-nya.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving budaya detail"
     *   },
     *   "data": {
     *     "id": 1,
     *     "judul": "Kuda Lumping",
     *     "kategori": "Seni Pertunjukan",
     *     "deskripsi": "Tarian tradisional...",
     *     "gambar": "https://...",
     *     "span_grid": 2,
     *     "is_active": true,
     *     "schedules": []
     *   }
     * }
     */
    public function show($id): JsonResponse
    {
        $budaya = Budaya::with('schedules')->where('is_active', true)->findOrFail($id);

        return ApiResponse::success($this->shape($budaya), 'Success retrieving budaya detail');
    }

    private function shape(Budaya $b): array
    {
        return [
            'id' => $b->id,
            'judul' => $b->judul,
            'kategori' => $b->kategori,
            'deskripsi' => $b->deskripsi,
            'gambar' => $b->gambar,
            'span_grid' => $b->span_grid,
            'is_active' => (bool) $b->is_active,
            'schedules' => $b->schedules
                ->where('is_active', true)
                ->sortBy('id')
                ->values()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'budaya_id' => $s->budaya_id,
                    'nama_acara' => $s->nama_acara,
                    'hari' => $s->hari,
                    'jam' => $s->jam,
                    'deskripsi' => $s->deskripsi,
                    'is_active' => (bool) $s->is_active,
                ]),
        ];
    }
}
