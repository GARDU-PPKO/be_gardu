<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Dusun')]
class DusunController extends Controller
{
    #[Endpoint('Daftar Dusun')]
    public function index(): JsonResponse
    {
        $dusun = Dusun::where('is_active', true)
            ->orderBy('nama')
            ->get()
            ->map(fn (Dusun $d) => $this->listShape($d))
            ->values();

        return ApiResponse::success($dusun, 'Success retrieving dusun list');
    }

    #[Endpoint('Detail Dusun')]
    #[PathParameter('id', description: 'ID dusun', example: '1')]
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
