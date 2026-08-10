<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Budaya;
use App\Support\ApiResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Budaya')]
class BudayaController extends Controller
{
    #[Endpoint('Daftar Budaya')]
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

    #[Endpoint('Detail Budaya')]
    #[PathParameter('id', description: 'ID budaya', example: '1')]
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
