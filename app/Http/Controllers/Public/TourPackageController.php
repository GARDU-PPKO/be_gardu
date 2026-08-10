<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaketWisata;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Paket Wisata')]
class TourPackageController extends Controller
{
    #[Endpoint('Daftar Paket Wisata')]
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

    #[Endpoint('Detail Paket Wisata')]
    #[PathParameter('id', description: 'ID paket wisata', example: '1')]
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
