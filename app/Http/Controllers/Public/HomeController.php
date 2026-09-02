<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Budaya;
use App\Models\Dusun;
use App\Models\PaketWisata;
use App\Models\Setting;
use App\Models\UmkmProduct;
use App\Models\VillageStat;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Ambil seluruh data landing page dalam satu request.
     *
     * @response {
     *   "meta": {
     *     "success": true,
     *     "status_code": "200",
     *     "message": "Success retrieving home data"
     *   },
     *   "data": {
     *     "settings": [],
     *     "village_stats": [],
     *     "dusun": [],
     *     "tour_packages": [],
     *     "umkm_products": [],
     *     "budaya": []
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'settings' => Setting::query()->orderBy('id')->get()->map(fn (Setting $s) => [
                'id' => $s->id,
                'key' => $s->key,
                'value' => $s->value,
                'deskripsi' => $s->deskripsi,
            ])->values(),
            'village_stats' => VillageStat::where('is_active', true)
                ->orderBy('urutan')
                ->get()
                ->map(fn (VillageStat $s) => [
                    'id' => $s->id,
                    'label' => $s->label,
                    'nilai' => $s->nilai,
                    'satuan' => $s->satuan,
                    'icon' => $s->icon,
                    'urutan' => $s->urutan,
                    'is_active' => (bool) $s->is_active,
                ])->values(),
            'dusun' => Dusun::where('is_active', true)
                ->orderBy('nama')
                ->get()
                ->map(fn (Dusun $d) => [
                    'id' => $d->id,
                    'nama' => $d->nama,
                    'rw' => $d->rw,
                    'deskripsi' => $d->deskripsi,
                    'thumbnail' => $d->thumbnail,
                    'hero_img' => $d->hero_img,
                    'is_active' => (bool) $d->is_active,
                ])->values(),

            'tour_packages' => PaketWisata::with('tiers')
                ->where('aktif', true)
                ->orderBy('nama')
                ->get()
                ->map(fn (PaketWisata $p) => $this->tourPackageShape($p))
                ->values(),
            'umkm_products' => UmkmProduct::where('is_active', true)
                ->orderBy('id')
                ->get()
                ->map(fn (UmkmProduct $u) => [
                    'id' => $u->id,
                    'nama' => $u->nama,
                    'kategori' => $u->kategori,
                    'harga' => (float) $u->harga,
                    'deskripsi' => $u->deskripsi,
                    'gambar' => $u->gambar,
                    'no_wa_penjual' => $u->no_wa_penjual,
                    'is_active' => (bool) $u->is_active,
                ])->values(),
            'budaya' => Budaya::with('schedules')
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
                ->map(fn (Budaya $b) => [
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
                        ])->values(),
                ])->values(),
        ], 'Success retrieving home data');
    }

    private function tourPackageShape(PaketWisata $p): array
    {
        $tiers = $p->tiers->sortBy('min_peserta')->values();
        $isPerOrang = $p->tipe_harga === 'per_orang_tier';

        $minParticipants = $isPerOrang ? ($tiers->first()?->min_peserta ?? 1) : 1;
        $harga = $isPerOrang
            ? (float) ($tiers->first()?->harga_per_orang ?? 0)
            : (float) ($p->harga_paket ?? 0);

        $tierList = $tiers->map(fn ($t) => [
            'id' => $t->id,
            'min_peserta' => (int) $t->min_peserta,
            'harga_per_orang' => (float) $t->harga_per_orang,
        ])->values()->all();

        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'deskripsi' => $p->deskripsi,
            'tipe_harga' => $p->tipe_harga,
            'harga' => $harga,
            'satuan' => $isPerOrang ? 'orang' : 'paket',
            'kapasitas_per_unit' => $p->kapasitas_per_unit,
            'tag' => $p->tag,
            'durasi' => $p->durasi,
            'min_participants' => $minParticipants,
            'max_participants' => $isPerOrang ? null : $p->kapasitas_per_unit,
            'gambar' => $p->gambar,
            'is_active' => (bool) $p->aktif,
            'includes' => collect($p->fasilitas ?? [])
                ->values()
                ->map(fn ($item, $i) => [
                    'id' => $i + 1,
                    'package_id' => $p->id,
                    'item' => (string) $item,
                    'urutan' => $i + 1,
                ])
                ->values(),
            'tiers' => $tierList,
        ];
    }
}
