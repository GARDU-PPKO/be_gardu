<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\Dusun;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;

#[Group('Dusun')]
class DusunController extends Controller
{
    use ApiResponse;

    #[Endpoint('Daftar Dusun')]
    public function index(): JsonResponse
    {
        $dusun = Dusun::with(['galleries', 'keunggulan'])->where('is_active', true)->get();
        return $this->success($dusun);
    }

    #[Endpoint('Detail Dusun')]
    #[PathParameter('id', description: 'ID dusun (UUID)', example: '9a8b7c6d-5e4f-3a2b-1c0d-9e8f7a6b5c4d')]
    public function show($id): JsonResponse
    {
        $dusun = Dusun::with(['galleries', 'keunggulan'])->where('is_active', true)->findOrFail($id);
        return $this->success($dusun);
    }
}
